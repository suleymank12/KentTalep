<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Enums\DevicePlatform;
use App\Models\User;
use App\Models\UserDevice;

/**
 * Kullanıcı için yeni bir Sanctum token'ı üretir ve buna bağlı cihaz kaydını
 * açar. Register ve login akışları tarafından paylaşılır.
 */
final class IssueDeviceToken
{
    /**
     * @return array{token: string, device: UserDevice}
     */
    public function handle(User $user, string $deviceName, DevicePlatform $platform): array
    {
        $newToken = $user->createToken($deviceName);

        $device = $user->devices()->create([
            'token_id' => $newToken->accessToken->getKey(),
            'device_name' => $deviceName,
            'platform' => $platform,
            'last_seen_at' => now(),
        ]);

        return [
            'token' => $newToken->plainTextToken,
            'device' => $device,
        ];
    }
}
