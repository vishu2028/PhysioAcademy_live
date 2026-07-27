<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class SearchTopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,

            'description' => $this->description
                ? Str::limit(
                    strip_tags($this->description),
                    180
                )
                : null,

            'module_number' => $this->module_number,
            'is_protected' => (bool) $this->is_protected,

            'subject' => $this->whenLoaded(
                'subject',
                function () {
                    if (! $this->subject) {
                        return null;
                    }

                    return [
                        'id' => $this->subject->id,
                        'name' => $this->subject->name,
                        'slug' => $this->subject->slug,
                    ];
                }
            ),

            'academic_year' => $this->whenLoaded(
                'academicYear',
                function () {
                    if (! $this->academicYear) {
                        return null;
                    }

                    return [
                        'id' => $this->academicYear->id,
                        'name' => $this->academicYear->name,
                        'slug' => $this->academicYear->slug,
                    ];
                }
            ),
        ];
    }
}
