<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;

/**
 * Kullanıcı yönetimi yalnızca admin rolüne açıktır.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user) || $user->hasRole(Role::Manager->value);
    }

    public function view(User $user, User $model): bool
    {
        return $this->isAdmin($user) || $user->hasRole(Role::Manager->value);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->isAdmin($user);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasRole(Role::Admin->value);
    }
}
