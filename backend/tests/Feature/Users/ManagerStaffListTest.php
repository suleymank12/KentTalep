<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\getJson;

it('lets a manager list only staff users', function (): void {
    $manager = userWithRole(Role::Manager);
    userWithRole(Role::Staff);
    userWithRole(Role::Staff);
    userWithRole(Role::Citizen);

    $response = getJson('/api/users?role=staff', bearer(tokenFor($manager)));

    $response->assertOk()->assertJsonCount(2, 'data');

    foreach ($response->json('data') as $user) {
        expect($user['role'])->toBe('staff');
    }
});

it('forbids a citizen from listing users', function (): void {
    getJson('/api/users', bearer(tokenFor(userWithRole(Role::Citizen))))->assertForbidden();
});
