<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class NewsletterSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:254'],
            'status' => [
                'nullable',
                Rule::in([
                    NewsletterSubscriber::STATUS_SUBSCRIBED,
                    NewsletterSubscriber::STATUS_UNSUBSCRIBED,
                ]),
            ],
        ]);

        $subscribers = NewsletterSubscriber::query()
            ->when(
                filled($validated['search'] ?? null),
                fn ($query) => $query->where('email', 'like', '%'.trim($validated['search']).'%')
            )
            ->when(
                $validated['status'] ?? null,
                fn ($query, $status) => $query->where('status', $status)
            )
            ->orderByDesc('subscribed_at')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.newsletter-subscribers.index', compact('subscribers'));
    }

    public function show(Request $request, NewsletterSubscriber $newsletterSubscriber)
    {
        return view('admin.newsletter-subscribers.show', [
            'subscriber' => $newsletterSubscriber,
            'listingQuery' => $request->query(),
        ]);
    }

    public function destroy(Request $request, NewsletterSubscriber $newsletterSubscriber)
    {
        $newsletterSubscriber->delete();

        return redirect()
            ->route('admin.newsletter-subscribers.index', $request->query())
            ->with('success', 'Newsletter subscriber deleted successfully.');
    }
}
