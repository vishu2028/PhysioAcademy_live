<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\PreservesListingContext;
use App\Http\Controllers\Controller;
use App\Models\TopicRequest;
use App\Notifications\TopicRequestStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TopicRequestController extends Controller
{
    use PreservesListingContext;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $search = trim($validated['search'] ?? '');
        $term = '%'.addcslashes($search, '%_\\').'%';

        $topicRequests = TopicRequest::with([
            'user',
            'subject',
            'academicYear',
            'semester',
            'topic',
        ])
            ->when($search !== '', function ($query) use ($term): void {
                $query->where(function ($requests) use ($term): void {
                    $requests
                        ->where('topic_name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('user', function ($users) use ($term): void {
                            $users
                                ->where('name', 'like', $term)
                                ->orWhere('email', 'like', $term);
                        })
                        ->orWhereHas('subject', fn ($subjects) => $subjects->where('name', 'like', $term));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.topic-requests.index', compact('topicRequests'));
    }

    public function update(Request $request, TopicRequest $topicRequest)
    {
        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    TopicRequest::STATUS_PENDING,
                    TopicRequest::STATUS_IN_PROGRESS,
                    TopicRequest::STATUS_RESOLVED,
                ]),
            ],
            'topic_link' => ['nullable', 'url:http,https', 'max:2048'],
        ]);

        $statusChanged = $topicRequest->status !== $validated['status'];
        $topicLink = trim((string) ($validated['topic_link'] ?? ''));
        $topicLink = $topicLink !== '' ? $topicLink : null;
        $topicLinkChanged = $topicRequest->topic_link !== $topicLink;
        $data = [
            'status' => $validated['status'],
            'topic_link' => $topicLink,
        ];

        if ($validated['status'] === TopicRequest::STATUS_RESOLVED && $statusChanged) {
            $data['resolved_at'] = now();
        } elseif ($validated['status'] !== TopicRequest::STATUS_RESOLVED) {
            $data['resolved_at'] = null;
        }

        $topicRequest->update($data);

        if (($statusChanged || $topicLinkChanged) && $topicRequest->user) {
            $topicRequest->user->notify(new TopicRequestStatusUpdated($topicRequest->fresh('topic')));
        }

        return $this->redirectToListing($request, 'admin.topic-requests.index')->with('success', 'Topic request updated successfully.');
    }
}
