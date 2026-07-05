<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketAccess;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;

final class UpdateTicket
{
    /**
     * Yönetici/admin terminal olmayan talepte category_id/priority değiştirir;
     * talep sahibi yalnız pending durumda title/description değiştirir.
     * Yetki (sahibi|manager|admin) TicketPolicy'de denetlenir.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(Ticket $ticket, User $actor, array $data): Ticket
    {
        if (TicketAccess::isManager($actor)) {
            if ($ticket->status->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'Kapanmış talepler düzenlenemez.',
                ]);
            }

            $ticket->fill(Arr::only($data, ['category_id', 'priority']));
        } else {
            if ($ticket->status !== TicketStatus::Pending) {
                throw ValidationException::withMessages([
                    'status' => 'Talep yalnızca beklemedeyken düzenlenebilir.',
                ]);
            }

            $ticket->fill(Arr::only($data, ['title', 'description']));
        }

        $ticket->save();

        return $ticket;
    }
}
