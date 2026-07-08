<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Http\Request;
use App\Models\ExamAid;
use App\Models\AcademicYear;
use App\Models\Message;
class FrontendController extends Controller
{
    public function home()
    {
        $hero = \App\Models\HeroSection::active()->first() ?? new \App\Models\HeroSection();
        $sectionEnabled = \App\Models\Feature::query()->value('section_enabled');
        $sectionEnabled = is_null($sectionEnabled) ? true : (bool) $sectionEnabled;

        $visibleFeatures = $sectionEnabled
            ? \App\Models\Feature::active()->ordered()->get()
            : collect();
        $features = \App\Models\Feature::active()->ordered()->get();
        // Curriculum data: Active years with topics count
        $years = \App\Models\AcademicYear::active()
            ->withCount([
                'semesters as units_count',
                'topics as topics_count' => function ($q) {
                    $q->active()->whereNull('parent_id');
                },
            ])
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

        return view('welcome', compact(
            'hero', 'features','examTopicsCount','examSubjectsCount','subjects','sectionEnabled','visibleFeatures','testimonialSectionEnabled', 'years', 'trendingTopics', 'testimonials', 'faqs', 'banners', 'sliders','mostRequestedTopic'
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
            ->withCount([
                'semesters as units_count',
                'topics as topics_count' => function ($q) {
                    $q->active()->whereNull('parent_id');
                },
            ])
            ->orderBy('order')
            ->get();

        $currentYear = $yearSlug
            ? \App\Models\AcademicYear::where('slug', $yearSlug)->active()->firstOrFail()
            : $years->first();

        // Agar admin ne saare academic years delete kar diye hon
        if (! $currentYear) {
            $topics = collect();

            $curriculumSubjects = $this->getCurriculumSubjectsForYear($currentYear);

            return view('topics-year', compact(
                'years',
                'currentYear',
                'topics',
                'curriculumSubjects'
            ));
        }

        // Topics grouped by Subject for the selected year
        $topics = \App\Models\Topic::active()
            ->where('academic_year_id', $currentYear->id)
            ->whereNull('parent_id')
            ->with([
                'subject',
                'subtopics' => function ($q) {
                    $q->active();
                },
            ])
            ->orderBy('order')
            ->get()
            ->groupBy(function ($topic) {
                return $topic->subject->name ?? 'Academic Core';
            });

        $curriculumSubjects = $this->getCurriculumSubjectsForYear($currentYear);

        return view('topics-year', compact(
            'years',
            'currentYear',
            'topics',
            'curriculumSubjects'
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

        $pageProtected = $page->is_protected;

        $faqs = FAQ::active()->ordered()->get();

        /*
        |--------------------------------------------------------------------------
        | Exam Aid Dynamic Data
        |--------------------------------------------------------------------------
        | Ye data admin panel ke Exam Aid form se save hua data frontend par show karega.
        */

        $years = AcademicYear::orderBy('name')->get();

        $examAids = ExamAid::with([
            'subject',
            'unit',
            'academicYear',
            'semester',
            'materials',
        ])
            ->where('status', 1)

            // Academic Year filter
            ->when($request->filled('year') && $request->year !== 'all', function ($query) use ($request) {
                $query->where('academic_year_id', $request->year);
            })

            // Learning Material / Resource Type filter
            ->when($request->filled('resource_type') && $request->resource_type !== 'all', function ($query) use ($request) {
                if ($request->resource_type === 'viva') {
                    $query->whereNotNull('viva_question')
                        ->where('viva_question', '!=', '');
                } elseif ($request->resource_type === 'exam') {
                    $query->whereNotNull('exam_question')
                        ->where('exam_question', '!=', '');
                } else {
                    $query->whereHas('materials', function ($materialQuery) use ($request) {
                        $materialQuery->where('type', $request->resource_type);
                    });
                }
            })

            // Subject / Title / Unit search
            ->when($request->filled('q'), function ($query) use ($request) {
                $search = $request->q;

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

        return \App\Models\Subject::active()
            ->whereHas('units', function ($query) {
                $query->where('is_active', true);
            })
            ->with([
                // Subject ke andar sari active units load hongi
                'units' => function ($query) {
                    $query->where('is_active', true)
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },

                // Har unit ke andar active topic-module topics load hongay
                'units.unitTopics' => function ($query) {
                    $query->where('status', true)
                        ->orderBy('sort_order')
                        ->orderBy('title');
                },

                // LMS topics sirf current academic year ke according load hongay
                'units.unitTopics.lmsTopics' => function ($query) use ($currentYear) {
                    $query->where('status', true)
                        ->whereNull('parent_id')
                        ->where('academic_year_id', $currentYear->id)
                        ->with(['subtopics' => function ($subQuery) {
                            $subQuery->where('status', true)
                                ->orderBy('order');
                        }])
                        ->orderBy('order');
                },
            ])
            ->orderBy('name')
            ->get();
    }
}
