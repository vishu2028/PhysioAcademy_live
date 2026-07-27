<?php

namespace App\Http\Resources;

use App\Models\LearningMaterial;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class BookmarkResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $bookmarkable = $this->bookmarkable;

        return [
            'id' => $this->id,

            'type' => $this->getBookmarkType(
                $bookmarkable
            ),

            'item' => $this->getBookmarkData(
                $bookmarkable
            ),

            'bookmarked_at' => $this->created_at
                ?->toISOString(),
        ];
    }

    private function getBookmarkType(mixed $bookmarkable): ?string
    {
        if ($bookmarkable instanceof Topic) {
            return 'topic';
        }

        if ($bookmarkable instanceof LearningMaterial) {
            return 'material';
        }

        return null;
    }
    public function destroy(
        Request $request,
        int $id
    ): JsonResponse {
        /*
         * Logged-in user ke bookmarks mein hi ID search hogi.
         *
         * Is se koi user kisi doosre user ka bookmark
         * delete nahi kar sakta.
         */
        $bookmark = $request->user()
            ->bookmarks()
            ->findOrFail($id);

        $bookmark->delete();

        return response()->json([
            'message' => 'Bookmark deleted successfully.',
            'data' => [
                'bookmark_id' => $id,
                'is_bookmarked' => false,
            ],
        ]);
    }

    private function getBookmarkData(mixed $bookmarkable): ?array
    {
        if ($bookmarkable instanceof Topic) {
            return [
                'id' => $bookmarkable->id,
                'title' => $bookmarkable->title,
                'slug' => $bookmarkable->slug,

                'description' => $bookmarkable->description
                    ? Str::limit(
                        strip_tags($bookmarkable->description),
                        180
                    )
                    : null,

                'module_number' => $bookmarkable
                    ->module_number,

                'is_protected' => (bool) $bookmarkable
                    ->is_protected,

                'subject' => $bookmarkable->subject
                    ? [
                        'id' => $bookmarkable->subject->id,
                        'name' => $bookmarkable->subject->name,
                        'slug' => $bookmarkable->subject->slug,
                    ]
                    : null,

                'academic_year' => $bookmarkable->academicYear
                    ? [
                        'id' => $bookmarkable
                            ->academicYear
                            ->id,

                        'name' => $bookmarkable
                            ->academicYear
                            ->name,

                        'slug' => $bookmarkable
                            ->academicYear
                            ->slug,
                    ]
                    : null,
            ];
        }

        if ($bookmarkable instanceof LearningMaterial) {
            return [
                'id' => $bookmarkable->id,
                'title' => $bookmarkable->title,
                'material_type' => $bookmarkable->type,
                'content' => $bookmarkable->content,
                'file_url' => $bookmarkable->file_url,
                'url' => $bookmarkable->url,
                'order' => $bookmarkable->order,

                'topic' => $bookmarkable->topic
                    ? [
                        'id' => $bookmarkable->topic->id,
                        'title' => $bookmarkable->topic->title,
                        'slug' => $bookmarkable->topic->slug,
                        'is_protected' => (bool) $bookmarkable
                            ->topic
                            ->is_protected,
                    ]
                    : null,
            ];
        }

        return null;
    }

}
