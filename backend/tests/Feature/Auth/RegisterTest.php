<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\User;
use App\Models\UserDevice;

use function Pest\Laravel\postJson;

it('registers a citizen with a token and device record', function (): void {
    $response = postJson('/api/auth/register', [
        'name' => 'Ayşe Yılmaz',
        'email' => 'ayse@example.com',
        'phone' => '05551112233',
        'password' => 'parola123',
        'password_confirmation' => 'parola123',
        'device_name' => 'Ayşe iPhone',
        'platform' => 'ios',
        'kvkk_accepted' => true,
    ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email', 'phone', 'role', 'is_active', 'created_at'],
        ])
        ->assertJsonPath('user.role', Role::Citizen->value)
        ->assertJsonPath('user.email', 'ayse@example.com');

    $user = User::where('email', 'ayse@example.com')->firstOrFail();

    expect($user->hasRole(Role::Citizen->value))->toBeTrue()
        ->and($user->kvkk_accepted_at)->not->toBeNull()
        ->and(UserDevice::where('user_id', $user->getKey())->count())->toBe(1);
});

it('returns a Turkish validation message on invalid input', function (): void {
    $response = postJson('/api/auth/register', [
        'name' => 'Ali',
        'email' => 'gecersiz-eposta',
        'password' => 'parola123',
        'password_confirmation' => 'parola123',
        'device_name' => 'Cihaz',
        'platform' => 'ios',
        'kvkk_accepted' => true,
    ]);

    $response->assertStatus(422);

    expect($response->json('errors.email.0'))->toContain('e-posta');
});

it('requires KVKK consent to register', function (): void {
    $response = postJson('/api/auth/register', [
        'name' => 'Kvkksiz',
        'email' => 'kvkk@example.com',
        'password' => 'parola123',
        'password_confirmation' => 'parola123',
        'device_name' => 'Cihaz',
        'platform' => 'ios',
        'kvkk_accepted' => false,
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('kvkk_accepted');

    expect($response->json('errors.kvkk_accepted.0'))->toContain('kabul');
});

it('rejects duplicate email registration', function (): void {
    User::factory()->create(['email' => 'var@example.com']);

    postJson('/api/auth/register', [
        'name' => 'Yeni',
        'email' => 'var@example.com',
        'password' => 'parola123',
        'password_confirmation' => 'parola123',
        'device_name' => 'Cihaz',
        'platform' => 'android',
        'kvkk_accepted' => true,
    ])->assertStatus(422);
});
