<?php

declare(strict_types=1);

use App\Enums\Role;

use function Pest\Laravel\patchJson;

it('lets the owner edit the title while pending', function (): void {
    $owner = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    patchJson("/api/tickets/{$ticket->id}", ['title' => 'Güncellenmiş başlık'], bearer(tokenFor($owner)))
        ->assertOk()
        ->assertJsonPath('data.title', 'Güncellenmiş başlık');
});

it('forbids the owner from editing a non-pending ticket', function (): void {
    $owner = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'assigned', 'user_id' => $owner->id]);

    patchJson("/api/tickets/{$ticket->id}", ['title' => 'Yeni başlık deneme'], bearer(tokenFor($owner)))
        ->assertStatus(422);
});

it('lets a manager change the priority', function (): void {
    $manager = userWithRole(Role::Manager);
    $ticket = makeTicket(['status' => 'pending', 'priority' => 'medium']);

    patchJson("/api/tickets/{$ticket->id}", ['priority' => 'high'], bearer(tokenFor($manager)))
        ->assertOk()
        ->assertJsonPath('data.priority', 'high');
});

it('forbids a non-owner citizen from editing', function (): void {
    $owner = userWithRole(Role::Citizen);
    $other = userWithRole(Role::Citizen);
    $ticket = makeTicket(['status' => 'pending', 'user_id' => $owner->id]);

    patchJson("/api/tickets/{$ticket->id}", ['title' => 'İzinsiz düzenleme'], bearer(tokenFor($other)))
        ->assertForbidden();
});
