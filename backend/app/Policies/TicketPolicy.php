<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Support\TicketAccess;

class TicketPolicy
{
    public function create(User $user): bool
    {
        return $user->hasRole(Role::Citizen->value) || $user->hasRole(Role::Admin->value);
    }

    public function view(User $user, Ticket $ticket): bool
    {
        return TicketAccess::canView($user, $ticket);
    }

    public function update(User $user, Ticket $ticket): bool
    {
        return TicketAccess::isManager($user) || (int) $ticket->user_id === (int) $user->getKey();
    }

    public function delete(User $user, Ticket $ticket): bool
    {
        return $user->hasRole(Role::Admin->value);
    }
}
