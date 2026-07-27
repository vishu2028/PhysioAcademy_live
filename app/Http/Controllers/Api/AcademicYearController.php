<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AcademicYearResource;
use App\Models\AcademicYear;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AcademicYearController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $academicYears = AcademicYear::query()
            ->active()
            ->withCurriculumCounts()
            ->orderBy('order')
            ->get();

        return AcademicYearResource::collection($academicYears);
    }

    public function curriculum(string $slug): JsonResponse
    {
        $academicYear = AcademicYear::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        /*
         * Selected academic year ke tamam active parent topics load karo.
         *
         * Hum strict whereHas() filtering nahi laga rahe, kyun ke agar kisi
         * topic ki unit-topic mapping incomplete ho to topic response se
         * completely disappear ho jata hai.
         */
        $topics = Topic::query()
            ->where('academic_year_id', $academicYear->id)
            ->where('status', true)
            ->whereNull('parent_id')
            ->with([
                'subject:id,name,code,slug,description,icon,image,status,order',

                'unitTopic:id,subject_id,unit_id,title,slug,sort_order,status',

                'unitTopic.unit:id,subject_id,name,slug,description,sort_order,is_active',

                'subtopics' => function ($query) {
                    $query
                        ->where('status', true)
                        ->orderBy('order');
                },
            ])
            ->orderBy('order')
            ->get();

        /*
         * Topics ko subject ke mutabiq group karo.
         */
        $subjects = $topics
            ->groupBy(function ($topic) {
                return $topic->subject_id ?: 'unassigned-subject';
            })
            ->map(function ($subjectTopics) {
                $subject = $subjectTopics->first()->subject;

                /*
                 * Subject ke topics ko unit ke mutabiq group karo.
                 */
                $units = $subjectTopics
                    ->groupBy(function ($topic) {
                        return $topic->unitTopic?->unit_id
                            ?: 'unassigned-unit';
                    })
                    ->map(function ($unitTopics) {
                        $unit = $unitTopics
                            ->first()
                            ->unitTopic
                            ?->unit;

                        /*
                         * Unit ke topics ko unit-topic/category ke
                         * mutabiq group karo.
                         */
                        $unitTopicGroups = $unitTopics
                            ->groupBy(function ($topic) {
                                return $topic->unit_topic_id
                                    ?: 'general';
                            })
                            ->map(function ($topicsInGroup) {
                                $unitTopic = $topicsInGroup
                                    ->first()
                                    ->unitTopic;

                                return [
                                    'id' => $unitTopic?->id,
                                    'title' => $unitTopic?->title
                                            ?? 'General',
                                    'slug' => $unitTopic?->slug,
                                    'sort_order' => $unitTopic?->sort_order,

                                    'topics' => $topicsInGroup
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

                                                'subtopics' => $topic
                                                    ->subtopics
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
                            ->values();

                        return [
                            'id' => $unit?->id,
                            'name' => $unit?->name
                                    ?? 'Unassigned Unit',
                            'slug' => $unit?->slug,
                            'description' => $unit?->description,
                            'sort_order' => $unit?->sort_order,
                            'is_active' => $unit
                                ? (bool) $unit->is_active
                                : null,
                            'unit_topics' => $unitTopicGroups,
                        ];
                    })
                    ->values();

                return [
                    'id' => $subject?->id,
                    'name' => $subject?->name
                            ?? 'Unassigned Subject',
                    'code' => $subject?->code,
                    'slug' => $subject?->slug,
                    'description' => $subject?->description,
                    'icon' => $subject?->icon,
                    'image' => $subject?->image,
                    'units' => $units,
                ];
            })
            ->values();

        $unitsCount = $subjects->sum(function ($subject) {
            return count($subject['units']);
        });

        return response()->json([
            'data' => [
                'id' => $academicYear->id,
                'name' => $academicYear->name,
                'slug' => $academicYear->slug,
                'description' => $academicYear->description,

                'subjects_count' => $subjects->count(),
                'units_count' => $unitsCount,
                'topics_count' => $topics->count(),

                'subjects' => $subjects,
            ],
        ]);
    }
}
