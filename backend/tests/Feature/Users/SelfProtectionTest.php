<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\deleteJson;
use function Pest\Laravel\patchJson;

beforeEach(function (): void {
    // İkinci bir admin, "son aktif admin" korumasının değil, "kendini koruma"
    // kuralının test edildiğini garanti eder.
    userWithRole(Role::Admin);
});

it('prevents an admin from changing their own role', function (): void {
    $admin = userWithRole(Role::Admin);

    patchJson("/api/users/{$admin->getKey()}", ['role' => 'citizen'], bearer(tokenFor($admin)))
        ->assertStatus(422)
        ->assertJsonValidationErrors('role');
});

it('prevents an admin from deactivating themselves', function (): void {
    $admin = userWithRole(Role::Admin);

    patchJson("/api/users/{$admin->getKey()}", ['is_active' => false], bearer(tokenFor($admin)))
        ->assertStatus(422)
        ->assertJsonValidationErrors('is_active');
});

it('prevents an admin from deleting themselves', function (): void {
    $admin = userWithRole(Role::Admin);

    deleteJson("/api/users/{$admin->getKey()}", [], bearer(tokenFor($admin)))
        ->assertStatus(422);
});
