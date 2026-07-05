<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\UserDevice;

use function Pest\Laravel\patchJson;

it('stores the push token on the current device', function (): void {
    $user = userWithRole(Role::Citizen);
    $token = tokenFor($user);

    patchJson('/api/auth/device', ['push_token' => 'ExponentPushToken[abc123]'], bearer($token))
        ->assertOk()
        ->assertJsonPath('data.push_token', 'ExponentPushToken[abc123]');

    expect(UserDevice::where('user_id', $user->getKey())->value('push_token'))
        ->toBe('ExponentPushToken[abc123]');
});

it('transfers a push token from a previous device to the new one', function (): void {
    $first = userWithRole(Role::Citizen);
    $firstToken = tokenFor($first);
    patchJson('/api/auth/device', ['push_token' => 'PUSH-XYZ'], bearer($firstToken))->assertOk();

    flushAuth();
    $second = userWithRole(Role::Citizen);
    $secondToken = tokenFor($second);
    patchJson('/api/auth/device', ['push_token' => 'PUSH-XYZ'], bearer($secondToken))->assertOk();

    $firstDevice = UserDevice::where('user_id', $first->getKey())->firstOrFail();
    $secondDevice = UserDevice::where('user_id', $second->getKey())->firstOrFail();

    expect($firstDevice->push_token)->toBeNull()
        ->and($secondDevice->push_token)->toBe('PUSH-XYZ');
});
