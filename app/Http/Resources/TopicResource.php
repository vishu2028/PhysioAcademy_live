<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TopicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'bottom_line' => $this->bottom_line,
            'module_number' => $this->module_number,
            'order' => $this->order,
            'is_protected' => (bool) $this->is_protected,
            'is_bookmarked' => (bool) (
                $this->is_bookmarked ?? false
            ),

            'subject' => $this->whenLoaded(
                'subject',
                fn () => new SubjectResource($this->subject)
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

            'semester' => $this->whenLoaded(
                'semester',
                function () {
                    if (! $this->semester) {
                        return null;
                    }

                    return [
                        'id' => $this->semester->id,
                        'name' => $this->semester->name,
                        'order' => $this->semester->order,
                    ];
                }
            ),

            'unit' => $this->whenLoaded(
                'unitTopic',
                function () {
                    $unit = $this->unitTopic?->unit;

                    if (! $unit) {
                        return null;
                    }

                    return [
                        'id' => $unit->id,
                        'name' => $unit->name,
                        'slug' => $unit->slug,
                        'description' => $unit->description,
                        'sort_order' => $unit->sort_order,
                    ];
                }
            ),

            'unit_topic' => $this->whenLoaded(
                'unitTopic',
                function () {
                    if (! $this->unitTopic) {
                        return null;
                    }

                    return [
                        'id' => $this->unitTopic->id,
                        'title' => $this->unitTopic->title,
                        'slug' => $this->unitTopic->slug,
                        'sort_order' => $this->unitTopic->sort_order,
                    ];
                }
            ),

            'subtopics_count' => $this->whenLoaded(
                'subtopics',
                fn () => $this->subtopics->count()
            ),

            'subtopics' => $this->whenLoaded(
                'subtopics',
                function () {
                    return $this->subtopics
                        ->map(function ($subtopic) {
                            return [
                                'id' => $subtopic->id,
                                'title' => $subtopic->title,
                                'slug' => $subtopic->slug,
                                'description' => $subtopic->description,
                                'bottom_line' => $subtopic->bottom_line,
                                'module_number' => $subtopic->module_number,
                                'order' => $subtopic->order,
                                'is_protected' => (bool) $subtopic->is_protected,
                                'is_bookmarked' => (bool) (
                                    $subtopic->is_bookmarked ?? false
                                ),
                            ];
                        })
                        ->values();
                }
            ),

            'materials_count' => $this->whenLoaded(
                'materials',
                fn () => $this->materials->count()
            ),

            'materials' => LearningMaterialResource::collection(
                $this->whenLoaded('materials')
            ),

            'related_topics' => $this->whenLoaded(
                'relatedTopics',
                function () {
                    return $this->relatedTopics
                        ->map(function ($topic) {
                            return [
                                'id' => $topic->id,
                                'title' => $topic->title,
                                'slug' => $topic->slug,
                                'description' => $topic->description,
                                'is_protected' => (bool) $topic->is_protected,

                                'academic_year' => $topic->academicYear
                                    ? [
                                        'id' => $topic->academicYear->id,
                                        'name' => $topic->academicYear->name,
                                        'slug' => $topic->academicYear->slug,
                                    ]
                                    : null,
                            ];
                        })
                        ->values();
                }
            ),

            'created_at' => $this->created_at?->format(
                'Y-m-d H:i:s'
            ),
        ];
    }
}
