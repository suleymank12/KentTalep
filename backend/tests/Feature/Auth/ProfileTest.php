<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\patchJson;

it('lets a user update their name and phone', function (): void {
    $user = userWithRole(Role::Citizen);

    patchJson('/api/auth/profile', [
        'name' => 'Yeni Ad Soyad',
        'phone' => '05559998877',
    ], bearer(tokenFor($user)))
        ->assertOk()
        ->assertJsonPath('data.name', 'Yeni Ad Soyad')
        ->assertJsonPath('data.phone', '05559998877');
});

it('rejects an invalid profile update with a Turkish message', function (): void {
    $user = userWithRole(Role::Citizen);

    $response = patchJson('/api/auth/profile', ['name' => 'ab'], bearer(tokenFor($user)));

    $response->assertStatus(422)->assertJsonValidationErrors('name');
});
