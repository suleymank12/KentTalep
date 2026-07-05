<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;

it('rejects a wrong current password with 422', function (): void {
    $user = userWithRole(Role::Citizen);
    $token = tokenFor($user);

    patchJson('/api/auth/password', [
        'current_password' => 'yanlis',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ], bearer($token))->assertStatus(422);
});

it('changes the password and revokes other tokens but keeps the current one', function (): void {
    $user = userWithRole(Role::Citizen);
    $current = tokenFor($user, 'Mevcut Cihaz');
    $other = tokenFor($user, 'Diğer Cihaz');

    patchJson('/api/auth/password', [
        'current_password' => 'password',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ], bearer($current))->assertNoContent();

    flushAuth();
    getJson('/api/auth/me', bearer($current))->assertOk();

    flushAuth();
    getJson('/api/auth/me', bearer($other))->assertUnauthorized();
});
