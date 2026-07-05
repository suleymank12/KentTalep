<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:reset-password');

    Route::middleware(['auth:sanctum', 'active', 'device.seen'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('device', [AuthController::class, 'updateDevice']);
        Route::patch('password', [AuthController::class, 'changePassword']);
    });
});

Route::middleware(['auth:sanctum', 'active', 'device.seen'])->group(function (): void {
    Route::get('users', [UserController::class, 'index'])->middleware('can:viewAny,'.User::class);
    Route::post('users', [UserController::class, 'store'])->middleware('can:create,'.User::class);
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('can:view,user');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('can:update,user');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:delete,user');
});
