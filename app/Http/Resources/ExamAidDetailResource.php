<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExamAidDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,

            // Complete questions detail API mein return hogi.
            'viva_question' => $this->viva_question,
            'exam_question' => $this->exam_question,

            'subject' => $this->subject
                ? [
                    'id' => $this->subject->id,
                    'name' => $this->subject->name,
                    'slug' => $this->subject->slug,
                ]
                : null,

            'unit' => $this->unit
                ? [
                    'id' => $this->unit->id,
                    'name' => $this->unit->name,
                    'slug' => $this->unit->slug,
                ]
                : null,

            'academic_year' => $this->academicYear
                ? [
                    'id' => $this->academicYear->id,
                    'name' => $this->academicYear->name,
                    'slug' => $this->academicYear->slug,
                ]
                : null,

            'semester' => $this->semester
                ? [
                    'id' => $this->semester->id,
                    'name' => $this->semester->name,
                    'order' => $this->semester->order,
                ]
                : null,

            'materials' => $this->materials
                ->map(function ($material) {
                    return [
                        'id' => $material->id,
                        'title' => $material->title,
                        'type' => $material->type,
                        'content' => $material->content,
                        'file_url' => $material->file_url,
                        'url' => $material->url,
                        'order' => $material->order,
                    ];
                })
                ->values(),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
