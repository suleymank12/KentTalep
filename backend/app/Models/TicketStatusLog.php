<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int|null $changed_by
 * @property TicketStatus|null $old_status
 * @property TicketStatus $new_status
 * @property string|null $note
 * @property Carbon|null $created_at
 */
class TicketStatusLog extends Model
{
    // Yalnızca created_at yönetilir (updated_at kolonu yok).
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'changed_by',
        'old_status',
        'new_status',
        'note',
    ];

    /**
     * @return BelongsTo<Ticket, $this>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_status' => TicketStatus::class,
            'new_status' => TicketStatus::class,
            'created_at' => 'datetime',
        ];
    }
}
