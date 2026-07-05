<?php

declare(strict_types=1);

use function Pest\Laravel\get;

test('the health check endpoint responds successfully', function (): void {
    get('/up')->assertOk();
});
