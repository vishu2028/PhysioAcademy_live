<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Http\Request;
use App\Models\ExamAid;
use App\Models\AcademicYear;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
class FrontendController extends Controller
{
    public function home()
    {
//        $hero = \App\Models\HeroSection::active()->first() ?? new \App\Models\HeroSection();
        $hero = \App\Models\HeroSection::query()
            ->where('status', true)
            ->latest('id')
            ->first()
            ?? new \App\Models\HeroSection();
        $sectionEnabled = \App\Models\Feature::query()->value('section_enabled');
        $sectionEnabled = is_null($sectionEnabled) ? true : (bool) $sectionEnabled;

        $visibleFeatures = $sectionEnabled
            ? \App\Models\Feature::active()->ordered()->get()
            : collect();
        $features = \App\Models\Feature::active()->ordered()->get();
        // Curriculum data: Active years with topics count
        $years = \App\Models\AcademicYear::active()
            ->withCurriculumCounts()
            ->orderBy('order')
            ->get();
        // Doubt form subjects
        $subjects = \App\Models\Subject::query()
            ->orderBy('name')
            ->get();


        // Trending Topics
        $trendingTopics = \App\Models\Topic::active()->with('subject')->orderBy('order')->limit(4)->get();

        $testimonialSectionEnabled = \App\Models\Testimonial::query()->value('section_enabled');
        $testimonialSectionEnabled = is_null($testimonialSectionEnabled) ? true : (bool) $testimonialSectionEnabled;

        $testimonials = $testimonialSectionEnabled
            ? \App\Models\Testimonial::active()->ordered()->get()
            : collect();
        $faqs = \App\Models\FAQ::active()->ordered()->limit(6)->get();
        $banners = \App\Models\Banner::active()->ordered()->get();
        $sliders = \App\Models\Slider::active()->ordered()->get();
        $mostRequestedTopic = Message::select('subject')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->groupBy('subject')
            ->orderByDesc('total')
            ->first();
        $examTopicsCount = \App\Models\Topic::active()->count();
        $examSubjectsCount = \App\Models\Subject::count();
        $aboutTopicsCount = \App\Models\Topic::curriculumVisible()->count();

        $vivaQuestionsCount = \App\Models\ExamAid::query()
            ->where('status', true)
            ->whereRaw("TRIM(COALESCE(viva_question, '')) <> ''")
            ->count();

        $examQuestionsCount = \App\Models\ExamAid::query()
            ->where('status', true)
            ->whereRaw("TRIM(COALESCE(exam_question, '')) <> ''")
            ->count();

        $aboutQuestionsCount = $vivaQuestionsCount + $examQuestionsCount;

        $aboutStudentsCount = \App\Models\User::role('user')->count();

        return view('welcome', compact(
            'hero',
            'features',
            'examTopicsCount',
            'examSubjectsCount',
            'subjects',
            'sectionEnabled',
            'visibleFeatures',
            'testimonialSectionEnabled',
            'years',
            'trendingTopics',
            'testimonials',
            'faqs',
            'banners',
            'sliders',
            'mostRequestedTopic',
            'aboutTopicsCount',
            'aboutQuestionsCount',
            'aboutStudentsCount'
        ));
    }

    public function about()
    {
        $page = Page::where('slug', 'about-us')->active()->firstOrFail();
        $pageProtected = $page->is_protected;

        // Eager-load sections with items, keyed by slug for easy lookup in the view
        $sections = \App\Models\PageSection::with('items')
            ->where('page_id', $page->id)
            ->where('enabled', true)
            ->orderBy('order')
            ->get()
            ->keyBy('slug');

        return view('about', compact('page', 'pageProtected', 'sections'));
    }

//    public function topics()
//    {
//        $subjects = \App\Models\Subject::active()->withCount(['topics' => function($q) {
//            $q->active()->whereNull('parent_id');
//        }])->orderBy('order')->get();
//
//        return view('topics', compact('subjects'));
//    }
    public function topics()
    {
        $subjects = \App\Models\Subject::active()
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                'units' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },

                'units.unitTopics' => function ($query) {
                    $query->where('status', true)
                        ->orderBy('sort_order')
                        ->orderBy('title');
                },

