<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TopicResource;
use App\Models\LearningMaterial;
use App\Models\Topic;
use Illuminate\Http\Request;

class TopicController extends Controller
{
    public function show(Request $request, string $slug)
    {
        $topic = Topic::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'subject:id,name,slug,description,icon,image,status',

                'academicYear:id,name,slug',

                'semester:id,name,academic_year_id,order',

                'unitTopic:id,subject_id,unit_id,title,slug,sort_order,status',

                'unitTopic.unit:id,subject_id,name,slug,description,sort_order,is_active',

                'subtopics' => function ($query) {
                    $query
                        ->where('status', true)
                        ->orderBy('order');
                },

                'materials' => function ($query) {
                    $query->orderBy('order');
                },
            ])
            ->firstOrFail();

        /*
         * Public topic No Auth ke saath open ho sakta hai.
         * Protected topic ke liye valid Sanctum token required hai.
         */
        $user = $request->user('sanctum');

        if ($topic->is_protected && ! $user) {
            return response()->json([
                'message' => 'Please login to access this topic.',
            ], 401);
        }

        /*
         * Related topics from the same subject.
         */
        $relatedTopics = Topic::query()
            ->active()
            ->where('subject_id', $topic->subject_id)
            ->where('id', '!=', $topic->id)
            ->whereNull('parent_id')
            ->with('academicYear:id,name,slug')
            ->orderBy('order')
            ->limit(4)
            ->get();

        $topic->setRelation('relatedTopics', $relatedTopics);

        /*
         * Default bookmark status when no user is logged in.
         */
        $topic->setAttribute('is_bookmarked', false);

        foreach ($topic->subtopics as $subtopic) {
            $subtopic->setAttribute('is_bookmarked', false);
        }

        foreach ($topic->materials as $material) {
            $material->setAttribute('is_bookmarked', false);
        }

        /*
         * Get bookmark statuses when user sends a valid token.
         */
        if ($user) {
            $topicIds = $topic->subtopics
                ->pluck('id')
                ->prepend($topic->id);

            $bookmarkedTopicIds = $user->bookmarks()
                ->where('bookmarkable_type', Topic::class)
                ->whereIn('bookmarkable_id', $topicIds)
                ->pluck('bookmarkable_id')
                ->map(fn ($id) => (int) $id);

            $topic->setAttribute(
                'is_bookmarked',
                $bookmarkedTopicIds->contains($topic->id)
            );

            foreach ($topic->subtopics as $subtopic) {
                $subtopic->setAttribute(
                    'is_bookmarked',
                    $bookmarkedTopicIds->contains($subtopic->id)
                );
            }

            $materialIds = $topic->materials->pluck('id');

            $bookmarkedMaterialIds = $user->bookmarks()
                ->where(
                    'bookmarkable_type',
                    LearningMaterial::class
                )
                ->whereIn('bookmarkable_id', $materialIds)
                ->pluck('bookmarkable_id')
                ->map(fn ($id) => (int) $id);

            foreach ($topic->materials as $material) {
                $material->setAttribute(
                    'is_bookmarked',
                    $bookmarkedMaterialIds->contains($material->id)
                );
            }
        }

        return new TopicResource($topic);
    }
}
