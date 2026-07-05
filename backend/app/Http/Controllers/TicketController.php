<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Tickets\CreateTicket;
use App\Actions\Tickets\UpdateTicket;
use App\Enums\Role;
use App\Http\Requests\Tickets\IndexTicketRequest;
use App\Http\Requests\Tickets\StoreTicketRequest;
use App\Http\Requests\Tickets\UpdateTicketRequest;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketStatusLogResource;
use App\Models\Ticket;
use App\Support\TicketAccess;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TicketController extends Controller
{
    public function index(IndexTicketRequest $request): AnonymousResourceCollection
    {
        $actor = $this->authUser($request);
        $perPage = max(1, min($request->integer('per_page', 20), 100));

        $query = Ticket::query()->with(['category', 'assignee']);

        // Rol kapsamı: yönetici/admin tümü, personel atananı, vatandaş kendini.
        if (TicketAccess::isManager($actor)) {
            // tümü
        } elseif ($actor->hasRole(Role::Staff->value)) {
            $query->where('assigned_to', $actor->getKey());
        } else {
            $query->where('user_id', $actor->getKey());
        }

        $query
            ->when($request->filled('status'), fn (Builder $q) => $q->where('status', $request->string('status')->value()))
            ->when($request->filled('category_id'), fn (Builder $q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('priority'), fn (Builder $q) => $q->where('priority', $request->string('priority')->value()))
            ->when($request->filled('q'), function (Builder $q) use ($request): void {
                $term = $request->string('q')->value();
                $q->where(function (Builder $inner) use ($term): void {
                    $inner->where('title', 'ilike', "%{$term}%")
                        ->orWhere('ticket_number', 'ilike', "%{$term}%");
                });
            });

        if ($request->filled('near')) {
            [$latitude, $longitude] = $this->parseNear($request->string('near')->value());
            $meters = $request->float('radius_km') * 1000;

            $query->whereRaw(
                'ST_DWithin(location, ST_SetSRID(ST_MakePoint(?, ?), 4326)::geography, ?)',
                [$longitude, $latitude, $meters],
            );
        }

        $tickets = $query->orderByDesc('created_at')->paginate($perPage);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request, CreateTicket $action): JsonResponse
    {
        $ticket = $action->handle(
            $this->authUser($request),
            $request->string('title')->value(),
            $request->string('description')->value(),
            $request->integer('category_id'),
            $request->float('latitude'),
            $request->float('longitude'),
            $request->filled('location_address') ? $request->string('location_address')->value() : null,
        );

        return TicketResource::make($ticket->load(['category', 'assignee', 'user', 'media']))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Ticket $ticket): TicketResource
    {
        return new TicketResource($ticket->load(['category', 'media', 'assignee', 'user']));
    }

    public function update(UpdateTicketRequest $request, Ticket $ticket, UpdateTicket $action): TicketResource
    {
        $ticket = $action->handle($ticket, $this->authUser($request), $request->validated());

        return new TicketResource($ticket->load(['category', 'assignee', 'user', 'media']));
    }

    public function destroy(Ticket $ticket): Response
    {
        $ticket->delete();

        return response()->noContent();
    }

    public function logs(Ticket $ticket): AnonymousResourceCollection
    {
        $logs = $ticket->statusLogs()->with('changedBy')->latest()->get();

        return TicketStatusLogResource::collection($logs);
    }

    /**
     * "lat,lng" biçimindeki near parametresini enlem/boylama ayrıştırır.
     *
     * @return array{0: float, 1: float}
     */
    private function parseNear(string $near): array
    {
        $parts = array_map('trim', explode(',', $near));

        return [(float) $parts[0], (float) $parts[1]];
    }
}
