<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class LearningMaterialResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $fileUrl = null;

        if ($this->file_path) {
            $fileUrl = Str::startsWith(
                $this->file_path,
                ['http://', 'https://']
            )
                ? $this->file_path
                : Storage::disk('public')->url($this->file_path);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'content' => $this->content,
            'file_url' => $fileUrl,
            'url' => $this->url,
            'order' => $this->order,
            'is_bookmarked' => (bool) (
                $this->is_bookmarked ?? false
            ),
        ];
    }
}
