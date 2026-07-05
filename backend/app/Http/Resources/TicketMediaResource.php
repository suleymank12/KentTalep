<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\TicketMedia;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TicketMedia
 */
class TicketMediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),
            'url' => "/api/ticket-media/{$this->id}",
            'thumb_url' => "/api/ticket-media/{$this->id}/thumb",
            'width' => $this->width,
            'height' => $this->height,
            'created_at' => $this->created_at,
        ];
    }
}
