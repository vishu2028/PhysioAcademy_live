<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\PreservesListingContext;
use App\Http\Controllers\Controller;
use App\Models\Doubt;
use App\Models\DoubtMessage;
use App\Notifications\DoubtAnswered;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DoubtController extends Controller
{
    use PreservesListingContext;

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
        ]);

        $query = Doubt::with([
            'user',
            'academicYear',
            'subject',
            'answeredBy',
            'messages.sender',
        ]);

        $search = trim($validated['search'] ?? '');

        if ($search !== '') {
            $term = '%' . addcslashes($search, '%_\\') . '%';

            $query->where(function ($doubts) use ($term): void {
                $doubts
                    ->whereHas('user', function ($users) use ($term): void {
                        $users
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term);
                    })
                    ->orWhereHas('subject', function ($subjects) use ($term): void {
                        $subjects->where('name', 'like', $term);
                    })
                    ->orWhere('topic', 'like', $term)
                    ->orWhere('message', 'like', $term)
                    ->orWhere('answer', 'like', $term);
            });
        }

        $doubts = $query->latest()->paginate(20)->withQueryString();

        return view('admin.doubts.index', compact('doubts'));
    }

    public function update(Request $request, Doubt $doubt)
    {
        $request->validate([
            'status' => ['required', 'in:pending,in_progress,answered,rejected'],
            'answer' => ['nullable', 'string'],
        ]);

        $wasAnswered = filled($doubt->answer);
        $previousAnswer = $doubt->answer;
        $answer = Doubt::sanitizeHtml($request->input('answer'));
        $data = [
            'status' => $request->status,
            'answer' => $answer ?: null,
        ];

        if (
            $request->status === Doubt::STATUS_ANSWERED
            && $answer
            && ! $doubt->answered_at
        ) {
            $data['answered_by'] = auth()->id();
            $data['answered_at'] = now();
        }

        DB::transaction(function () use ($doubt, $data, $answer, $wasAnswered, $previousAnswer): void {
            if (filled($previousAnswer) && $previousAnswer !== $answer) {
                $this->archiveLegacyAnswer($doubt, $previousAnswer);
            }

            $doubt->update($data);

            if (filled($answer) && $previousAnswer !== $answer) {
                $doubt->messages()->create([
                    'sender_id' => auth()->id(),
                    'sender_role' => DoubtMessage::ROLE_ADMIN,
                    'message' => $answer,
                ]);
            }

            if (
                ! $wasAnswered
                && $answer
                && $data['status'] === Doubt::STATUS_ANSWERED
                && $doubt->user
            ) {
                $doubt->user->notify(new DoubtAnswered($doubt->fresh([
                    'subject',
                ])));
            }
        });

        return $this->redirectToListing($request, 'admin.doubts.index')->with('success', 'Doubt updated successfully.');
    }

    private function archiveLegacyAnswer(Doubt $doubt, string $answer): void
    {
        $alreadyArchived = $doubt->messages()
            ->where('sender_role', DoubtMessage::ROLE_ADMIN)
            ->where('message', $answer)
            ->exists();

        if ($alreadyArchived) {
            return;
        }

        $doubt->messages()->create([
            'sender_id' => $doubt->answered_by,
            'sender_role' => DoubtMessage::ROLE_ADMIN,
            'message' => $answer,
            'created_at' => $doubt->answered_at ?? $doubt->updated_at,
        ]);
    }

    public function destroy(Request $request, Doubt $doubt)
    {
        $doubt->delete();

        return $this->redirectToListing($request, 'admin.doubts.index')->with('success', 'Doubt deleted successfully.');
    }
}