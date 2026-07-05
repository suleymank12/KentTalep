<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

abstract class Controller
{
    use AuthorizesRequests;

    /**
     * Kimliği doğrulanmış kullanıcıyı User tipiyle döndürür.
     * auth:sanctum arkasındaki rotalarda kullanıcı her zaman mevcuttur.
     */
    protected function authUser(Request $request): User
    {
        $user = $request->user();

        if (! $user instanceof User) {
            abort(401);
        }

        return $user;
    }
}
