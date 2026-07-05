<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Category;
use App\Models\Ticket;
use Clickbar\Magellan\Data\Geometries\Point;

use function Pest\Laravel\getJson;

it('scopes ticket listing by role', function (): void {
    $citizenA = userWithRole(Role::Citizen);
    $citizenB = userWithRole(Role::Citizen);
    $staff = userWithRole(Role::Staff);
    $manager = userWithRole(Role::Manager);
    $category = Category::factory()->create();

    Ticket::factory()->create(['user_id' => $citizenA->id, 'category_id' => $category->id]);
    Ticket::factory()->create(['user_id' => $citizenB->id, 'category_id' => $category->id]);
    Ticket::factory()->create([
        'user_id' => $citizenB->id,
        'category_id' => $category->id,
        'assigned_to' => $staff->id,
        'status' => 'assigned',
    ]);

    getJson('/api/tickets', bearer(tokenFor($citizenA)))->assertOk()->assertJsonCount(1, 'data');
    flushAuth();
    getJson('/api/tickets', bearer(tokenFor($staff)))->assertOk()->assertJsonCount(1, 'data');
    flushAuth();
    getJson('/api/tickets', bearer(tokenFor($manager)))->assertOk()->assertJsonCount(3, 'data');
});

it('filters tickets by proximity with the near parameter', function (): void {
    $manager = userWithRole(Role::Manager);
    $category = Category::factory()->create();

    Ticket::factory()->create(['category_id' => $category->id, 'location' => Point::makeGeodetic(39.930, 32.858)]);
    Ticket::factory()->create(['category_id' => $category->id, 'location' => Point::makeGeodetic(40.100, 33.100)]);

    getJson('/api/tickets?near=39.925,32.854&radius_km=5', bearer(tokenFor($manager)))
        ->assertOk()
        ->assertJsonCount(1, 'data');
});

it('finds a ticket by its number via the q filter', function (): void {
    $manager = userWithRole(Role::Manager);
    $category = Category::factory()->create();

    Ticket::factory()->create(['category_id' => $category->id, 'ticket_number' => '2026-098765']);
    Ticket::factory()->create(['category_id' => $category->id]);

    getJson('/api/tickets?q=098765', bearer(tokenFor($manager)))
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.ticket_number', '2026-098765');
});
