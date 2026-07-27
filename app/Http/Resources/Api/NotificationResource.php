<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform notification for the mobile API.
     */
    public function toArray(Request $request): array
    {
        $data = is_array($this->data)
            ? $this->data
            : [];

        return [
            'id' => $this->id,

            'type' => class_basename($this->type),

            'title' => $data['title'] ?? null,

            'message' => $data['message']
                    ?? $data['body']
                    ?? null,

            'action_url' => $data['action_url']
                    ?? $data['url']
                    ?? null,

            'data' => $data,

            'is_read' => ! is_null($this->read_at),

            'read_at' => $this->read_at
                ?->toISOString(),

            'created_at' => $this->created_at
                ?->toISOString(),

            'updated_at' => $this->updated_at
                ?->toISOString(),
        ];
    }
}
