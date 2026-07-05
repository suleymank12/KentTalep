<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use App\Models\UserDevice;

use function Pest\Laravel\postJson;

it('logs in with valid credentials and creates a device', function (): void {
    $user = userWithRole(Role::Citizen);

    $response = postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Cihaz',
        'platform' => 'android',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user' => ['id', 'role']]);

    expect(UserDevice::where('user_id', $user->getKey())->count())->toBe(1);
});

it('rejects a wrong password with a generic 422', function (): void {
    $user = userWithRole(Role::Citizen);

    postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'yanlissifre',
        'device_name' => 'Cihaz',
        'platform' => 'android',
    ])->assertStatus(422);
});

it('forbids inactive users with 403', function (): void {
    $user = User::factory()->inactive()->create();
    $user->assignRole(Role::Citizen->value);

    postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'Cihaz',
        'platform' => 'android',
    ])->assertStatus(403);
});
