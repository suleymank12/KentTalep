<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;

use function Pest\Laravel\assertSoftDeleted;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\patchJson;
use function Pest\Laravel\postJson;

it('forbids citizen and staff from listing users', function (Role $role): void {
    $user = userWithRole($role);

    getJson('/api/users', bearer(tokenFor($user)))->assertForbidden();
})->with([
    'citizen' => [Role::Citizen],
    'staff' => [Role::Staff],
]);
// Not: yönetici (manager) Faz 2'den itibaren personel listesini görebilir
// (bkz. ManagerStaffListTest); bu yüzden dataset'ten çıkarıldı.

it('allows an admin to list users', function (): void {
    $admin = userWithRole(Role::Admin);
    userWithRole(Role::Citizen);

    getJson('/api/users', bearer(tokenFor($admin)))
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'name', 'email', 'role', 'is_active']]]);
});

it('lets an admin create a staff user', function (): void {
    $admin = userWithRole(Role::Admin);

    postJson('/api/users', [
        'name' => 'Personel Bir',
        'email' => 'personel@example.com',
        'password' => 'parola123',
        'role' => 'staff',
    ], bearer(tokenFor($admin)))
        ->assertCreated()
        ->assertJsonPath('data.role', 'staff');

    expect(User::where('email', 'personel@example.com')->firstOrFail()->hasRole('staff'))->toBeTrue();
});

it('filters users by role and search term', function (): void {
    $admin = userWithRole(Role::Admin);
    $target = userWithRole(Role::Staff);
    $target->update(['name' => 'Benzersiz Isim']);

    getJson('/api/users?role=staff&search=Benzersiz', bearer(tokenFor($admin)))
        ->assertOk()
        ->assertJsonPath('data.0.email', $target->email)
        ->assertJsonCount(1, 'data');
});

it('lets an admin update a role and soft-delete a user', function (): void {
    $admin = userWithRole(Role::Admin);
    $target = userWithRole(Role::Citizen);
    $headers = bearer(tokenFor($admin));

    patchJson("/api/users/{$target->getKey()}", ['role' => 'manager'], $headers)
        ->assertOk()
        ->assertJsonPath('data.role', 'manager');

    deleteJson("/api/users/{$target->getKey()}", [], $headers)->assertNoContent();

    assertSoftDeleted($target);
});

it('deletes tokens and devices when deactivating a user', function (): void {
    $admin = userWithRole(Role::Admin);
    $target = userWithRole(Role::Citizen);
    tokenFor($target);

    patchJson("/api/users/{$target->getKey()}", ['is_active' => false], bearer(tokenFor($admin)))
        ->assertOk();

    expect($target->tokens()->count())->toBe(0)
        ->and($target->devices()->count())->toBe(0);
});
