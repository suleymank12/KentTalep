<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

test('postgis extension is available in the test database', function (): void {
    $result = DB::selectOne('SELECT postgis_version() AS v');

    expect($result)->not->toBeNull()
        ->and($result->v)->not->toBeEmpty();
});
