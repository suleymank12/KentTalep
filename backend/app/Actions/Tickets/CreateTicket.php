<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketStatusLog;
use App\Models\User;
use App\Services\TicketNumberGenerator;
use Clickbar\Magellan\Data\Geometries\Point;
use Illuminate\Support\Facades\DB;

final class CreateTicket
{
    public function __construct(private readonly TicketNumberGenerator $numbers) {}

    /**
     * Talebi pending olarak oluşturur, konumu Point olarak yazar ve ilk
     * durum kaydını (old=null, new=pending) ekler. Priority triage'a bırakılır
     * (varsayılan medium).
     */
    public function handle(
        User $actor,
        string $title,
        string $description,
        int $categoryId,
        float $latitude,
        float $longitude,
        ?string $address,
    ): Ticket {
        return DB::transaction(function () use ($actor, $title, $description, $categoryId, $latitude, $longitude, $address): Ticket {
            $ticket = Ticket::create([
                'ticket_number' => $this->numbers->generate(),
                'user_id' => $actor->getKey(),
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'status' => TicketStatus::Pending->value,
                'priority' => TicketPriority::Medium->value,
                'location' => Point::makeGeodetic($latitude, $longitude),
                'location_address' => $address,
            ]);

            TicketStatusLog::create([
                'ticket_id' => $ticket->getKey(),
                'changed_by' => $actor->getKey(),
                'old_status' => null,
                'new_status' => TicketStatus::Pending->value,
                'note' => null,
            ]);

            return $ticket;
        });
    }
}
