<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;

/**
 * Tek bir durum geçişinin izin ve gereklilik kurallarını taşıyan değer nesnesi.
 */
final class TicketTransition
{
    /**
     * @param  list<Role>  $roles  Rolüyle her zaman izinli olan roller
     * @param  bool  $owner  Talep sahibi izinli mi
     * @param  bool  $assignee  Atanan personel izinli mi
     * @param  bool  $requiresNote  Not (açıklama) zorunlu mu
     * @param  bool  $requiresAssignee  Hedef personel (assigned_to) zorunlu mu
     * @param  bool  $requiresAfterMedia  En az bir "sonrası" medya zorunlu mu
     */
    public function __construct(
        public array $roles,
        public bool $owner = false,
        public bool $assignee = false,
        public bool $requiresNote = false,
        public bool $requiresAssignee = false,
        public bool $requiresAfterMedia = false,
    ) {}
}
