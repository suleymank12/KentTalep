<?php

declare(strict_types=1);

use App\Enums\Role;
use App\Models\Category;
use App\Models\Ticket;
use App\Models\TicketStatusLog;
use App\Models\User;

use function Pest\Laravel\patchJson;

/**
 * @param  array<string, mixed>  $attributes
 */
function makeTicket(array $attributes = []): Ticket
{
    return Ticket::factory()->create([
        'category_id' => Category::factory(),
        ...$attributes,
    ]);
}

function addAfterMedia(Ticket $ticket, User $uploader): void
{
    $ticket->media()->create([
        'uploaded_by' => $uploader->id,
        'type' => 'after',
        'disk' => 'media_test',
        'path' => 'seed/x.jpg',
        'thumb_path' => 'seed/x_thumb.jpg',
        'original_name' => 'x.jpg',
        'mime_type' => 'image/jpeg',
        'size' => 1,
        'width' => 10,
        'height' => 10,
    ]);
}

it('lets a manager assign a pending ticket and logs it', function (): void {
    $manager = userWithRole(Role::Manager);
    $staff = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'pending']);

    patchJson("/api/tickets/{$ticket->id}/assign", ['assigned_to' => $staff->id], bearer(tokenFor($manager)))
        ->assertOk()
        ->assertJsonPath('data.status', 'assigned');

    expect($ticket->refresh()->assigned_to)->toBe($staff->id)
        ->and(TicketStatusLog::where('ticket_id', $ticket->id)->where('new_status', 'assigned')->exists())->toBeTrue();
});

it('forbids a citizen from assigning', function (): void {
    $citizen = userWithRole(Role::Citizen);
    $staff = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'pending']);

    patchJson("/api/tickets/{$ticket->id}/assign", ['assigned_to' => $staff->id], bearer(tokenFor($citizen)))
        ->assertForbidden();
});

it('lets the assigned staff start work but forbids others', function (): void {
    $staff = userWithRole(Role::Staff);
    $otherStaff = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'assigned', 'assigned_to' => $staff->id]);

    patchJson("/api/tickets/{$ticket->id}/start", [], bearer(tokenFor($otherStaff)))->assertForbidden();
    flushAuth();
    patchJson("/api/tickets/{$ticket->id}/start", [], bearer(tokenFor($staff)))
        ->assertOk()
        ->assertJsonPath('data.status', 'in_progress');
});

it('requires an after photo before resolving', function (): void {
    $staff = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'in_progress', 'assigned_to' => $staff->id]);

    patchJson("/api/tickets/{$ticket->id}/resolve", [], bearer(tokenFor($staff)))->assertStatus(422);
    flushAuth();

    addAfterMedia($ticket, $staff);

    patchJson("/api/tickets/{$ticket->id}/resolve", [], bearer(tokenFor($staff)))
        ->assertOk()
        ->assertJsonPath('data.status', 'resolved');

    expect($ticket->refresh()->resolved_at)->not->toBeNull();
});

it('requires a note to reject or reopen', function (): void {
    $manager = userWithRole(Role::Manager);
    $pending = makeTicket(['status' => 'pending']);
    $resolved = makeTicket(['status' => 'resolved']);

    patchJson("/api/tickets/{$pending->id}/reject", [], bearer(tokenFor($manager)))->assertStatus(422);
    flushAuth();
    patchJson("/api/tickets/{$pending->id}/reject", ['note' => 'Görev alanı dışında.'], bearer(tokenFor($manager)))
        ->assertOk()
        ->assertJsonPath('data.status', 'rejected');
    flushAuth();
    patchJson("/api/tickets/{$resolved->id}/reopen", [], bearer(tokenFor($manager)))->assertStatus(422);
});

it('allows the owner to cancel a pending ticket only', function (): void {
    $owner = userWithRole(Role::Citizen);
    $other = userWithRole(Role::Citizen);

    $pending = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);
    $inProgress = makeTicket(['status' => 'in_progress', 'user_id' => $owner->id]);

    patchJson("/api/tickets/{$pending->id}/cancel", [], bearer(tokenFor($other)))->assertForbidden();
    flushAuth();
    patchJson("/api/tickets/{$inProgress->id}/cancel", [], bearer(tokenFor($owner)))->assertStatus(422);
    flushAuth();
    patchJson("/api/tickets/{$pending->id}/cancel", [], bearer(tokenFor($owner)))
        ->assertOk()
        ->assertJsonPath('data.status', 'cancelled');
});

it('rejects an invalid transition with 422', function (): void {
    $staff = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'pending', 'assigned_to' => $staff->id]);

    // pending -> resolved tanımlı değil
    patchJson("/api/tickets/{$ticket->id}/resolve", [], bearer(tokenFor($staff)))->assertStatus(422);
});

it('records the new assignee on reassignment', function (): void {
    $manager = userWithRole(Role::Manager);
    $first = userWithRole(Role::Staff);
    $second = userWithRole(Role::Staff);
    $ticket = makeTicket(['status' => 'assigned', 'assigned_to' => $first->id]);

    patchJson("/api/tickets/{$ticket->id}/assign", ['assigned_to' => $second->id], bearer(tokenFor($manager)))
        ->assertOk();

    expect($ticket->refresh()->assigned_to)->toBe($second->id);
});
