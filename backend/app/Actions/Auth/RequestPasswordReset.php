<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Kayıtlı ve aktif kullanıcı için 6 haneli sıfırlama kodu üretir, hash'leyip
 * saklar ve e-posta ile gönderir. Kayıtsız/pasif kullanıcıda sessizce çıkar
 * (çağıran katman her durumda generic yanıt döner). Aynı e-postaya 60 sn
 * içinde ikinci kod üretilmez.
 */
final class RequestPasswordReset
{
    public function handle(string $email): void
    {
        $user = User::where('email', $email)->first();

        if ($user === null || ! $user->is_active) {
            return;
        }

        $existing = DB::table('password_reset_tokens')->where('email', $email)->first();

        if ($existing !== null
            && is_string($existing->created_at)
            && Carbon::parse($existing->created_at)->gt(now()->subSeconds(60))
        ) {
            return;
        }

        $code = (string) random_int(100000, 999999);

        // Yeni kod yazılırken deneme sayacı sıfırlanır (üst üste forgot
        // çağrısında sayaç taşımaz).
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => Hash::make($code), 'created_at' => now(), 'attempts' => 0],
        );

        $user->notify(new PasswordResetCodeNotification($code));
    }
}