                'units.unitTopics.lmsTopics' => function ($query) {
                    $query->where('status', true)
                        ->whereNull('parent_id')
                        ->with([
                            'academicYear',
                            'subtopics' => function ($subQuery) {
                                $subQuery->where('status', true)
                                    ->orderBy('order');
                            },
                        ])
                        ->orderBy('order');
                },
            ])
            ->orderBy('order')
            ->get();

        $requestedTags = \App\Models\Message::select('subject')
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('subject')
            ->where('subject', '!=', '')
            ->groupBy('subject')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('subject');

        return view('topics', compact('subjects', 'requestedTags'));
    }

    public function topicsByYear($yearSlug = null)
    {
        $years = \App\Models\AcademicYear::active()
            ->withCurriculumCounts()
            ->orderBy('order')
            ->get();

        $currentYear = $yearSlug
            ? \App\Models\AcademicYear::where('slug', $yearSlug)
                ->active()
                ->firstOrFail()
            : $years->first();

        if (! $currentYear) {
            return view('topics-year', [
                'years' => $years,
                'currentYear' => null,
                'topics' => collect(),
                'curriculumSubjects' => collect(),
                'subjectCount' => 0,
                'topicCount' => 0,
            ]);
        }

        /*
         * Use the same filtered query for both the displayed data and stats.
         * This prevents a newly-created year from showing subjects/topics that
         * belong to another academic year.
         */
        $yearTopicsQuery = \App\Models\Topic::curriculumVisible()
            ->where('academic_year_id', $currentYear->id);

        $subjectCount = (clone $yearTopicsQuery)
            ->distinct()
            ->count('subject_id');

        $topicCount = (clone $yearTopicsQuery)->count();

        $topics = $yearTopicsQuery
            ->with([
                'subject',
                'subtopics' => function ($query) {
                    $query->active();
                },
            ])
            ->orderBy('order')
            ->get()
            ->groupBy(function ($topic) {
                return $topic->subject->name ?? 'Academic Core';
            });

        $curriculumSubjects = $this->getCurriculumSubjectsForYear(
            $currentYear
        );

        return view('topics-year', compact(
            'years',
            'currentYear',
            'topics',
            'curriculumSubjects',
            'subjectCount',
            'topicCount'
        ));
    }

    public function topicShow($slug)
    {
        $topic = \App\Models\Topic::with(['materials', 'subject', 'academicYear', 'semester', 'subtopics'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        $relatedTopics = \App\Models\Topic::active()
            ->where('subject_id', $topic->subject_id)
            ->where('id', '!=', $topic->id)
            ->whereNull('parent_id')
            ->limit(4)
            ->get();

        $pageProtected = $topic->is_protected;
        return view('topic', compact('topic', 'pageProtected', 'relatedTopics'));
    }

    public function examAid(Request $request)
    {
        $page = \App\Models\Page::where('slug', 'exam-aid')
            ->active()
            ->firstOrFail();

        $sections = \App\Models\PageSection::with([
            'items' => function ($query) {
                $query->orderBy('order');
            }
        ])
            ->where('page_id', $page->id)
            ->where('enabled', true)
            ->orderBy('order')
            ->get();

        $defaults = $this->examAidDefaultSections();

        $sections = $sections
            ->filter(function ($section) use ($defaults) {
                return isset($defaults[$section->type]);
            })
            ->map(function ($section) use ($defaults) {
                $content = is_array($section->content)
                    ? $section->content
                    : (json_decode($section->content, true) ?: []);

                $section->content = array_replace_recursive(
                    $defaults[$section->type],
                    $content
                );

                return $section;
            })
            ->values();

        if ($sections->isEmpty()) {
            $sections = collect([
                (object) [
                    'type' => 'exam_hero',
                    'content' => $defaults['exam_hero'],
                    'items' => collect(),
                ],
                (object) [
                    'type' => 'exam_filters',
                    'content' => $defaults['exam_filters'],
                    'items' => collect(),
                ],
                (object) [
                    'type' => 'exam_resources',
                    'content' => $defaults['exam_resources'],
                    'items' => collect(),
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic Hero Stats
        |--------------------------------------------------------------------------
        | Ye circled hero cards, readiness score, progress bars aur mini stats ko
        | database se dynamic karega.
        */
        $examHeroStats = $this->examAidHeroStats();

        $sections = $sections->map(function ($section) use ($examHeroStats) {
            if ($section->type === 'exam_hero') {
                $section->content = array_replace_recursive(
                    is_array($section->content) ? $section->content : [],
                    $examHeroStats
                );
            }

            return $section;
        });

        $pageProtected = $page->is_protected;

        $faqs = FAQ::active()->ordered()->get();

        $years = AcademicYear::orderBy('name')->get();

        $examAids = ExamAid::with([
            'subject',
            'unit',
            'academicYear',
            'semester',
            'materials' => function ($query) {
                $query->orderBy('order');
            },
        ])
            ->where('status', 1)

            // Academic Year filter
            ->when($request->filled('year') && $request->year !== 'all', function ($query) use ($request) {
                $query->where('academic_year_id', $request->year);
            })

            // Learning Material / Resource Type filter
            ->when($request->filled('resource_type') && $request->resource_type !== 'all', function ($query) use ($request) {
                if ($request->resource_type === 'viva') {
                    $query->whereRaw("TRIM(COALESCE(viva_question, '')) <> ''");
                } elseif ($request->resource_type === 'exam') {
                    $query->whereRaw("TRIM(COALESCE(exam_question, '')) <> ''");
                } elseif ($request->resource_type === 'guide') {
                    $query->whereHas('materials', function ($materialQuery) {
                        $materialQuery->whereIn('type', ['video', 'link']);
                    });
                } else {
                    $query->whereHas('materials', function ($materialQuery) use ($request) {
                        $materialQuery->where('type', $request->resource_type);
                    });
                }
            })

            // Subject / Title / Unit search
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = trim($request->q);

                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('subject', function ($subjectQuery) use ($search) {
                            $subjectQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('unit', function ($unitQuery) use ($search) {
                            $unitQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('exam-aid', compact(
            'page',
            'sections',
            'pageProtected',
            'faqs',
            'years',
            'examAids'
        ));
    }
    private function examAidHeroStats(): array
    {
        $totalActiveAids = ExamAid::where('status', 1)->count();

        $materialBaseQuery = DB::table('exam_aid_materials')
            ->join('exam_aids', 'exam_aids.id', '=', 'exam_aid_materials.exam_aid_id')
            ->where('exam_aids.status', 1);

        $pastPapersCount = (clone $materialBaseQuery)
            ->where('exam_aid_materials.type', 'pdf')
            ->count();

        $subjectNotesCount = (clone $materialBaseQuery)
            ->where('exam_aid_materials.type', 'note')
            ->count();

        $videoGuideCount = (clone $materialBaseQuery)
            ->whereIn('exam_aid_materials.type', ['video', 'link'])
            ->count();

        $vivaQuestionsCount = ExamAid::where('status', 1)
            ->whereRaw("TRIM(COALESCE(viva_question, '')) <> ''")
            ->count();

        $examQuestionsCount = ExamAid::where('status', 1)
            ->whereRaw("TRIM(COALESCE(exam_question, '')) <> ''")
            ->count();

        $examGuidesCount = $videoGuideCount;

        $paperCoverage = (clone $materialBaseQuery)
            ->where('exam_aid_materials.type', 'pdf')
            ->distinct()
            ->count('exam_aid_materials.exam_aid_id');

        $vivaCoverage = $vivaQuestionsCount;

        $guideCoverage = ExamAid::where('status', 1)
            ->whereHas('materials', function ($materialQuery) {
                $materialQuery->whereIn('type', ['video', 'link']);
            })
            ->count();

        $coverageBase = max($totalActiveAids, 1);

        $paperWidth = min(100, round(($paperCoverage / $coverageBase) * 100));
        $vivaWidth = min(100, round(($vivaCoverage / $coverageBase) * 100));
        $guideWidth = min(100, round(($guideCoverage / $coverageBase) * 100));

        $readinessScore = $totalActiveAids > 0
            ? round(($paperWidth + $vivaWidth + $guideWidth) / 3)
            : 0;

        return [
            'readiness_score' => $readinessScore,

            'quick_links' => [
                [
                    'icon_num' => '01',
                    'label' => 'Past Papers',
                    'count' => $pastPapersCount,
                    'url' => route('exam-aid', ['resource_type' => 'pdf']) . '#exam-resources',
                ],
                [
                    'icon_num' => '02',
                    'label' => 'Viva Questions',
                    'count' => $vivaQuestionsCount,
                    'url' => route('exam-aid', ['resource_type' => 'viva']) . '#exam-resources',
                ],
                [
                    'icon_num' => '03',
                    'label' => 'Subject Notes',
                    'count' => $subjectNotesCount,
                    'url' => route('exam-aid', ['resource_type' => 'note']) . '#exam-resources',
                ],
                [
                    'icon_num' => '04',
                    'label' => 'Exam Guides',
                    'count' => $examGuidesCount,
                    'url' => route('exam-aid', ['resource_type' => 'guide']) . '#exam-resources',
                ],
            ],

            'progress_items' => [
                [
                    'label' => 'Past Papers',
                    'width' => $paperWidth . '%',
                ],
                [
                    'label' => 'Viva',
                    'width' => $vivaWidth . '%',
                ],
                [
                    'label' => 'Guides',
                    'width' => $guideWidth . '%',
                ],
            ],

            'stats' => [
                'papers' => $pastPapersCount,
                'questions' => $vivaQuestionsCount + $examQuestionsCount,
                'guides' => $examGuidesCount,
            ],
        ];
    }
    private function examAidDefaultSections(): array
    {
        return [
            'exam_hero' => [
                'kicker' => 'Exam Aid Studio',
                'title' => 'Study smarter with exam-ready resources',
                'description' => 'Find past papers, viva questions, subject notes and exam guides in one place.',
                'primary_cta_text' => 'Explore Resources',
                'primary_cta_url' => '#exam-resources',
                'secondary_cta_text' => 'Search Subjects',
                'secondary_cta_url' => '#college-selector',
                'readiness_score' => 92,
                'quick_links' => [
                    [
                        'icon_num' => '01',
                        'label' => 'Past Papers',
                        'url' => '#exam-resources',
                    ],
                    [
                        'icon_num' => '02',
                        'label' => 'Viva Questions',
                        'url' => '#exam-resources',
                    ],
                    [
                        'icon_num' => '03',
                        'label' => 'Subject Notes',
                        'url' => '#exam-resources',
                    ],
                    [
                        'icon_num' => '04',
                        'label' => 'Exam Guides',
                        'url' => '#exam-resources',
                    ],
                ],
                'progress_items' => [
                    [
                        'label' => 'Past Papers',
                        'width' => '85%',
                    ],
                    [
                        'label' => 'Viva Sets',
                        'width' => '70%',
                    ],
                    [
                        'label' => 'Guides',
                        'width' => '92%',
                    ],
                ],
                'stats' => [
                    'papers' => 120,
                    'questions' => 850,
                    'guides' => 40,
                ],
                'floating_cards' => [
                    'Updated exam resources',
                    'Topper verified notes',
                ],
            ],

            'exam_filters' => [
                'eyebrow' => 'Smart Filters',
                'title' => 'Find the right study material',
                'description' => 'Filter resources by university, year and resource type.',
            ],

            'exam_resources' => [
                'eyebrow' => 'Resource Library',
                'title' => 'Exam resources built for students',
                'description' => 'Explore FAQs, topics, past papers and viva preparation material.',
            ],
        ];
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $results = collect();

        if ($query) {
            $results = \App\Models\Topic::active()
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->with(['subject', 'academicYear'])
                ->orderBy('order')
                ->get();
        } else {
            // Show trending topics when no query is given
            $results = \App\Models\Topic::active()
                ->with(['subject', 'academicYear'])
                ->orderBy('order')
                ->limit(20)
                ->get();
        }

        return view('search', compact('results', 'query'));
    }

    public function searchSuggestions(Request $request)
    {
        $query = trim($request->input('query', ''));

        if (!$query) {
            // Return trending/popular topics if query is empty
            $trending = \App\Models\Topic::active()
                ->with('subject:id,name')
                ->orderBy('order')
                ->limit(8)
                ->get(['id', 'title', 'slug', 'subject_id']);

            return response()->json([
                'status' => 'success',
                'type'   => 'trending',
                'data'   => $trending
            ]);
        }

        // Wrap OR conditions in a grouped closure so active() scope is not broken
        $results = \App\Models\Topic::active()
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhereHas('subject', function($sq) use ($query) {
                        $sq->where('name', 'like', "%{$query}%");
                    });
            })
            ->with('subject:id,name')
            ->orderBy('order')
            ->limit(10)
            ->get(['id', 'title', 'slug', 'subject_id']);

        return response()->json([
            'status' => 'success',
            'type'   => 'search',
            'data'   => $results
        ]);
    }

    public function bookmarks()
    {
        $page = Page::where('slug', 'bookmarks')->active()->firstOrFail();

        $sections = \App\Models\PageSection::with('items')
            ->where('page_id', $page->id)
            ->where('enabled', true)
            ->orderBy('order')
            ->get()
            ->keyBy('slug');

        $bookmarks = [];
        if (auth()->check()) {
            $bookmarks = auth()->user()->bookmarks()
                ->with('bookmarkable')
                ->latest()
                ->get();
        }

        return view('bookmarks', compact('page', 'sections', 'bookmarks'));
    }

    public function faq()
    {
        $faqs = FAQ::active()->ordered()->get();
        return view('faq', compact('faqs'));
    }

    public function contact()
    {
        $page = Page::where('slug', 'contact-us')->active()->firstOrFail();
        $sections = \App\Models\PageSection::with('items')
            ->where('page_id', $page->id)
            ->where('enabled', true)
            ->orderBy('order')
            ->get()
            ->keyBy('slug');
        $pageProtected = false;

        return view('contact', compact('page', 'sections', 'pageProtected'));
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        \App\Models\Message::create($request->all());

        return back()->with('success', 'Your message has been sent successfully!');
    }

    public function dynamicPage($slug)
    {
        $page = \App\Models\Page::where('slug', $slug)->active()->firstOrFail();
        $pageProtected = $page->is_protected;
        return view('page', compact('page', 'pageProtected'));
    }

    public function downloadMaterial($id)
    {
        $material = \App\Models\LearningMaterial::findOrFail($id);

        if ($material->type !== 'pdf' || !$material->file_path) {
            return back()->with('error', 'This material is not a downloadable file.');
        }

        $path = storage_path('app/public/' . $material->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'File not found.');
        }

        return response()->download($path);
    }
    private function getCurriculumSubjectsForYear($currentYear)
    {
        if (! $currentYear) {
            return collect();
        }

        $yearId = $currentYear->id;

        $filterTopicsForYear = function ($query) use ($yearId) {
            $query->where('status', true)
                ->whereNull('parent_id')
                ->where('academic_year_id', $yearId);
        };

        return \App\Models\Subject::active()
            ->whereHas(
                'units.unitTopics.lmsTopics',
                $filterTopicsForYear
            )
            ->with([
                'units' => function ($query) use (
                    $filterTopicsForYear
                ) {
                    $query->where('is_active', true)
                        ->whereHas(
                            'unitTopics.lmsTopics',
                            $filterTopicsForYear
                        )
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
                'units.unitTopics' => function ($query) use (
                    $filterTopicsForYear
                ) {
                    $query->where('status', true)
                        ->whereHas(
                            'lmsTopics',
                            $filterTopicsForYear
                        )
                        ->orderBy('sort_order')
                        ->orderBy('title');
                },
                'units.unitTopics.lmsTopics' => function ($query) use (
                    $yearId
                ) {
                    $query->where('status', true)
                        ->whereNull('parent_id')
                        ->where('academic_year_id', $yearId)
                        ->with([
                            'subtopics' => function ($subQuery) {
                                $subQuery->where('status', true)
                                    ->orderBy('order');
                            },
                        ])
                        ->orderBy('order');
                },
            ])
            ->orderBy('name')
            ->get();
    }
}
