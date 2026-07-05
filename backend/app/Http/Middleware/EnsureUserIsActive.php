<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Kimliği doğrulanmış kullanıcı pasifse (is_active=false) isteği 403 ile
 * reddeder. Oturum açtıktan sonra devre dışı bırakılan hesapları da kapatır.
 */
final class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Hesabınız devre dışı bırakılmış.');
        }

        return $next($request);
    }
}
