<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\UserDevice;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('logs out, deletes the device and invalidates the token', function (): void {
    $user = userWithRole(Role::Citizen);
    $token = tokenFor($user);

    postJson('/api/auth/logout', [], bearer($token))->assertNoContent();

    expect(UserDevice::where('user_id', $user->getKey())->count())->toBe(0);

    flushAuth();
    getJson('/api/auth/me', bearer($token))->assertUnauthorized();
});
