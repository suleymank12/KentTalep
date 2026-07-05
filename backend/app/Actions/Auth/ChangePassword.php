<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final class ChangePassword
{
    /**
     * Mevcut şifreyi doğrulayıp yenisiyle değiştirir; mevcut oturum hariç
     * kullanıcının diğer tüm token'larını (ve cascade ile cihazlarını) iptal
     * eder. Mevcut şifre hatalıysa 422 fırlatır.
     */
    public function handle(
        User $user,
        PersonalAccessToken $currentToken,
        string $currentPassword,
        string $newPassword,
    ): void {
        if (! Hash::check($currentPassword, (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Mevcut şifre hatalı.',
            ]);
        }

        $user->update(['password' => $newPassword]);

        $user->tokens()->whereKeyNot($currentToken->getKey())->delete();
    }
}
