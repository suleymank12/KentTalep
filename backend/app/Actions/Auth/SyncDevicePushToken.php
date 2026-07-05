<?php

declare(strict_types=1);

namespace App\Actions\Auth;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * Push token'ı mevcut token'ın cihaz satırına yazar. Aynı push_token başka
 * bir satırda kayıtlıysa cihaz el değiştirmiş demektir: önce o satır(lar)da
 * null'lanır, sonra bu cihaza yazılır (UNIQUE kısıtı korunur).
 */
final class SyncDevicePushToken
{
    public function handle(User $user, PersonalAccessToken $currentToken, string $pushToken): UserDevice
    {
        return DB::transaction(function () use ($user, $currentToken, $pushToken): UserDevice {
            UserDevice::query()
                ->where('push_token', $pushToken)
                ->update(['push_token' => null]);

            $device = $user->devices()
                ->where('token_id', $currentToken->getKey())
                ->firstOrFail();

            $device->update([
                'push_token' => $pushToken,
                'last_seen_at' => now(),
            ]);

            return $device;
        });
    }
}
