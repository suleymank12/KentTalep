<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Enums\TicketStatus;
use App\Models\TicketMedia;
use App\Models\User;
use App\Support\TicketAccess;

class TicketMediaPolicy
{
    /**
     * Yükleyenin medyayı silebileceği durumlar (açık allow-list). Resolve
     * anından itibaren medya denetim kanıtıdır; resolved/closed'da yükleyen
     * silemez. reopen ile in_progress'e dönerse tekrar silinebilir (iş
     * yeniden yapılıyor, yeni resolve yeni fotoğraf ister).
     *
     * @var list<TicketStatus>
     */
    private const UPLOADER_DELETABLE_STATUSES = [
        TicketStatus::Pending,
        TicketStatus::Assigned,
        TicketStatus::InProgress,
    ];

    public function view(User $user, TicketMedia $media): bool
    {
        return TicketAccess::canView($user, $media->ticket()->firstOrFail());
    }

    public function delete(User $user, TicketMedia $media): bool
    {
        // Admin her durumda silebilir: istisnai silmeler (KVKK talebi, hatalı
        // yükleme) admin yetkisinden yürür.
        if ($user->hasRole(Role::Admin->value)) {
            return true;
        }

        return (int) $media->uploaded_by === (int) $user->getKey()
            && in_array($media->ticket()->firstOrFail()->status, self::UPLOADER_DELETABLE_STATUSES, true);
    }
}
