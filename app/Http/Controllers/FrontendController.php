<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Http\Request;
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

        return view('welcome', compact(
            'hero', 'features','subjects','sectionEnabled','visibleFeatures','testimonialSectionEnabled', 'years', 'trendingTopics', 'testimonials', 'faqs', 'banners', 'sliders','mostRequestedTopic'
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

    public function topics()
    {
        $subjects = \App\Models\Subject::active()->withCount(['topics' => function($q) {
            $q->active()->whereNull('parent_id');
        }])->orderBy('order')->get();
        return view('topics', compact('subjects'));
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

            return view('topics-year', compact('years', 'currentYear', 'topics'));
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

        return view('topics-year', compact('years', 'currentYear', 'topics'));
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

    public function examAid()
    {
        $page = \App\Models\Page::where('slug', 'exam-aid')->active()->firstOrFail();
        $sections = \App\Models\PageSection::with('items')->where('page_id', $page->id)->where('enabled', true)->orderBy('order')->get();
        $pageProtected = $page->is_protected;

        // We still fetch FAQs for the resource library if the dynamic logic requires it
        $faqs = FAQ::active()->ordered()->get();

        return view('exam-aid', compact('page', 'sections', 'pageProtected', 'faqs'));
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
}
