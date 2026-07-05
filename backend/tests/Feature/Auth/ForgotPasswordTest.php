<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Notifications\PasswordResetCodeNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\postJson;

it('returns a generic 200 even for an unknown email', function (): void {
    postJson('/api/auth/forgot-password', ['email' => 'yok@example.com'])
        ->assertOk()
        ->assertJsonPath('message', 'E-posta kayıtlıysa sıfırlama kodu gönderildi.');

    expect(DB::table('password_reset_tokens')->count())->toBe(0);
});

it('generates a code and notifies a registered active user', function (): void {
    Notification::fake();
    $user = userWithRole(Role::Citizen);

    postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();

    expect(DB::table('password_reset_tokens')->where('email', $user->email)->exists())->toBeTrue();

    Notification::assertSentTo($user, PasswordResetCodeNotification::class);
});

it('does not issue a second code within 60 seconds', function (): void {
    $user = userWithRole(Role::Citizen);

    postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
    $first = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');

    postJson('/api/auth/forgot-password', ['email' => $user->email])->assertOk();
    $second = DB::table('password_reset_tokens')->where('email', $user->email)->value('token');

    expect($second)->toBe($first);
});
