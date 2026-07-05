<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_number' => now()->year.'-'.Str::padLeft((string) fake()->unique()->numberBetween(1, 999999), 6, '0'),
            'user_id' => User::factory(),
            'assigned_to' => null,
            'category_id' => Category::factory(),
            'title' => rtrim(fake()->sentence(4), '.'),
            'description' => fake()->paragraph(),
            'status' => TicketStatus::Pending->value,
            'priority' => TicketPriority::Medium->value,
            'location' => Point::makeGeodetic(fake()->latitude(39.85, 39.99), fake()->longitude(32.75, 32.95)),
            'location_address' => fake()->streetAddress(),
        ];
    }
}
