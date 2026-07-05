<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

test('the test suite runs against the pgsql kenttalep_test database', function (): void {
    expect(DB::connection()->getDriverName())->toBe('pgsql')
        ->and(DB::connection()->getDatabaseName())->toBe('kenttalep_test');
});
