<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class ExamAidResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,

            'description' => $this->description
                ? Str::limit(
                    strip_tags($this->description),
                    180
                )
                : null,

            /*
             * List API mein full questions return nahi kar rahe.
             * Sirf availability show hogi.
             */
            'has_viva_question' => trim(
                    (string) $this->viva_question
                ) !== '',

            'has_exam_question' => trim(
                    (string) $this->exam_question
                ) !== '',

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

            'materials_count' => (int) $this->materials_count,

            'material_types' => $this->relationLoaded('materials')
                ? $this->materials
                    ->pluck('type')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all()
                : [],

            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
