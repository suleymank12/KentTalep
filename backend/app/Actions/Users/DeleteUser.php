<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final class DeleteUser
{
    /**
     * Kullanıcıyı soft delete eder ve tüm token/cihaz kayıtlarını siler.
     * Kendini/son admini silme koruması DestroyUserRequest'te uygulanır.
     */
    public function handle(User $user): void
    {
        $user->tokens()->delete();
        $user->devices()->delete();
        $user->delete();
    }
}
