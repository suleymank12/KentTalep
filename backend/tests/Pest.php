<?php

declare(strict_types=1);

use App\Actions\Auth\IssueDeviceToken;
use App\Enums\DevicePlatform;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class)->in('Feature');

/**
 * Verilen kullanıcı için gerçek bir Sanctum token'ı ve cihaz kaydı üretir,
 * plaintext token'ı döndürür. Testler bunu withToken() ile kullanır.
 */
function tokenFor(User $user, string $device = 'Test Cihaz', DevicePlatform $platform = DevicePlatform::Android): string
{
    return app(IssueDeviceToken::class)->handle($user, $device, $platform)['token'];
}

/**
 * Belirtilen role sahip yeni bir kullanıcı oluşturur.
 */
function userWithRole(Role $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role->value);

    return $user;
}

/**
 * Bearer token için Authorization başlık dizisi üretir.
 *
 * @return array<string, string>
 */
function bearer(string $token): array
{
    return ['Authorization' => "Bearer {$token}"];
}

/**
 * Auth guard'ının önbelleğe aldığı kullanıcıyı temizler. Testlerde tek app
 * örneği paylaşıldığı için, art arda farklı token'larla istek yaparken guard
 * ilk çözümlediği kullanıcıyı önbellekte tutar; bu yardımcı, her isteğin
 * token'ı yeniden doğrulamasını sağlar (production'da her istek ayrı süreçtir).
 */
function flushAuth(): void
{
    Auth::forgetGuards();
}
