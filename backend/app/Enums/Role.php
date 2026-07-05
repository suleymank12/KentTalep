<?php

declare(strict_types=1);

namespace App\Enums;

enum Role: string
{
    case Citizen = 'citizen';
    case Staff = 'staff';
    case Manager = 'manager';
    case Admin = 'admin';

    /**
     * İnsan tarafından okunabilir Türkçe rol etiketi.
     */
    public function label(): string
    {
        return match ($this) {
            self::Citizen => 'Vatandaş',
            self::Staff => 'Personel',
            self::Manager => 'Yönetici',
            self::Admin => 'Admin',
        };
    }
}
