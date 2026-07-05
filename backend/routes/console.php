<?php

declare(strict_types=1);

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function (): void {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Süresi dolmuş Sanctum token'larını her gün, 24 saatten eski olanları temizle.
Schedule::command('sanctum:prune-expired --hours=24')->daily();
