<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function show(
        Request $request,
        string $slug
    ): JsonResponse {
        $page = Page::query()
            ->active()
            ->where('slug', $slug)
            ->with([
                'sections' => function ($sectionQuery) {
                    $sectionQuery
                        ->where('enabled', true)
                        ->orderBy('order')
                        ->with([
                            'items' => function ($itemQuery) {
                                $itemQuery
                                    ->where('enabled', true)
                                    ->orderBy('order');
                            },
                        ]);
                },
            ])
            ->firstOrFail();

        /*
         * Protected page ko login ke baghair
         * access nahi kiya ja sakta.
         */
        if (
            $page->is_protected
            && ! $request->user('sanctum')
        ) {
            return response()->json([
                'message' => 'Please login to access this page.',
            ], 401);
        }

        return response()->json([
            'data' => [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'is_protected' => (bool) $page->is_protected,

                'meta' => [
                    'title' => $page->meta_title,
                    'description' => $page->meta_description,
                ],

                'sections' => $page->sections
                    ->map(function ($section) {
                        return [
                            'id' => $section->id,
                            'name' => $section->name,
                            'slug' => $section->slug,
                            'type' => $section->type,
                            'content' => $section->content,
                            'order' => $section->order,

                            'items' => $section->items
                                ->map(function ($item) {
                                    return [
                                        'id' => $item->id,
                                        'title' => $item->title,
                                        'body' => $item->body,
                                        'meta' => $item->meta,
                                        'order' => $item->order,
                                    ];
                                })
                                ->values(),
                        ];
                    })
                    ->values(),
            ],
        ]);
    }
}
