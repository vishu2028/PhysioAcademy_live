<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchTopicResource;
use App\Models\Topic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => [
                'nullable',
                'string',
                'max:255',
            ],
            'subject' => [
                'nullable',
                'string',
                'max:255',
            ],
            'academic_year' => [
                'nullable',
                'string',
                'max:255',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $search = trim($validated['q'] ?? '');
        $subjectSlug = $validated['subject'] ?? null;
        $academicYearSlug = $validated['academic_year'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 15);

        $topics = Topic::query()
            ->active()
            ->whereNull('parent_id')
            ->with([
                'subject:id,name,slug',
                'academicYear:id,name,slug',
            ])

            // Search topic title, description or subject name.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('bottom_line', 'like', "%{$search}%")
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where(
                                'name',
                                'like',
                                "%{$search}%"
                            );
                        });
                });
            })

            // Filter by subject slug.
            ->when($subjectSlug, function ($query) use ($subjectSlug) {
                $query->whereHas('subject', function ($subjectQuery) use (
                    $subjectSlug
                ) {
                    $subjectQuery
                        ->where('slug', $subjectSlug)
                        ->where('status', true);
                });
            })

            // Filter by academic-year slug.
            ->when(
                $academicYearSlug,
                function ($query) use ($academicYearSlug) {
                    $query->whereHas(
                        'academicYear',
                        function ($academicYearQuery) use (
                            $academicYearSlug
                        ) {
                            $academicYearQuery
                                ->where('slug', $academicYearSlug)
                                ->where('status', true);
                        }
                    );
                }
            )

            // Ensure inactive subjects are not returned.
            ->whereHas('subject', function ($subjectQuery) {
                $subjectQuery->where('status', true);
            })

            ->orderBy('order')
            ->orderBy('title')
            ->paginate($perPage)
            ->withQueryString();

        return SearchTopicResource::collection($topics);
    }
}
