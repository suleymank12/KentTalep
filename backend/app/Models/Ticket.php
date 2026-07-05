<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Clickbar\Magellan\Data\Geometries\Point;
use Database\Factories\TicketFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $ticket_number
 * @property int $user_id
 * @property int|null $assigned_to
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property TicketStatus $status
 * @property TicketPriority $priority
 * @property Point $location
 * @property string|null $location_address
 * @property Carbon|null $resolved_at
 * @property Carbon|null $closed_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class Ticket extends Model
{
    /** @use HasFactory<TicketFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_number',
        'user_id',
        'assigned_to',
        'category_id',
        'title',
        'description',
        'status',
        'priority',
        'location',
        'location_address',
        'resolved_at',
        'closed_at',
    ];

    /**
     * Talebi oluşturan vatandaş.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Talebe atanan personel.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<TicketMedia, $this>
     */
    public function media(): HasMany
    {
        return $this->hasMany(TicketMedia::class);
    }

    /**
     * @return HasMany<TicketStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(TicketStatusLog::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => TicketPriority::class,
            'location' => Point::class,
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }
}
