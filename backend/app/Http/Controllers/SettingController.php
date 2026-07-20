<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    /**
     * Yalnız whitelist'teki genel ayarları düz {key: value} objesi olarak
     * döner (auth gerektirmez). 5 dakika önbelleklenir.
     */
    public function index(): JsonResponse
    {
        $settings = Cache::remember('public-settings', 300, fn (): array => Setting::query()
            ->whereIn('key', (array) config('kenttalep.public_settings'))
            ->pluck('value', 'key')
            ->all());

        return response()->json($settings);
    }
}
