<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'parent_id' => null,
            'icon' => 'map-pin',
            'color' => fake()->hexColor(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
