<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicYearCurriculumResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,

            'subjects_count' => $this->subjects_count,
            'units_count' => $this->units_count,
            'topics_count' => $this->topics_count,

            'subjects' => $this->curriculumSubjects
                ->map(function ($subject) {
                    return [
                        'id' => $subject->id,
                        'name' => $subject->name,
                        'code' => $subject->code,
                        'slug' => $subject->slug,
                        'description' => $subject->description,
                        'icon' => $subject->icon,
                        'image' => $subject->image,

                        'units' => $subject->units
                            ->map(function ($unit) {
                                return [
                                    'id' => $unit->id,
                                    'name' => $unit->name,
                                    'slug' => $unit->slug,
                                    'description' => $unit->description,
                                    'sort_order' => $unit->sort_order,

                                    'unit_topics' => $unit->unitTopics
                                        ->map(function ($unitTopic) {
                                            return [
                                                'id' => $unitTopic->id,
                                                'title' => $unitTopic->title,
                                                'slug' => $unitTopic->slug,
                                                'sort_order' => $unitTopic->sort_order,

                                                'topics' => $unitTopic->lmsTopics
                                                    ->map(function ($topic) {
                                                        return [
                                                            'id' => $topic->id,
                                                            'title' => $topic->title,
                                                            'slug' => $topic->slug,
                                                            'description' => $topic->description,
                                                            'bottom_line' => $topic->bottom_line,
                                                            'module_number' => $topic->module_number,
                                                            'is_protected' => (bool) $topic->is_protected,
                                                            'order' => $topic->order,

                                                            'subtopics' => $topic->subtopics
                                                                ->map(function ($subtopic) {
                                                                    return [
                                                                        'id' => $subtopic->id,
                                                                        'title' => $subtopic->title,
                                                                        'slug' => $subtopic->slug,
                                                                        'description' => $subtopic->description,
                                                                        'bottom_line' => $subtopic->bottom_line,
                                                                        'is_protected' => (bool) $subtopic->is_protected,
                                                                        'order' => $subtopic->order,
                                                                    ];
                                                                })
                                                                ->values(),
                                                        ];
                                                    })
                                                    ->values(),
                                            ];
                                        })
                                        ->values(),
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values(),
        ];
    }
}
