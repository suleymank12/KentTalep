<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * White-label varsayılan ayarları. Idempotent'tir ve production kurulumda da
 * koşar (bkz. README).
 */
class SettingsSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const DEFAULTS = [
        'municipality_name' => 'KentTalep Demo',
        'primary_color' => '#0F766E',
        'map_center_lat' => '39.925',
        'map_center_lng' => '32.854',
        'map_zoom' => '13',
        // OSM tile'ı yalnız demo içindir; üretim kurulumunda belediyeye kendi
        // tile kaynağı önerilir (Faz 6 kurulum dokümanı).
        'map_tile_url' => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
    ];

    public function run(): void
    {
        foreach (self::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
