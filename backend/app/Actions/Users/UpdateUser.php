<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

final class UpdateUser
{
    /**
     * Yalnızca gönderilen (validate edilmiş) alanları günceller. Rol ayrı
     * senkronize edilir. is_active=false yapılırsa hedef kullanıcının tüm
     * token/cihaz kayıtları silinir. Kendini/son admini koruma kuralları
     * UpdateUserRequest'te 422 olarak uygulanır.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): void
    {
        if (array_key_exists('role', $data)) {
            $user->syncRoles([$data['role']]);
            unset($data['role']);
        }

        $deactivating = false;

        if (array_key_exists('is_active', $data)) {
            $isActive = filter_var($data['is_active'], FILTER_VALIDATE_BOOL);
            $data['is_active'] = $isActive;
            $deactivating = ! $isActive;
        }

        $user->update($data);

        if ($deactivating) {
            $user->tokens()->delete();
            $user->devices()->delete();
        }
    }
}
