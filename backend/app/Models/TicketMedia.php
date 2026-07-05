<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TicketMediaType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $ticket_id
 * @property int $uploaded_by
 * @property TicketMediaType $type
 * @property string $disk
 * @property string $path
 * @property string $thumb_path
 * @property string $original_name
 * @property string $mime_type
 * @property int $size
 * @property int $width
 * @property int $height
 * @property Carbon|null $created_at
 */
class TicketMedia extends Model
{
    protected $table = 'ticket_media';

    // Yalnızca created_at yönetilir (updated_at kolonu yok).
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'ticket_id',
        'uploaded_by',
        'type',
        'disk',
        'path',
        'thumb_path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
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
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TicketMediaType::class,
            'size' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
