<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FAQ;
use Illuminate\Http\JsonResponse;

class FAQController extends Controller
{
    public function index(): JsonResponse
    {
        $faqs = FAQ::query()
            ->active()
            ->ordered()
            ->get([
                'id',
                'question',
                'answer',
                'category',
                'order',
            ]);

        return response()->json([
            'data' => $faqs
                ->map(function (FAQ $faq) {
                    return [
                        'id' => $faq->id,
                        'question' => brand_text($faq->question),
                        'answer' => brand_text($faq->answer),
                        'category' => brand_text($faq->category),
                        'order' => $faq->order,
                    ];
                })
                ->values(),

            'meta' => [
                'total' => $faqs->count(),
            ],
        ]);
    }
}
