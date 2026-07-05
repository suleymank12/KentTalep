<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Category;

use function Pest\Laravel\getJson;

it('returns active categories as a tree ordered by sort_order', function (): void {
    $parent = Category::factory()->create(['name' => 'Yol ve Asfalt', 'sort_order' => 0]);
    Category::factory()->create(['name' => 'Çukur', 'parent_id' => $parent->id, 'sort_order' => 0]);
    Category::factory()->create(['name' => 'Park', 'sort_order' => 1]);
    Category::factory()->create(['name' => 'Pasif', 'is_active' => false, 'sort_order' => 2]);

    $response = getJson('/api/categories', bearer(tokenFor(userWithRole(Role::Citizen))));

    $response->assertOk()
        ->assertJsonCount(2, 'data') // yalnız aktif kök kategoriler
        ->assertJsonPath('data.0.name', 'Yol ve Asfalt')
        ->assertJsonPath('data.0.children.0.name', 'Çukur');
});
