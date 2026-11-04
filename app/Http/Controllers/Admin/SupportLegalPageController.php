<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class SupportLegalPageController extends Controller
{
    private const PAGE_DEFINITIONS = [
        'faq' => 'FAQ',
        'feedback' => 'Feedback',
        'help-center' => 'Help Center',
        'privacy-policy' => 'Privacy Policy',
        'terms-and-conditions' => 'Terms & Conditions',
    ];

    public function index()
    {
        $this->ensurePagesExist();

        $pages = Page::query()
            ->whereIn('slug', array_keys(self::PAGE_DEFINITIONS))
            ->orderByRaw("CASE slug WHEN 'faq' THEN 1 WHEN 'feedback' THEN 2 WHEN 'help-center' THEN 3 WHEN 'privacy-policy' THEN 4 WHEN 'terms-and-conditions' THEN 5 END")
            ->get();

        return view('admin.support-legal-pages.index', compact('pages'));
    }

    public function edit(string $slug)
    {
        abort_unless(array_key_exists($slug, self::PAGE_DEFINITIONS), 404);

        $page = $this->ensurePage($slug);

        return view('admin.support-legal-pages.edit', compact('page'));
    }

    public function update(Request $request, string $slug)
    {
        abort_unless(array_key_exists($slug, self::PAGE_DEFINITIONS), 404);

        $page = $this->ensurePage($slug);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'status' => ['nullable', 'boolean'],
        ]);

        $page->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? '',
            'status' => $request->boolean('status'),
        ]);

        return redirect()
            ->route('admin.support-legal-pages.index')
            ->with('success', "{$page->title} updated successfully.");
    }

    private function ensurePagesExist(): void
    {
        foreach (array_keys(self::PAGE_DEFINITIONS) as $slug) {
            $this->ensurePage($slug);
        }
    }

    private function ensurePage(string $slug): Page
    {
        return Page::firstOrCreate(
            ['slug' => $slug],
            [
                'title' => self::PAGE_DEFINITIONS[$slug],
                'content' => '',
                'status' => false,
            ]
        );
    }
}
