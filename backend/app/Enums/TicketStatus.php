<?php

declare(strict_types=1);

namespace App\Enums;

enum TicketStatus: string
{
    case Pending = 'pending';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Beklemede',
            self::Assigned => 'Atandı',
            self::InProgress => 'Devam Ediyor',
            self::Resolved => 'Çözüldü',
            self::Closed => 'Kapatıldı',
            self::Cancelled => 'İptal Edildi',
            self::Rejected => 'Reddedildi',
        };
    }

    /**
     * Terminal (son) durumlar: bir daha geçiş yapılamaz.
     */
    public function isTerminal(): bool
    {
        return match ($this) {
            self::Closed, self::Cancelled, self::Rejected => true,
            default => false,
        };
    }
}
