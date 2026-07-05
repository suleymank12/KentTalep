<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Http\Requests\Tickets\AssignTicketRequest;
use App\Http\Requests\Tickets\TransitionNoteRequest;
use App\Http\Resources\TicketResource;
use App\Models\Ticket;
use App\Services\TicketStateMachine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;

class TicketTransitionController extends Controller
{
    public function __construct(private readonly TicketStateMachine $machine) {}

    public function assign(AssignTicketRequest $request, Ticket $ticket): TicketResource
    {
        $ticket = $this->machine->transition(
            $ticket,
            TicketStatus::Assigned,
            $this->authUser($request),
            null,
            ['assigned_to' => $request->integer('assigned_to')],
        );

        return $this->respond($ticket);
    }

    public function start(Request $request, Ticket $ticket): TicketResource
    {
        return $this->respond(
            $this->machine->transition($ticket, TicketStatus::InProgress, $this->authUser($request)),
        );
    }

    public function resolve(TransitionNoteRequest $request, Ticket $ticket): TicketResource
    {
        return $this->respond(
            $this->machine->transition($ticket, TicketStatus::Resolved, $this->authUser($request), $this->note($request)),
        );
    }

    public function close(Request $request, Ticket $ticket): TicketResource
    {
        return $this->respond(
            $this->machine->transition($ticket, TicketStatus::Closed, $this->authUser($request)),
        );
    }

    public function reopen(TransitionNoteRequest $request, Ticket $ticket): TicketResource
    {
        return $this->respond(
            $this->machine->transition($ticket, TicketStatus::InProgress, $this->authUser($request), $this->note($request)),
        );
    }

    public function cancel(TransitionNoteRequest $request, Ticket $ticket): TicketResource
    {
        return $this->respond(
            $this->machine->transition($ticket, TicketStatus::Cancelled, $this->authUser($request), $this->note($request)),
        );
    }

    public function reject(TransitionNoteRequest $request, Ticket $ticket): TicketResource
    {
        return $this->respond(
            $this->machine->transition($ticket, TicketStatus::Rejected, $this->authUser($request), $this->note($request)),
        );
    }

    private function respond(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket->load(['category', 'assignee', 'user', 'media']));
    }

    private function note(FormRequest $request): ?string
    {
        return $request->filled('note') ? $request->string('note')->value() : null;
    }
}
