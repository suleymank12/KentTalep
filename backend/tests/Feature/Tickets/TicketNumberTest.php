<?php

declare(strict_types=1);

use App\Services\TicketNumberGenerator;
use Illuminate\Support\Carbon;

it('generates sequential yearly ticket numbers', function (): void {
    $generator = app(TicketNumberGenerator::class);

    expect($generator->generate())->toBe(now()->year.'-000001')
        ->and($generator->generate())->toBe(now()->year.'-000002');
});

it('restarts the counter for a new year', function (): void {
    $generator = app(TicketNumberGenerator::class);

    Carbon::setTestNow('2025-06-01 10:00:00');
    expect($generator->generate())->toBe('2025-000001');

    Carbon::setTestNow('2026-06-01 10:00:00');
    expect($generator->generate())->toBe('2026-000001');

    Carbon::setTestNow();
});
