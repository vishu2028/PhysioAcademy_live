<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookmarkResource;
use App\Models\LearningMaterial;
use App\Models\Topic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
class BookmarkController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'type' => [
                'nullable',
                'string',
                Rule::in([
                    'topic',
                    'material',
                ]),
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $type = $validated['type'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 15);

        /*
         * getMorphClass() use karne se query normal class names
         * aur Laravel morph-map dono ke saath work karegi.
         */
        $topicMorphType = (new Topic())->getMorphClass();

        $materialMorphType = (new LearningMaterial())
            ->getMorphClass();

        $bookmarks = $request->user()
            ->bookmarks()

            // Optional topic/material filter.
            ->when(
                $type === 'topic',
                function ($query) use ($topicMorphType) {
                    $query->where(
                        'bookmarkable_type',
                        $topicMorphType
                    );
                }
            )

            ->when(
                $type === 'material',
                function ($query) use ($materialMorphType) {
                    $query->where(
                        'bookmarkable_type',
                        $materialMorphType
                    );
                }
            )

            /*
             * Deleted ya inactive bookmarkable records
             * response mein return nahi honge.
             */
            ->where(function (Builder $query) {
                $query
                    ->whereHasMorph(
                        'bookmarkable',
                        [Topic::class],
                        function (Builder $topicQuery) {
                            $topicQuery->where('status', true);
                        }
                    )
                    ->orWhereHasMorph(
                        'bookmarkable',
                        [LearningMaterial::class],
                        function (Builder $materialQuery) {
                            $materialQuery->whereHas(
                                'topic',
                                function (Builder $topicQuery) {
                                    $topicQuery->where(
                                        'status',
                                        true
                                    );
                                }
                            );
                        }
                    );
            })

            /*
             * Polymorphic relations ke nested relations
             * efficiently eager-load hongi.
             */
            ->with([
                'bookmarkable' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Topic::class => [
                            'subject:id,name,slug',
                            'academicYear:id,name,slug',
                        ],

                        LearningMaterial::class => [
                            'topic:id,title,slug,status,is_protected',
                        ],
                    ]);
                },
            ])

            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return BookmarkResource::collection($bookmarks);
    }
    public function toggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => [
                'required',
                'string',
                Rule::in([
                    'topic',
                    'material',
                ]),
            ],

            'id' => [
                'required',
                'integer',
                'min:1',
            ],
        ]);

        /*
         * Request type ke mutabiq bookmarkable
         * model find karein.
         */
        if ($validated['type'] === 'topic') {
            $bookmarkable = Topic::query()
                ->where('status', true)
                ->findOrFail($validated['id']);
        } else {
            $bookmarkable = LearningMaterial::query()
                ->whereHas('topic', function ($topicQuery) {
                    $topicQuery->where('status', true);
                })
                ->findOrFail($validated['id']);
        }

        $bookmark = $request->user()
            ->bookmarks()
            ->where(
                'bookmarkable_type',
                $bookmarkable->getMorphClass()
            )
            ->where(
                'bookmarkable_id',
                $bookmarkable->getKey()
            )
            ->first();

        /*
         * Bookmark pehle se hai to remove karein.
         */
        if ($bookmark) {
            $bookmark->delete();

            return response()->json([
                'message' => 'Bookmark removed successfully.',
                'data' => [
                    'type' => $validated['type'],
                    'item_id' => $bookmarkable->getKey(),
                    'is_bookmarked' => false,
                ],
            ]);
        }

        /*
         * Bookmark nahi hai to create karein.
         */
        $bookmark = $request->user()
            ->bookmarks()
            ->create([
                'bookmarkable_id' => $bookmarkable->getKey(),
                'bookmarkable_type' => $bookmarkable->getMorphClass(),
            ]);

        return response()->json([
            'message' => 'Bookmark added successfully.',
            'data' => [
                'bookmark_id' => $bookmark->id,
                'type' => $validated['type'],
                'item_id' => $bookmarkable->getKey(),
                'is_bookmarked' => true,
            ],
        ], 201);
    }
}
