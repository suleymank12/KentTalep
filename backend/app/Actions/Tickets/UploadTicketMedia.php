<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Enums\Role;
use App\Enums\TicketMediaType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketMedia;
use App\Models\User;
use App\Services\TicketMediaProcessor;
use App\Support\TicketAccess;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

final class UploadTicketMedia
{
    private const MAX_MEDIA = 10;

    public function __construct(private readonly TicketMediaProcessor $processor) {}

    public function handle(Ticket $ticket, User $actor, UploadedFile $file, TicketMediaType $type): TicketMedia
    {
        $this->authorizeUpload($ticket, $actor, $type);

        if ($ticket->media()->count() >= self::MAX_MEDIA) {
            throw ValidationException::withMessages([
                'file' => 'Bir talebe en fazla 10 medya eklenebilir.',
            ]);
        }

        $data = $this->processor->process($file, (int) $ticket->getKey());

        return $ticket->media()->create([
            'uploaded_by' => $actor->getKey(),
            'type' => $type->value,
            ...$data,
        ]);
    }

    private function authorizeUpload(Ticket $ticket, User $actor, TicketMediaType $type): void
    {
        $actorId = (int) $actor->getKey();
        $isOwner = (int) $ticket->user_id === $actorId;
        $isAssignee = $ticket->assigned_to !== null && (int) $ticket->assigned_to === $actorId;

        if ($type === TicketMediaType::Before) {
            if (! $isOwner && ! $actor->hasRole(Role::Admin->value)) {
                abort(Response::HTTP_FORBIDDEN, 'Bu talebe fotoğraf ekleyemezsiniz.');
            }
            if ($ticket->status->isTerminal()) {
                throw ValidationException::withMessages([
                    'type' => 'Kapanmış talebe fotoğraf eklenemez.',
                ]);
            }

            return;
        }

        if (! $isAssignee && ! TicketAccess::isManager($actor)) {
            abort(Response::HTTP_FORBIDDEN, 'Bu talebe iş fotoğrafı ekleyemezsiniz.');
        }

        if (! in_array($ticket->status, [TicketStatus::InProgress, TicketStatus::Resolved], true)) {
            throw ValidationException::withMessages([
                'type' => 'İş fotoğrafı yalnız devam eden veya çözülen talebe eklenebilir.',
            ]);
        }
    }
}
