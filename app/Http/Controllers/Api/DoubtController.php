<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DoubtResource;
use App\Models\Doubt;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

class DoubtController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => [
                'nullable',
                'string',
                Rule::in([
                    Doubt::STATUS_PENDING,
                    Doubt::STATUS_IN_PROGRESS,
                    Doubt::STATUS_ANSWERED,
                    Doubt::STATUS_REJECTED,
                ]),
            ],

            'q' => [
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

        $status = $validated['status'] ?? null;
        $search = trim($validated['q'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 15);

        $doubts = Doubt::query()

            /*
             * Sirf current logged-in user ke doubts.
             */
            ->where('user_id', $request->user()->id)

            ->with([
                'academicYear:id,name,slug',
                'subject:id,name,slug',
                'answeredBy:id,name',
            ])

            /*
             * Optional status filter.
             */
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })

            /*
             * Topic, message, answer aur subject name mein search.
             */
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('topic', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('answer', 'like', "%{$search}%")
                        ->orWhereHas(
                            'subject',
                            function ($subjectQuery) use ($search) {
                                $subjectQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                });
            })

            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return DoubtResource::collection($doubts);
    }
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'academic_year_id' => [
                'required',
                'integer',
                Rule::exists('academic_years', 'id')
                    ->where('status', true),
            ],

            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')
                    ->where('status', true),
            ],

            'topic' => [
                'required',
                'string',
                'max:255',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],
        ]);

        $doubt = Doubt::query()->create([
            'user_id' => $request->user()->id,
            'academic_year_id' => $validated['academic_year_id'],
            'subject_id' => $validated['subject_id'],
            'topic' => trim($validated['topic']),
            'message' => trim($validated['message']),
            'status' => Doubt::STATUS_PENDING,
            'answer' => null,
            'answered_by' => null,
            'answered_at' => null,
        ]);

        $doubt->load([
            'academicYear:id,name,slug',
            'subject:id,name,slug',
            'answeredBy:id,name',
        ]);

        return (new DoubtResource($doubt))
            ->additional([
                'message' => 'Doubt submitted successfully.',
            ])
            ->response()
            ->setStatusCode(201);
    }
}
