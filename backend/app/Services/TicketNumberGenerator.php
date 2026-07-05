<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;
use RuntimeException;

final class TicketNumberGenerator
{
    /**
     * Yıl bazlı sıralı, benzersiz talep numarası üretir (ör. 2026-000482).
     */
    public function generate(): string
    {
        $year = (int) now()->year;

        // Tek atomik ifade: ON CONFLICT ... RETURNING sayacı SQL düzeyinde
        // artırır; "oku → +1 → yaz" olmadığından eşzamanlı isteklerde lost
        // update / çakışan numara oluşmaz.
        $row = DB::selectOne(
            'INSERT INTO ticket_counters (year, last_value) VALUES (?, 1)
             ON CONFLICT (year) DO UPDATE SET last_value = ticket_counters.last_value + 1
             RETURNING last_value',
            [$year],
        );

        if ($row === null) {
            throw new RuntimeException('Talep numarası üretilemedi.');
        }

        return sprintf('%d-%06d', $year, (int) $row->last_value);
    }
}
