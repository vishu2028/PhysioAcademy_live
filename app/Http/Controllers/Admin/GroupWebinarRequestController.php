<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarParticipantPayment;
use App\Models\GroupWebinarRequest;
use App\Services\GroupWebinarEmailService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class GroupWebinarRequestController extends Controller
{
    public function __construct(private readonly GroupWebinarEmailService $emails)
    {
    }

    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in([
                GroupWebinarRequest::STATUS_PENDING,
                GroupWebinarRequest::STATUS_CONFIRMED,
                GroupWebinarRequest::STATUS_COMPLETED,
                GroupWebinarRequest::STATUS_CANCELLED,
            ])],
            'payment_status' => ['nullable', Rule::in(['fully_paid', 'partial', 'pending'])],
            'scheduling_status' => ['nullable', Rule::in(['scheduled', 'not_scheduled'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $search = trim((string) ($validated['search'] ?? ''));
        $dueParticipant = static fn ($query) => $query
            ->whereIn('payment_status', [
                GroupWebinarParticipant::PAYMENT_PENDING,
                GroupWebinarParticipant::PAYMENT_FAILED,
            ])
            ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
            ->whereHas('request', fn ($requestQuery) => $requestQuery->where('status', '!=', GroupWebinarRequest::STATUS_CANCELLED));

        $requests = GroupWebinarRequest::query()
            ->withCount([
                'participants',
                'participants as active_participants_count' => fn ($query) => $query
                    ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED),
                'participants as paid_participants_count' => fn ($query) => $query
                    ->where('payment_status', GroupWebinarParticipant::PAYMENT_PAID),
                'participants as no_payment_required_count' => fn ($query) => $query
                    ->where('payment_status', GroupWebinarParticipant::PAYMENT_NOT_REQUIRED),
                'participants as pending_participants_count' => $dueParticipant,
            ])
            ->withSum('participants as expected_amount', 'amount_due')
            ->withSum([
                'participants as pending_amount' => fn ($query) => $query
                    ->whereIn('payment_status', [
                        GroupWebinarParticipant::PAYMENT_PENDING,
                        GroupWebinarParticipant::PAYMENT_FAILED,
                    ])
                    ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
                    ->whereHas('request', fn ($requestQuery) => $requestQuery->where('status', '!=', GroupWebinarRequest::STATUS_CANCELLED)),
            ], 'amount_due')
            ->addSelect([
                'collected_amount' => DB::table('group_webinar_participant_payments')
                    ->join('group_webinar_participants', 'group_webinar_participants.id', '=', 'group_webinar_participant_payments.participant_id')
                    ->whereColumn('group_webinar_participants.group_webinar_request_id', 'group_webinar_requests.id')
                    ->where('group_webinar_participant_payments.status', GroupWebinarParticipantPayment::STATUS_PAID)
                    ->where('group_webinar_participant_payments.currency', 'INR')
                    ->selectRaw('COALESCE(SUM(group_webinar_participant_payments.amount), 0)'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery->where('reference', 'like', '%'.$search.'%')
                        ->orWhere('organizer_name', 'like', '%'.$search.'%')
                        ->orWhere('organizer_email', 'like', '%'.$search.'%')
                        ->orWhere('topic', 'like', '%'.$search.'%')
                        ->orWhereHas('participants', fn ($participants) => $participants->where('email', 'like', '%'.$search.'%'));
                });
            })
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(($validated['scheduling_status'] ?? null) === 'scheduled', fn ($query) => $query->whereNotNull('scheduled_at'))
            ->when(($validated['scheduling_status'] ?? null) === 'not_scheduled', fn ($query) => $query->whereNull('scheduled_at'))
            ->when($validated['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($validated['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->when(($validated['payment_status'] ?? null) === 'fully_paid', function ($query): void {
                $query->whereHas('participants', fn ($participants) => $participants
                    ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED))
                    ->whereDoesntHave('participants', fn ($participants) => $participants
                        ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
                        ->whereNotIn('payment_status', [
                            GroupWebinarParticipant::PAYMENT_PAID,
                            GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
                        ]));
            })
            ->when(($validated['payment_status'] ?? null) === 'partial', function ($query): void {
                $query->whereHas('participants', fn ($participants) => $participants
                    ->where('payment_status', GroupWebinarParticipant::PAYMENT_PAID))
                    ->whereHas('participants', fn ($participants) => $participants
                        ->whereIn('payment_status', [
                            GroupWebinarParticipant::PAYMENT_PENDING,
                            GroupWebinarParticipant::PAYMENT_FAILED,
                        ])
                        ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
                        ->whereHas('request', fn ($requestQuery) => $requestQuery->where('status', '!=', GroupWebinarRequest::STATUS_CANCELLED)));
            })
            ->when(($validated['payment_status'] ?? null) === 'pending', function ($query) use ($dueParticipant): void {
                $query->whereDoesntHave('participants', fn ($participants) => $participants
                    ->where('payment_status', GroupWebinarParticipant::PAYMENT_PAID))
                    ->whereHas('participants', $dueParticipant);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $participantCounts = GroupWebinarParticipant::query()
            ->selectRaw('COUNT(*) AS total_count')
            ->selectRaw(
                'SUM(CASE WHEN payment_status IN (?, ?) AND status != ? AND group_webinar_request_id IN (SELECT id FROM group_webinar_requests WHERE status != ?) THEN 1 ELSE 0 END) AS pending_payment_count',
                [
                    GroupWebinarParticipant::PAYMENT_PENDING,
                    GroupWebinarParticipant::PAYMENT_FAILED,
                    GroupWebinarParticipant::STATUS_CANCELLED,
                    GroupWebinarRequest::STATUS_CANCELLED,
                ]
            )
            ->first();

        $fullyPaidWebinars = GroupWebinarRequest::query()
            ->whereHas('participants', fn ($query) => $query->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED))
            ->whereDoesntHave('participants', fn ($query) => $query
                ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
                ->whereNotIn('payment_status', [
                    GroupWebinarParticipant::PAYMENT_PAID,
                    GroupWebinarParticipant::PAYMENT_NOT_REQUIRED,
                ]))
            ->count();

        $counts = [
            'total_requests' => GroupWebinarRequest::count(),
            'total_participants' => (int) ($participantCounts->total_count ?? 0),
            'pending_payments' => (int) ($participantCounts->pending_payment_count ?? 0),
            'fully_paid' => $fullyPaidWebinars,
            'needs_scheduling' => GroupWebinarRequest::where('status', GroupWebinarRequest::STATUS_PENDING)
                ->whereNull('scheduled_at')
                ->count(),
            'confirmed' => GroupWebinarRequest::where('status', GroupWebinarRequest::STATUS_CONFIRMED)->count(),
            'completed' => GroupWebinarRequest::where('status', GroupWebinarRequest::STATUS_COMPLETED)->count(),
        ];

        $now = now();
        $revenueSummary = GroupWebinarParticipantPayment::query()
            ->where('status', GroupWebinarParticipantPayment::STATUS_PAID)
            ->where('currency', 'INR')
            ->whereNotNull('paid_at')
            ->where('paid_at', '<=', $now)
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN paid_at >= ? THEN amount ELSE 0 END), 0) AS daily,
                 COALESCE(SUM(CASE WHEN paid_at >= ? THEN amount ELSE 0 END), 0) AS weekly,
                 COALESCE(SUM(CASE WHEN paid_at >= ? THEN amount ELSE 0 END), 0) AS monthly,
                 COALESCE(SUM(CASE WHEN paid_at >= ? THEN amount ELSE 0 END), 0) AS yearly,
                 COALESCE(SUM(amount), 0) AS total_collected',
                [
                    $now->copy()->startOfDay(),
                    $now->copy()->startOfWeek(Carbon::MONDAY),
                    $now->copy()->startOfMonth(),
                    $now->copy()->startOfYear(),
                ]
            )
            ->first();

        $totalPending = GroupWebinarParticipant::query()
            ->whereIn('payment_status', [
                GroupWebinarParticipant::PAYMENT_PENDING,
                GroupWebinarParticipant::PAYMENT_FAILED,
            ])
            ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
            ->whereHas('request', fn ($query) => $query->where('status', '!=', GroupWebinarRequest::STATUS_CANCELLED))
            ->sum('amount_due');

        $revenues = [
            'daily' => (float) ($revenueSummary->daily ?? 0),
            'weekly' => (float) ($revenueSummary->weekly ?? 0),
            'monthly' => (float) ($revenueSummary->monthly ?? 0),
            'yearly' => (float) ($revenueSummary->yearly ?? 0),
            'total_collected' => (float) ($revenueSummary->total_collected ?? 0),
            'total_pending' => (float) $totalPending,
        ];

        return view('admin.group-webinars.index', compact('requests', 'counts', 'validated', 'revenues'));
    }

    public function show(GroupWebinarRequest $groupWebinar)
    {
        $groupWebinar->load([
            'organizer',
            'confirmedBy',
            'participants' => fn ($query) => $query->orderByRaw("CASE WHEN role = 'organizer' THEN 0 ELSE 1 END")->orderBy('id'),
            'participants.payments' => fn ($query) => $query->latest(),
            'participants.emailDeliveries' => fn ($query) => $query->latest(),
        ]);

        $expected = $groupWebinar->participants->sum(fn (GroupWebinarParticipant $participant) => (float) $participant->amount_due);
        $collected = $groupWebinar->participants->sum(
            fn (GroupWebinarParticipant $participant) => $participant->payments
                ->where('status', GroupWebinarParticipantPayment::STATUS_PAID)
                ->where('currency', 'INR')
                ->sum(fn ($payment) => (float) $payment->amount)
        );
        $pending = $groupWebinar->participants
            ->whereIn('payment_status', [
                GroupWebinarParticipant::PAYMENT_PENDING,
                GroupWebinarParticipant::PAYMENT_FAILED,
            ])
            ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
            ->sum(fn (GroupWebinarParticipant $participant) => (float) $participant->amount_due);

        return view('admin.group-webinars.show', compact('groupWebinar', 'expected', 'collected', 'pending'));
    }

    public function update(Request $request, GroupWebinarRequest $groupWebinar)
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                GroupWebinarRequest::STATUS_PENDING,
                GroupWebinarRequest::STATUS_CONFIRMED,
                GroupWebinarRequest::STATUS_COMPLETED,
                GroupWebinarRequest::STATUS_CANCELLED,
            ])],
            'scheduled_at' => ['nullable', 'date', Rule::requiredIf($request->input('status') === GroupWebinarRequest::STATUS_CONFIRMED)],
            'meeting_link' => ['nullable', 'url', 'max:2048'],
            'admin_response' => ['nullable', 'string', 'max:5000'],
        ]);

        $before = [
            $groupWebinar->status,
            $groupWebinar->scheduled_at?->toIso8601String(),
            $groupWebinar->meeting_link,
            $groupWebinar->admin_response,
        ];
        DB::transaction(function () use ($validated, $request, $groupWebinar): void {
            $groupWebinar->update([
                ...$validated,
                'confirmed_by' => $validated['status'] === GroupWebinarRequest::STATUS_CONFIRMED
                    ? $request->user()->id
                    : $groupWebinar->confirmed_by,
            ]);

            if ($validated['status'] === GroupWebinarRequest::STATUS_CANCELLED) {
                $groupWebinar->participants()
                    ->where('status', '!=', GroupWebinarParticipant::STATUS_CANCELLED)
                    ->update(['status' => GroupWebinarParticipant::STATUS_CANCELLED]);
            }
        });

        $groupWebinar->refresh();
        $after = [
            $groupWebinar->status,
            $groupWebinar->scheduled_at?->toIso8601String(),
            $groupWebinar->meeting_link,
            $groupWebinar->admin_response,
        ];
        $changed = $before !== $after;
        if ($changed) {
            $eventKey = $groupWebinar->status === GroupWebinarRequest::STATUS_CANCELLED
                ? 'cancellation'
                : 'schedule_update';
            $message = $groupWebinar->status === GroupWebinarRequest::STATUS_CANCELLED
                ? 'The group webinar has been cancelled.'
                : ($groupWebinar->status === GroupWebinarRequest::STATUS_CONFIRMED
                    ? 'The group webinar has been confirmed or its schedule updated.'
                    : 'There is an update to your group webinar request.');

            $groupWebinar->participants()->with('request')->get()->each(function (GroupWebinarParticipant $participant) use ($message, $eventKey): void {
                try {
                    $this->emails->sendRequestUpdate($participant, $message, $eventKey);
                } catch (Throwable $exception) {
                    Log::error('Unable to queue a group webinar update email.', [
                        'participant_id' => $participant->id,
                        'exception' => $exception::class,
                    ]);
                }
            });
        }

        return redirect()->route('admin.group-webinars.show', $groupWebinar)->with('success', 'Group webinar request updated.');
    }

    public function resendInvitation(GroupWebinarParticipant $participant)
    {
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 409);

        $token = Str::random(64);
        $participant->update([
            'invitation_token_hash' => hash('sha256', $token),
            'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
            'invitation_sent_at' => null,
        ]);

        try {
            $this->emails->sendInvitation($participant->fresh()->load('request'), $token);
        } catch (Throwable $exception) {
            Log::error('Unable to resend a group webinar invitation from admin.', [
                'participant_id' => $participant->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['email' => 'The invitation could not be queued. Please check the configured mail service and try again.']);
        }

        return back()->with('success', 'A new individual invitation was queued for '.$participant->email.'.');
    }
}
