<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TicketMediaType;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketStatusLog;
use App\Models\User;
use App\Support\TicketTransition;
use App\Support\TicketTransitionMap;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

/**
 * Talep durum geçişlerini rol/sahiplik yetkileri ve gereklilik kurallarıyla
 * uygular; her geçişi ticket_status_logs'a yazar. Tüm yazımlar transaction
 * içindedir.
 */
final class TicketStateMachine
{
    /**
     * @param  array<string, mixed>  $extra  Geçişe özel veriler (ör. assigned_to)
     */
    public function transition(
        Ticket $ticket,
        TicketStatus $to,
        User $actor,
        ?string $note = null,
        array $extra = [],
    ): Ticket {
        // Tüm akış transaction + satır kilidi altında. Eşzamanlı ikinci geçiş
        // (ör. cancel + assign) ilki commit edene kadar lockForUpdate'te bekler,
        // sonra TAZE durumu okuyup kuralları ona göre değerlendirir; böylece
        // bayat status üzerinden çelişkili geçiş/log oluşmaz. İçeride fırlayan
        // ValidationException/abort'ta Laravel otomatik rollback yapar.
        return DB::transaction(function () use ($ticket, $to, $actor, $note, $extra): Ticket {
            $ticket = Ticket::query()->lockForUpdate()->findOrFail((int) $ticket->getKey());

            $from = $ticket->status;
            $rule = TicketTransitionMap::find($from, $to);

            if ($rule === null) {
                throw ValidationException::withMessages([
                    'status' => 'Talep şu anki durumundan bu duruma geçirilemez.',
                ]);
            }

            if (! $this->actorAllowed($rule, $ticket, $actor)) {
                abort(Response::HTTP_FORBIDDEN, 'Bu işlemi yapma yetkiniz yok.');
            }

            $note = $note !== null ? trim($note) : null;

            if ($rule->requiresNote && ($note === null || $note === '')) {
                throw ValidationException::withMessages([
                    'note' => 'Bu işlem için açıklama (not) zorunludur.',
                ]);
            }

            $assignedTo = $this->resolveAssignee($rule, $extra);

            if ($rule->requiresAfterMedia
                && ! $ticket->media()->where('type', TicketMediaType::After->value)->exists()
            ) {
                throw ValidationException::withMessages([
                    'media' => 'İş bitirme fotoğrafı ekleyin.',
                ]);
            }

            $ticket->status = $to;

            if ($assignedTo !== null) {
                $ticket->assigned_to = $assignedTo;
            }
            if ($to === TicketStatus::Resolved) {
                $ticket->resolved_at = now();
            }
            if ($to === TicketStatus::Closed) {
                $ticket->closed_at = now();
            }
            // Reopen: çözülmüş talep tekrar işleme alınırsa resolved_at temizlenir.
            if ($from === TicketStatus::Resolved && $to === TicketStatus::InProgress) {
                $ticket->resolved_at = null;
            }

            $ticket->save();

            TicketStatusLog::create([
                'ticket_id' => $ticket->getKey(),
                'changed_by' => $actor->getKey(),
                'old_status' => $from->value,
                'new_status' => $to->value,
                'note' => $note,
            ]);

            return $ticket;
        });
    }

    private function actorAllowed(TicketTransition $rule, Ticket $ticket, User $actor): bool
    {
        foreach ($rule->roles as $role) {
            if ($actor->hasRole($role->value)) {
                return true;
            }
        }

        $actorId = (int) $actor->getKey();

        if ($rule->owner && (int) $ticket->user_id === $actorId) {
            return true;
        }

        return $rule->assignee
            && $ticket->assigned_to !== null
            && (int) $ticket->assigned_to === $actorId;
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    private function resolveAssignee(TicketTransition $rule, array $extra): ?int
    {
        if (! $rule->requiresAssignee) {
            return null;
        }

        $assignedTo = $extra['assigned_to'] ?? null;

        if (! is_numeric($assignedTo)) {
            throw ValidationException::withMessages([
                'assigned_to' => 'Atanacak personel seçilmelidir.',
            ]);
        }

        return (int) $assignedTo;
    }
}
