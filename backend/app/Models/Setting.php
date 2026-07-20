<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        // Ayar değişince public-settings önbelleği geçersiz kılınır.
        static::saved(fn () => Cache::forget('public-settings'));
        static::deleted(fn () => Cache::forget('public-settings'));
    }
}
