<?php

declare(strict_types=1);

namespace App\Actions\Users;

use App\Enums\Role;
use App\Models\User;

final class CreateUser
{
    public function handle(
        string $name,
        string $email,
        ?string $phone,
        string $password,
        Role $role,
        bool $isActive = true,
    ): User {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'is_active' => $isActive,
        ]);

        $user->assignRole($role->value);

        return $user;
    }
}
