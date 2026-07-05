<?php

declare(strict_types=1);

use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\TicketStatusLog;
use Database\Seeders\DatabaseSeeder;

use function Pest\Laravel\seed;

it('seeds a realistic ticket dataset covering every status', function (): void {
    seed(DatabaseSeeder::class);

    $ticketCount = Ticket::count();

    expect($ticketCount)->toBeGreaterThanOrEqual(50)
        ->and(TicketStatusLog::count())->toBeGreaterThanOrEqual($ticketCount);

    foreach (TicketStatus::cases() as $status) {
        expect(Ticket::where('status', $status->value)->exists())->toBeTrue();
    }
});
