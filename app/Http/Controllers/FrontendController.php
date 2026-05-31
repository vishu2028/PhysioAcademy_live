<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\FAQ;
use Illuminate\Http\Request;

class FrontendController extends Controller
{
    public function home()
    {
        $hero = \App\Models\HeroSection::active()->first() ?? new \App\Models\HeroSection();
        $features = \App\Models\Feature::active()->ordered()->get();
        // Curriculum data: Active years with topics count
        $years = \App\Models\AcademicYear::active()->withCount(['topics' => function($q) {
            $q->active();
        }])->orderBy('order')->get();
        
        // Trending Topics
        $trendingTopics = \App\Models\Topic::active()->with('subject')->orderBy('order')->limit(4)->get();
        
        $testimonials = \App\Models\Testimonial::active()->ordered()->get();
        $faqs = \App\Models\FAQ::active()->ordered()->limit(6)->get();
        $banners = \App\Models\Banner::active()->ordered()->get();
        $sliders = \App\Models\Slider::active()->ordered()->get();

        return view('welcome', compact(
            'hero', 'features', 'years', 'trendingTopics', 'testimonials', 'faqs', 'banners', 'sliders'
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
        $years = \App\Models\AcademicYear::active()->orderBy('order')->get();
        
        $currentYear = $yearSlug 
            ? \App\Models\AcademicYear::where('slug', $yearSlug)->active()->firstOrFail() 
            : \App\Models\AcademicYear::active()->orderBy('order')->first();

        // Topics grouped by Subject for the selected year (top-level only)
        $topics = \App\Models\Topic::active()
            ->where('academic_year_id', $currentYear->id)
            ->whereNull('parent_id')
            ->with(['subject', 'subtopics' => function($q) {
                $q->active();
            }])
            ->orderBy('order')
            ->get()
            ->groupBy(function($topic) {
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
        $results = [];

        if ($query) {
            $results = \App\Models\Topic::active()
                ->where('title', 'like', "%{$query}%")
                ->orWhere('description', 'like', "%{$query}%")
                ->with('subject')
                ->get();
        }

        return view('search', compact('results', 'query'));
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
}
