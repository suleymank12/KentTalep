<?php

declare(strict_types=1);

use App\Enums\Role;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\withoutMiddleware;

/**
 * @return array<string, mixed>
 */
function wrongResetPayload(string $email): array
{
    return [
        'email' => $email,
        'code' => '000000',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ];
}

function seedResetCode(string $email, string $code = '123456', int $ageMinutes = 0): void
{
    DB::table('password_reset_tokens')->insert([
        'email' => $email,
        'token' => Hash::make($code),
        'created_at' => now()->subMinutes($ageMinutes),
    ]);
}

it('resets the password with a valid code and revokes all tokens', function (): void {
    $user = userWithRole(Role::Citizen);
    $token = tokenFor($user);
    seedResetCode($user->email);

    postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'code' => '123456',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ])->assertOk();

    expect(Hash::check('yeniparola1', (string) $user->refresh()->password))->toBeTrue()
        ->and(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();

    getJson('/api/auth/me', bearer($token))->assertUnauthorized();
});

it('rejects an invalid code', function (): void {
    $user = userWithRole(Role::Citizen);
    seedResetCode($user->email);

    postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'code' => '000000',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ])->assertStatus(422);
});

it('rejects an expired code', function (): void {
    $user = userWithRole(Role::Citizen);
    seedResetCode($user->email, ageMinutes: 16);

    postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'code' => '123456',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ])->assertStatus(422);
});

it('invalidates the code after five wrong attempts', function (): void {
    // reset-password throttle'ı (5/dk) aşmak için ThrottleRequests devre dışı.
    withoutMiddleware(ThrottleRequests::class);
    $user = userWithRole(Role::Citizen);
    seedResetCode($user->email);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        postJson('/api/auth/reset-password', wrongResetPayload($user->email))->assertStatus(422);
    }

    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeFalse();

    // Beşinci yanlış denemeden sonra doğru kod bile reddedilir (satır silindi).
    postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'code' => '123456',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ])->assertStatus(422);
});

it('allows a correct code on the fifth try after four wrong attempts', function (): void {
    withoutMiddleware(ThrottleRequests::class);
    $user = userWithRole(Role::Citizen);
    seedResetCode($user->email);

    for ($attempt = 0; $attempt < 4; $attempt++) {
        postJson('/api/auth/reset-password', wrongResetPayload($user->email))->assertStatus(422);
    }

    postJson('/api/auth/reset-password', [
        'email' => $user->email,
        'code' => '123456',
        'password' => 'yeniparola1',
        'password_confirmation' => 'yeniparola1',
    ])->assertOk();

    expect(Hash::check('yeniparola1', (string) $user->refresh()->password))->toBeTrue();
});

it('resets the attempts counter when a new code is issued', function (): void {
    withoutMiddleware(ThrottleRequests::class);
    $user = userWithRole(Role::Citizen);
    // 60 sn'lik yeniden gönderim sınırını aşmak için kod 2 dk eski.
    seedResetCode($user->email, ageMinutes: 2);

    for ($attempt = 0; $attempt < 4; $attempt++) {
        postJson('/api/auth/reset-password', wrongResetPayload($user->email))->assertStatus(422);
    }

    expect((int) DB::table('password_reset_tokens')->where('email', $user->email)->value('attempts'))->toBe(4);

    postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();

    expect((int) DB::table('password_reset_tokens')->where('email', $user->email)->value('attempts'))->toBe(0);
});
