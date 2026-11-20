<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Message;
use App\Models\Page;
use Illuminate\Http\Request;
use App\Services\SeoMetadata;

class SupportPageController extends Controller
{
    private const PAGE_TITLES = [
        'faq' => 'FAQ',
        'feedback' => 'Feedback',
        'privacy-policy' => 'Privacy Policy',
        'terms-and-conditions' => 'Terms & Conditions',
    ];

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::PAGE_TITLES), 404);

        $pageRecord = $slug === 'faq'
            ? null
            : Page::query()->where('slug', $slug)->first();
        $page = $pageRecord?->status ? $pageRecord : null;
        $faqs = $slug === 'faq' ? FAQ::active()->ordered()->get() : collect();
        $pageTitle = $page?->title ?: self::PAGE_TITLES[$slug];
        $supportRoute = match ($slug) {
            'privacy-policy' => 'support.privacy-policy',
            'terms-and-conditions' => 'support.terms',
            'feedback' => 'support.feedback',
            default => 'faq',
        };
        $seo = $page
            ? app(SeoMetadata::class)->for(
                $page,
                $pageTitle,
                rtrim(config('app.url'), '/') . route($supportRoute, [], false),
                $page->is_protected
            )
            : app(SeoMetadata::class)->forPage(
                $pageTitle,
                $faqs->first()?->answer,
                rtrim(config('app.url'), '/') . route($supportRoute, [], false)
            );

        return view('support.page', [
            'page' => $page,
            'pageTitle' => $pageTitle,
            'faqs' => $faqs,
            'slug' => $slug,
            'pageProtected' => $page?->is_protected ?? false,
            'seo' => $seo,
        ]);
    }

    public function feedbackSubmit(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
        ]);

        Message::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => 'Website Feedback',
            'message' => $validated['message'],
        ]);

        return back()->with('success', 'Thank you for sharing your feedback.');
    }
}
