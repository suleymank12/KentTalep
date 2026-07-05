<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class LastAdminGuard
{
    /**
     * Verilen kullanıcı sistemdeki son aktif admin mi? (Silme/pasifleştirme/
     * rol düşürme korumaları için kullanılır.)
     */
    public static function isLastActiveAdmin(User $user): bool
    {
        if (! $user->is_active || ! $user->hasRole(Role::Admin->value)) {
            return false;
        }

        return ! User::query()
            ->where('is_active', true)
            ->whereKeyNot($user->getKey())
            ->whereHas('roles', fn (Builder $query) => $query->where('name', Role::Admin->value))
            ->exists();
    }
}
