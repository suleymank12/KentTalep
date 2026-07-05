<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserDevice;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kimliği doğrulanmış isteklerde, mevcut token'a bağlı cihazın last_seen_at
 * değeri 15 dakikadan eskiyse sessizce günceller. Recent kayıtlarda hiçbir
 * yazma yapılmaz (koşullu WHERE 0 satır etkiler).
 */
final class UpdateDeviceLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $user = $request->user();

        if ($user instanceof User) {
            $token = $user->currentAccessToken();

            UserDevice::query()
                ->where('token_id', $token->getKey())
                ->where(function (Builder $query): void {
                    $query->whereNull('last_seen_at')
                        ->orWhere('last_seen_at', '<', now()->subMinutes(15));
                })
                ->update(['last_seen_at' => now()]);
        }

        return $response;
    }
}
