<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\DevicePlatform;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDevice extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'token_id',
        'device_name',
        'platform',
        'push_token',
        'last_seen_at',
    ];

    /**
     * Cihazın sahibi kullanıcı.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform' => DevicePlatform::class,
            'last_seen_at' => 'datetime',
        ];
    }
}
