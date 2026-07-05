<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'app' => config('app.name'),
    'status' => 'ok',
]));
