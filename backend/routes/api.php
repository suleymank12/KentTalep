<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketMediaController;
use App\Http\Controllers\TicketTransitionController;
use App\Http\Controllers\UserController;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Genel white-label ayarları (auth gerektirmez; ForceJson uygulanır).
Route::get('settings', [SettingController::class, 'index']);

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register'])->middleware('throttle:register');
    Route::post('login', [AuthController::class, 'login'])->middleware('throttle:login');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:forgot-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:reset-password');

    Route::middleware(['auth:sanctum', 'active', 'device.seen'])->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::patch('profile', [AuthController::class, 'updateProfile']);
        Route::patch('device', [AuthController::class, 'updateDevice']);
        Route::patch('password', [AuthController::class, 'changePassword']);
    });
});

Route::middleware(['auth:sanctum', 'active', 'device.seen'])->group(function (): void {
    // Kullanıcı yönetimi
    Route::get('users', [UserController::class, 'index'])->middleware('can:viewAny,'.User::class);
    Route::post('users', [UserController::class, 'store'])->middleware('can:create,'.User::class);
    Route::get('users/{user}', [UserController::class, 'show'])->middleware('can:view,user');
    Route::match(['put', 'patch'], 'users/{user}', [UserController::class, 'update'])->middleware('can:update,user');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->middleware('can:delete,user');

    // Kategoriler
    Route::get('categories', [CategoryController::class, 'index']);

    // Talep CRUD
    Route::get('tickets', [TicketController::class, 'index']);
    Route::post('tickets', [TicketController::class, 'store'])->middleware(['can:create,'.Ticket::class, 'throttle:ticket-create']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->middleware('can:view,ticket');
    Route::patch('tickets/{ticket}', [TicketController::class, 'update'])->middleware('can:update,ticket');
    Route::delete('tickets/{ticket}', [TicketController::class, 'destroy'])->middleware('can:delete,ticket');
    Route::get('tickets/{ticket}/logs', [TicketController::class, 'logs'])->middleware('can:view,ticket');

    // Durum geçişleri (yetki TicketStateMachine içinde)
    Route::patch('tickets/{ticket}/assign', [TicketTransitionController::class, 'assign']);
    Route::patch('tickets/{ticket}/start', [TicketTransitionController::class, 'start']);
    Route::patch('tickets/{ticket}/resolve', [TicketTransitionController::class, 'resolve']);
    Route::patch('tickets/{ticket}/close', [TicketTransitionController::class, 'close']);
    Route::patch('tickets/{ticket}/reopen', [TicketTransitionController::class, 'reopen']);
    Route::patch('tickets/{ticket}/cancel', [TicketTransitionController::class, 'cancel']);
    Route::patch('tickets/{ticket}/reject', [TicketTransitionController::class, 'reject']);

    // Medya
    Route::post('tickets/{ticket}/media', [TicketMediaController::class, 'store'])->middleware('can:view,ticket');
    Route::get('ticket-media/{media}', [TicketMediaController::class, 'show'])->middleware('can:view,media');
    Route::get('ticket-media/{media}/thumb', [TicketMediaController::class, 'thumb'])->middleware('can:view,media');
    Route::delete('ticket-media/{media}', [TicketMediaController::class, 'destroy'])->middleware('can:delete,media');
});
