<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\postJson;

it('throttles login after five attempts', function (): void {
    $user = userWithRole(Role::Citizen);

    $payload = [
        'email' => $user->email,
        'password' => 'yanlissifre',
        'device_name' => 'Cihaz',
        'platform' => 'android',
    ];

    for ($attempt = 0; $attempt < 5; $attempt++) {
        postJson('/api/auth/login', $payload)->assertStatus(422);
    }

    postJson('/api/auth/login', $payload)->assertStatus(429);
});
