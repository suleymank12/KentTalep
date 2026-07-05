<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

use function Pest\Laravel\postJson;

/**
 * @return array<string, mixed>
 */
function ticketPayload(int $categoryId): array
{
    return [
        'title' => 'Sokakta derin çukur var',
        'description' => 'Mahalle girişindeki kaldırımda tehlikeli bir çukur oluştu, acil onarım gerekiyor.',
        'category_id' => $categoryId,
        'latitude' => 39.925,
        'longitude' => 32.854,
        'location_address' => 'Kızılay Meydanı, Ankara',
    ];
}

it('creates a pending ticket with an initial log and stored location', function (): void {
    $citizen = userWithRole(Role::Citizen);
    $category = Category::factory()->create();

    $response = postJson('/api/tickets', [
        ...ticketPayload($category->id),
        'priority' => 'high', // triage yöneticinindir — yok sayılmalı
    ], bearer(tokenFor($citizen)));

    $response->assertCreated()
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.priority', 'medium');

    $ticket = Ticket::firstOrFail();

    expect($ticket->statusLogs()->count())->toBe(1)
        ->and($ticket->statusLogs()->first()?->old_status)->toBeNull()
        ->and($ticket->statusLogs()->first()?->new_status->value)->toBe('pending');

    $row = DB::selectOne('SELECT ST_Y(location::geometry) y, ST_X(location::geometry) x FROM tickets WHERE id = ?', [$ticket->id]);

    expect((float) $row->y)->toEqualWithDelta(39.925, 0.0001)
        ->and((float) $row->x)->toEqualWithDelta(32.854, 0.0001);
});

it('rejects invalid coordinates', function (): void {
    $citizen = userWithRole(Role::Citizen);
    $category = Category::factory()->create();

    postJson('/api/tickets', [
        ...ticketPayload($category->id),
        'latitude' => 200,
    ], bearer(tokenFor($citizen)))->assertStatus(422);
});

it('throttles ticket creation after five attempts', function (): void {
    $citizen = userWithRole(Role::Citizen);
    $category = Category::factory()->create();
    $token = tokenFor($citizen);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        postJson('/api/tickets', ticketPayload($category->id), bearer($token))->assertCreated();
    }

    postJson('/api/tickets', ticketPayload($category->id), bearer($token))->assertStatus(429);
});
