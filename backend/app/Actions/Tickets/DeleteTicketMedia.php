<?php

declare(strict_types=1);

namespace App\Actions\Tickets;

use App\Models\TicketMedia;
use Illuminate\Support\Facades\Storage;

final class DeleteTicketMedia
{
    /**
     * Medya kaydını ve diskteki asıl + thumbnail dosyalarını siler.
     * Yetki (yükleyen/terminal-değil veya admin) TicketMediaPolicy'de denetlenir.
     */
    public function handle(TicketMedia $media): void
    {
        Storage::disk($media->disk)->delete([$media->path, $media->thumb_path]);

        $media->delete();
    }
}
