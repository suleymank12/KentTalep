<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketMediaType: string
{
    case Before = 'before';
    case After = 'after';

    public function label(): string
    {
        return match ($this) {
            self::Before => 'Öncesi',
            self::After => 'Sonrası',
        };
    }
}
