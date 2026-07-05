<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;
use App\Models\Ticket;
use App\Models\User;

/**
 * Talep erişim/rol yardımcıları — policy'ler ve controller'lar arasında
 * paylaşılan tek doğruluk kaynağı.
 */
final class TicketAccess
{
    public static function isManager(User $user): bool
    {
        return $user->hasRole(Role::Manager->value) || $user->hasRole(Role::Admin->value);
    }

    /**
     * Kullanıcı talebi görebilir mi? (sahibi, atanan personel, yönetici, admin)
     */
    public static function canView(User $user, Ticket $ticket): bool
    {
        if (self::isManager($user)) {
            return true;
        }

        $userId = (int) $user->getKey();

        if ((int) $ticket->user_id === $userId) {
            return true;
        }

        return $ticket->assigned_to !== null && (int) $ticket->assigned_to === $userId;
    }
}
