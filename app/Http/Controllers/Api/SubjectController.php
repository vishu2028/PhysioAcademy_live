<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Models\Topic;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SubjectController extends Controller
{
    /**
     * Return active subjects list.
     */
    public function index(): AnonymousResourceCollection
    {
        $subjects = Subject::query()
            ->active()
            ->orderBy('order')
            ->orderBy('name')
            ->get();

        return SubjectResource::collection($subjects);
    }

    /**
     * Return the complete curriculum for a selected subject.
     */
    public function curriculum(
        Request $request,
        string $slug
    ): JsonResponse {
        $subject = Subject::query()
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        /*
         * Get all active main topics of the selected subject.
         *
         * Strict unit relationship filtering is deliberately avoided.
         * If an old topic does not have a unit_topic_id, it will still
         * appear under "Unassigned Unit" instead of disappearing.
         */
        $topics = Topic::query()
            ->where('subject_id', $subject->id)
            ->where('status', true)
            ->whereNull('parent_id')
            ->with([
                'academicYear:id,name,slug',

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
         * Group subject topics by their unit.
         */
        $units = $topics
            ->groupBy(function ($topic) {
                return $topic->unitTopic?->unit_id
                    ?: 'unassigned-unit';
            })
            ->map(function ($unitTopics) {
                $unit = $unitTopics
                    ->first()
                    ?->unitTopic
                    ?->unit;

                /*
                 * Group topics inside the unit by unit topic.
                 */
                $unitTopicGroups = $unitTopics
                    ->groupBy(function ($topic) {
                        return $topic->unit_topic_id
                            ?: 'general';
                    })
                    ->map(function ($topicsInGroup) {
                        $unitTopic = $topicsInGroup
                            ->first()
                            ?->unitTopic;

                        return [
                            'id' => $unitTopic?->id,
                            'title' => $unitTopic?->title
                                    ?? 'General',
                            'slug' => $unitTopic?->slug,
                            'sort_order' => $unitTopic?->sort_order,

                            'topics_count' => $topicsInGroup->count(),

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

                                        'academic_year' => $topic->academicYear
                                            ? [
                                                'id' => $topic->academicYear->id,
                                                'name' => $topic->academicYear->name,
                                                'slug' => $topic->academicYear->slug,
                                            ]
                                            : null,

                                        'subtopics_count' => $topic
                                            ->subtopics
                                            ->count(),

                                        'subtopics' => $topic
                                            ->subtopics
                                            ->map(function ($subtopic) {
                                                return [
                                                    'id' => $subtopic->id,
                                                    'title' => $subtopic->title,
                                                    'slug' => $subtopic->slug,
                                                    'description' => $subtopic->description,
                                                    'bottom_line' => $subtopic->bottom_line,
                                                    'module_number' => $subtopic->module_number,
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
                    ->sortBy(function ($unitTopic) {
                        return $unitTopic['sort_order']
                            ?? PHP_INT_MAX;
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

                    'unit_topics_count' => $unitTopicGroups->count(),
                    'unit_topics' => $unitTopicGroups,
                ];
            })
            ->sortBy(function ($unit) {
                return $unit['sort_order']
                    ?? PHP_INT_MAX;
            })
            ->values();

        $subjectData = (new SubjectResource($subject))
            ->resolve($request);

        return response()->json([
            'data' => array_merge($subjectData, [
                'units_count' => $units->count(),
                'topics_count' => $topics->count(),

                'subtopics_count' => $topics->sum(
                    fn ($topic) => $topic->subtopics->count()
                ),

                'units' => $units,
            ]),
        ]);
    }
}
