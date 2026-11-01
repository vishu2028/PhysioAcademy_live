<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewsletterSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'email' => trim((string) $request->input('email', '')),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:254'],
        ]);

        $email = Str::lower(trim($validated['email']));
        $subscribedAt = now();

        [$subscriber, $didSubscribe] = DB::transaction(function () use ($email, $subscribedAt): array {
            $subscriber = NewsletterSubscriber::query()
                ->where('email', $email)
                ->lockForUpdate()
                ->first();

            if (! $subscriber) {
                $subscriber = NewsletterSubscriber::firstOrCreate(
                    ['email' => $email],
                    [
                        'subscribed_at' => $subscribedAt,
                        'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
                    ]
                );

                if ($subscriber->wasRecentlyCreated) {
                    return [$subscriber, true];
                }
            }

            if ($subscriber->isSubscribed()) {
                return [$subscriber, false];
            }

            $subscriber->update([
                'subscribed_at' => $subscribedAt,
                'status' => NewsletterSubscriber::STATUS_SUBSCRIBED,
            ]);

            return [$subscriber, true];
        });

        return response()->json([
            'message' => $didSubscribe
                ? "Thank you for subscribing! You'll receive updates from PhysioSource."
                : 'You are already subscribed!',
            'subscribed' => $subscriber->isSubscribed(),
            'already_subscribed' => ! $didSubscribe,
        ]);
    }
}
