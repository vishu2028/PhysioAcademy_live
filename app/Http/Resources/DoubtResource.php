<?php

namespace App\Http\Resources;

use App\Models\Doubt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DoubtResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'topic' => $this->topic,
            'message' => $this->message,
            'answer' => $this->answer,
            'status' => $this->status,

            'is_answered' => (
                $this->status === Doubt::STATUS_ANSWERED
                && filled($this->answer)
            ),

            'academic_year' => $this->academicYear
                ? [
                    'id' => $this->academicYear->id,
                    'name' => $this->academicYear->name,
                    'slug' => $this->academicYear->slug,
                ]
                : null,

            'subject' => $this->subject
                ? [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                    'slug' => $this->subject->slug,
                ]
                : null,

            'answered_by' => $this->answeredBy
                ? [
                    'id' => $this->answeredBy->id,
                    'name' => $this->answeredBy->name,
                ]
                : null,

            'answered_at' => $this->answered_at
                ?->toISOString(),

            'created_at' => $this->created_at
                ?->toISOString(),

            'updated_at' => $this->updated_at
                ?->toISOString(),
        ];
    }
}
