<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SearchTopicResource;
use App\Models\Topic;
use App\Services\SiteSearch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function index(Request $request, SiteSearch $siteSearch): JsonResponse|AnonymousResourceCollection
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $search = trim($validated['q'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 15);

        if ($search === '') {
            $topics = Topic::query()
                ->active()
                ->whereNull('parent_id')
                ->with([
                    'subject:id,name,slug',
                    'academicYear:id,name,slug',
                ])
                ->whereHas('subject', fn ($subject) => $subject->where('status', true))
                ->orderBy('order')
                ->orderBy('title')
                ->paginate($perPage)
                ->withQueryString();

            return SearchTopicResource::collection($topics);
        }

        $results = $siteSearch->search($search)
            ->filter(fn (array $result): bool =>
                (! isset($validated['subject'])
                    || data_get($result, 'subject.slug') === $validated['subject'])
                && (! isset($validated['academic_year'])
                    || data_get($result, 'academic_year.slug') === $validated['academic_year'])
            )
            ->values();

        $page = max(1, $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            $results->forPage($page, $perPage)->values(),
            $results->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return response()->json($paginator);
    }

    public function suggestions(Request $request, SiteSearch $siteSearch): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'query' => ['nullable', 'string', 'max:100'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $search = trim($validated['q'] ?? $validated['query'] ?? '');
        $limit = (int) ($validated['limit'] ?? 8);
        $results = $search !== ''
            ? $siteSearch->search($search, $limit)->take($limit)
            : $siteSearch->trending($limit);

        return response()->json([
            'type' => $search !== '' ? 'search' : 'trending',
            'query' => $search,
            'count' => $results->count(),
            'data' => $results->values(),
        ]);
    }
}
