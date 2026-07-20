<?php

declare(strict_types=1);

use App\Models\Setting;
use Database\Seeders\SettingsSeeder;

use function Pest\Laravel\getJson;
use function Pest\Laravel\seed;

it('returns the whitelisted public settings without authentication', function (): void {
    seed(SettingsSeeder::class);

    getJson('/api/settings')
        ->assertOk()
        ->assertJsonCount(6)
        ->assertJsonPath('municipality_name', 'KentTalep Demo')
        ->assertJsonPath('primary_color', '#0F766E');
});

it('does not leak non-whitelisted settings', function (): void {
    Setting::create(['key' => 'municipality_name', 'value' => 'KentTalep Demo']);
    Setting::create(['key' => 'internal_note', 'value' => 'gizli bilgi']);

    $response = getJson('/api/settings')->assertOk();

    expect($response->json())->toHaveKey('municipality_name');
    expect($response->json())->not->toHaveKey('internal_note');
});
