<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * 6 haneli kodu doğrular (hash + 15 dk geçerlilik), şifreyi değiştirir,
 * kullanıcının tüm token/cihaz kayıtlarını ve sıfırlama kodunu siler. Kod
 * geçersiz/süresi dolmuşsa AYNI generic 422 döner; 5 yanlış denemede kod
 * geçersiz kılınır (satır silinir). Kalan deneme hakkı asla sızdırılmaz.
 */
final class ResetPassword
{
    private const MAX_ATTEMPTS = 5;

    public function handle(string $email, string $code, string $newPassword): void
    {
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($record === null
            || ! is_string($record->created_at)
            || ! is_string($record->token)
            || Carbon::parse($record->created_at)->lte(now()->subMinutes(15))
        ) {
            // Süresi dolmuş (veya bozuk) satır varsa temizle; her durumda generic hata.
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            $this->invalid();
        }

        if (! Hash::check($code, $record->token)) {
            // Sayacı atomik olarak artır ve eşiği SQL koşuluyla uygula:
            // "oku → +1 → yaz" deseni eşzamanlı isteklerde lost update'e yol
            // açardı; increment (attempts = attempts + 1) ve koşullu delete
            // okuma yapmaz, yarış penceresi bırakmaz. Satır başka istekçe
            // silinmişse her iki ifade de no-op olur (fail-safe).
            DB::table('password_reset_tokens')->where('email', $email)->increment('attempts');

            DB::table('password_reset_tokens')
                ->where('email', $email)
                ->where('attempts', '>=', self::MAX_ATTEMPTS)
                ->delete();

            $this->invalid();
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->update(['password' => $newPassword]);

        $user->tokens()->delete();
        $user->devices()->delete();

        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    /**
     * Kod doğrulamasının başarısız olduğu tüm durumlarda aynı generic 422.
     */
    private function invalid(): never
    {
        throw ValidationException::withMessages([
            'code' => 'Kod geçersiz veya süresi dolmuş.',
        ]);
    }
}
