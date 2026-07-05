<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * API isteklerinin her zaman JSON döndürülmesini garanti eder: gelen isteğe
 * "Accept: application/json" başlığını zorlar; böylece doğrulama/hata
 * yanıtları HTML yerine JSON olur.
 */
final class ForceJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        return $next($request);
    }
}
