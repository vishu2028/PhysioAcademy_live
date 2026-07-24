<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamAidResource;
use App\Models\ExamAid;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use App\Http\Resources\ExamAidDetailResource;

class ExamAidController extends Controller
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

            'resource_type' => [
                'nullable',
                'string',
                Rule::in([
                    'all',
                    'viva',
                    'exam',
                    'guide',
                    'pdf',
                    'video',
                    'link',
                    'note',
                ]),
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
        $resourceType = $validated['resource_type'] ?? null;
        $perPage = (int) ($validated['per_page'] ?? 15);

        $examAids = ExamAid::query()
            ->where('status', true)

            // Inactive subject ke exam aids return nahi honge.
            ->whereHas('subject', function ($subjectQuery) {
                $subjectQuery->where('status', true);
            })

            ->with([
                'subject:id,name,slug',
                'unit:id,subject_id,name,slug',
                'academicYear:id,name,slug',
                'semester:id,academic_year_id,name,order',
                'materials:id,exam_aid_id,type',
            ])

            ->withCount('materials')

            // Search by title, description, questions,
            // subject name or unit name.
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('viva_question', 'like', "%{$search}%")
                        ->orWhere('exam_question', 'like', "%{$search}%")
                        ->orWhereHas(
                            'subject',
                            function ($subjectQuery) use ($search) {
                                $subjectQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        )
                        ->orWhereHas(
                            'unit',
                            function ($unitQuery) use ($search) {
                                $unitQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                });
            })

            // Filter by subject slug.
            ->when($subjectSlug, function ($query) use ($subjectSlug) {
                $query->whereHas(
                    'subject',
                    function ($subjectQuery) use ($subjectSlug) {
                        $subjectQuery
                            ->where('slug', $subjectSlug)
                            ->where('status', true);
                    }
                );
            })

            // Filter by academic-year slug.
            ->when(
                $academicYearSlug,
                function ($query) use ($academicYearSlug) {
                    $query->whereHas(
                        'academicYear',
                        function ($yearQuery) use ($academicYearSlug) {
                            $yearQuery
                                ->where('slug', $academicYearSlug)
                                ->where('status', true);
                        }
                    );
                }
            )

            // Filter by resource type.
            ->when(
                $resourceType && $resourceType !== 'all',
                function ($query) use ($resourceType) {
                    if ($resourceType === 'viva') {
                        $query->whereRaw(
                            "TRIM(COALESCE(viva_question, '')) <> ''"
                        );

                        return;
                    }

                    if ($resourceType === 'exam') {
                        $query->whereRaw(
                            "TRIM(COALESCE(exam_question, '')) <> ''"
                        );

                        return;
                    }

                    if ($resourceType === 'guide') {
                        $query->whereHas(
                            'materials',
                            function ($materialQuery) {
                                $materialQuery->whereIn(
                                    'type',
                                    ['video', 'link']
                                );
                            }
                        );

                        return;
                    }

                    $query->whereHas(
                        'materials',
                        function ($materialQuery) use ($resourceType) {
                            $materialQuery->where(
                                'type',
                                $resourceType
                            );
                        }
                    );
                }
            )

            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return ExamAidResource::collection($examAids);
    }
    public function show(int $id): ExamAidDetailResource
    {
        $examAid = ExamAid::query()
            ->where('status', true)

            // Inactive subject ka exam aid display nahi hoga.
            ->whereHas('subject', function ($subjectQuery) {
                $subjectQuery->where('status', true);
            })

            ->with([
                'subject:id,name,slug',
                'unit:id,subject_id,name,slug',
                'academicYear:id,name,slug',
                'semester:id,academic_year_id,name,order',

                'materials' => function ($materialQuery) {
                    $materialQuery
                        ->orderBy('order')
                        ->orderBy('id');
                },
            ])

            ->findOrFail($id);

        return new ExamAidDetailResource($examAid);
    }
}
