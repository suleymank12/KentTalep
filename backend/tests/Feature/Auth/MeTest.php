<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\getJson;

it('returns the authenticated user fields', function (): void {
    $user = userWithRole(Role::Manager);
    $user->update(['phone' => '05559998877']);
    $token = tokenFor($user);

    getJson('/api/auth/me', bearer($token))
        ->assertOk()
        ->assertJsonStructure([
            'data' => ['id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at'],
        ])
        ->assertJsonPath('data.role', Role::Manager->value)
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.phone', '05559998877');
});

it('rejects unauthenticated access to me', function (): void {
    getJson('/api/auth/me')->assertUnauthorized();
});
