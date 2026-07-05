<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin UserDevice
 */
class UserDeviceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'device_name' => $this->device_name,
            'platform' => $this->platform,
            'push_token' => $this->push_token,
            'last_seen_at' => $this->last_seen_at,
        ];
    }
}
