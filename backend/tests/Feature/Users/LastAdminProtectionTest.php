<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\patchJson;

it('allows deleting an admin while another active admin remains', function (): void {
    $actor = userWithRole(Role::Admin);
    $other = userWithRole(Role::Admin);

    deleteJson("/api/users/{$other->getKey()}", [], bearer(tokenFor($actor)))->assertNoContent();
});

it('blocks removing the last active admin', function (): void {
    $admin = userWithRole(Role::Admin);

    deleteJson("/api/users/{$admin->getKey()}", [], bearer(tokenFor($admin)))
        ->assertStatus(422)
        ->assertJsonValidationErrors(['user' => 'son aktif admin']);
});

it('blocks demoting the last active admin', function (): void {
    $admin = userWithRole(Role::Admin);

    patchJson("/api/users/{$admin->getKey()}", ['role' => 'manager'], bearer(tokenFor($admin)))
        ->assertStatus(422);
});
