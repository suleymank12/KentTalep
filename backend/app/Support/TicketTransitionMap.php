<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Role;
use App\Enums\TicketStatus;

/**
 * Talep durum geçişlerinin sabit tablosu: hangi durumdan hangisine, kim,
 * hangi gerekliliklerle geçebilir. Terminal durumlardan çıkış tanımlı değildir.
 */
final class TicketTransitionMap
{
    /**
     * @return array<string, array<string, TicketTransition>>
     */
    public static function map(): array
    {
        return [
            TicketStatus::Pending->value => [
                TicketStatus::Assigned->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    requiresAssignee: true,
                ),
                TicketStatus::Rejected->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    requiresNote: true,
                ),
                TicketStatus::Cancelled->value => new TicketTransition(
                    roles: [Role::Admin],
                    owner: true,
                ),
            ],
            TicketStatus::Assigned->value => [
                TicketStatus::Assigned->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    requiresAssignee: true,
                ),
                TicketStatus::InProgress->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    assignee: true,
                ),
                TicketStatus::Cancelled->value => new TicketTransition(
                    roles: [Role::Admin],
                    owner: true,
                ),
            ],
            TicketStatus::InProgress->value => [
                TicketStatus::Resolved->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    assignee: true,
                    requiresAfterMedia: true,
                ),
            ],
            TicketStatus::Resolved->value => [
                TicketStatus::Closed->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    owner: true,
                ),
                TicketStatus::InProgress->value => new TicketTransition(
                    roles: [Role::Manager, Role::Admin],
                    owner: true,
                    requiresNote: true,
                ),
            ],
        ];
    }

    public static function find(TicketStatus $from, TicketStatus $to): ?TicketTransition
    {
        return self::map()[$from->value][$to->value] ?? null;
    }
}
