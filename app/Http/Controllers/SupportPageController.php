<?php

namespace App\Http\Controllers;

use App\Models\FAQ;
use App\Models\Message;
use App\Models\Page;
use Illuminate\Http\Request;

class SupportPageController extends Controller
{
    private const PAGE_TITLES = [
        'faq' => 'FAQ',
        'feedback' => 'Feedback',
        'help-center' => 'Help Center',
        'privacy-policy' => 'Privacy Policy',
        'terms-and-conditions' => 'Terms & Conditions',
    ];

    public function show(string $slug)
    {
        abort_unless(array_key_exists($slug, self::PAGE_TITLES), 404);

        $pageRecord = Page::query()
            ->where('slug', $slug)
            ->first();
        $page = $pageRecord?->status ? $pageRecord : null;

        $faqs = $slug === 'faq' && ! $pageRecord
            ? FAQ::active()->ordered()->get()
            : collect();

        return view('support.page', [
            'page' => $page,
            'pageTitle' => $page?->title ?: self::PAGE_TITLES[$slug],
            'faqs' => $faqs,
            'slug' => $slug,
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
