<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupWebinarAdjustmentRequest;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarParticipantPayment;
use App\Models\GroupWebinarRequest;
use App\Services\GroupWebinarEmailService;
use App\Services\RazorpayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class GroupWebinarRequestController extends Controller
{
    public function __construct(
        private readonly GroupWebinarEmailService $emails,
        private readonly RazorpayService $razorpay
    ) {}

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
            'adjustments.participant',
            'adjustments.payment',
            'adjustments.requester',
            'adjustments.processor',
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

            $groupWebinar->participants()->with('request')->get()->each(function (GroupWebinarParticipant $participant) use ($message, $eventKey, $groupWebinar): void {
                $rotatedTokenHash = null;
                $previousTokenHash = $participant->invitation_token_hash;
                $previousInvitationExpiry = $participant->invitation_expires_at;
                try {
                    $actionUrl = null;
                    if (
                        $eventKey === 'schedule_update'
                        && $groupWebinar->status === GroupWebinarRequest::STATUS_CONFIRMED
                        && $groupWebinar->scheduled_at
                    ) {
                        $token = Str::random(64);
                        $rotatedTokenHash = hash('sha256', $token);
                        $participant->update([
                            'invitation_token_hash' => $rotatedTokenHash,
                            'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
                        ]);
                        $actionUrl = route('group-webinars.invitation', ['token' => $token]);
                    }

                    $this->emails->sendRequestUpdate($participant, $message, $eventKey, $actionUrl);
                } catch (Throwable $exception) {
                    if ($rotatedTokenHash !== null) {
                        $participant->newQuery()
                            ->whereKey($participant->id)
                            ->where('invitation_token_hash', $rotatedTokenHash)
                            ->update([
                                'invitation_token_hash' => $previousTokenHash,
                                'invitation_expires_at' => $previousInvitationExpiry,
                            ]);
                    }
                    Log::error('Unable to queue a group webinar update email.', [
                        'participant_id' => $participant->id,
                        'exception' => $exception::class,
                    ]);
                }
            });
        }

        return redirect()->route('admin.group-webinars.show', $groupWebinar)->with('success', 'Group webinar request updated.');
    }

    public function updateAdjustment(Request $request, GroupWebinarAdjustmentRequest $adjustment)
    {
        $validated = $request->validate([
            'decision' => ['required', Rule::in(['approve', 'reject', 'refresh'])],
            'admin_response' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($validated['decision'] === 'refresh') {
            abort_unless(
                $adjustment->type === GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND
                    && $adjustment->status === GroupWebinarAdjustmentRequest::STATUS_PROCESSING
                    && filled($adjustment->provider_reference),
                409
            );
            try {
                $refund = $this->razorpay->fetchGroupWebinarRefund($adjustment->provider_reference);
            } catch (Throwable $exception) {
                Log::error('Unable to refresh a group webinar refund status.', [
                    'adjustment_id' => $adjustment->id,
                    'exception' => $exception::class,
                ]);

                return back()->withErrors(['refund' => 'The refund status could not be refreshed. Check the payment provider dashboard.']);
            }

            $adjustment->update([
                'status' => match ($refund['status']) {
                    'processed' => GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
                    'failed' => GroupWebinarAdjustmentRequest::STATUS_FAILED,
                    default => GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                },
                'processed_at' => $refund['status'] === 'processed' ? now() : null,
            ]);
        } else {
            $adjustment = DB::transaction(function () use ($validated, $request, $adjustment): GroupWebinarAdjustmentRequest {
                $locked = GroupWebinarAdjustmentRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($adjustment->id);
                abort_unless($locked->status === GroupWebinarAdjustmentRequest::STATUS_PENDING, 409);

                if ($validated['decision'] === 'reject') {
                    $locked->update([
                        'status' => GroupWebinarAdjustmentRequest::STATUS_REJECTED,
                        'admin_response' => $validated['admin_response'] ?? null,
                        'processed_by' => $request->user()->id,
                        'processed_at' => now(),
                    ]);
                } else {
                    $locked->update([
                        'status' => GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                        'admin_response' => $validated['admin_response'] ?? null,
                        'processed_by' => $request->user()->id,
                    ]);
                }

                return $locked->fresh([
                    'request',
                    'participant',
                    'payment',
                ]);
            });
        }

        if ($validated['decision'] === 'approve' && $adjustment->type === GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND) {
            try {
                $refund = $this->razorpay->refundGroupWebinarParticipantPayment(
                    $adjustment->payment,
                    (float) $adjustment->amount,
                    'GW-REFUND-'.$adjustment->id
                );
            } catch (Throwable $exception) {
                $adjustment->update([
                    'status' => GroupWebinarAdjustmentRequest::STATUS_FAILED,
                    'admin_response' => 'The refund could not be confirmed by Razorpay. Verify the provider dashboard before taking further action.',
                    'processed_at' => now(),
                ]);
                Log::error('Unable to process a group webinar dropout refund.', [
                    'adjustment_id' => $adjustment->id,
                    'payment_id' => $adjustment->payment_id,
                    'exception' => $exception::class,
                ]);

                return redirect()->route('admin.group-webinars.show', $adjustment->group_webinar_request_id)
                    ->withErrors(['refund' => 'Razorpay did not confirm the refund. Check the provider dashboard before retrying.']);
            }

            $adjustment->update([
                'status' => $refund['status'] === 'processed'
                    ? GroupWebinarAdjustmentRequest::STATUS_COMPLETED
                    : GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                'provider_reference' => $refund['id'],
                'processed_at' => $refund['status'] === 'processed' ? now() : null,
            ]);
        } elseif ($validated['decision'] === 'approve') {
            $creditRequest = trim((string) $adjustment->request->admin_response);
            $creditRequest .= ($creditRequest === '' ? '' : "\n\n")
                .'Converted to a future session credit after schedule negotiations.';
            $adjustment->update([
                'status' => GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
                'provider_reference' => 'SESSION-CREDIT-'.$adjustment->id,
                'processed_at' => now(),
            ]);
            $adjustment->request->update([
                'status' => GroupWebinarRequest::STATUS_PENDING,
                'scheduled_at' => null,
                'meeting_link' => null,
                'admin_response' => $creditRequest,
            ]);
            $adjustment->request->participants()->get()->each(function (GroupWebinarParticipant $participant): void {
                $participant->update([
                    'status' => $participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID
                        ? GroupWebinarParticipant::STATUS_PAID
                        : GroupWebinarParticipant::STATUS_PAYMENT_PENDING,
                ]);
            });
            $adjustment->request->participants()->with('request')->get()->each(function (GroupWebinarParticipant $participant): void {
                try {
                    $this->emails->sendRequestUpdate(
                        $participant,
                        'The group request has been converted to a future session credit. The admin can coordinate a new schedule through this booking.',
                        'schedule_update'
                    );
                } catch (Throwable $exception) {
                    Log::error('Unable to queue a group webinar session-credit cancellation email.', [
                        'participant_id' => $participant->id,
                        'exception' => $exception::class,
                    ]);
                }
            });
        }

        $adjustment->loadMissing('participant');
        if ($adjustment->participant) {
            $message = $adjustment->status === GroupWebinarAdjustmentRequest::STATUS_COMPLETED
                ? ($adjustment->type === GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND
                    ? 'Your ₹'.number_format((float) $adjustment->amount, 2).' dropout refund was processed.'
                    : 'Your future session credit request was approved.')
                : ($adjustment->status === GroupWebinarAdjustmentRequest::STATUS_PROCESSING
                    ? 'Your refund was initiated with the payment provider and is still processing.'
                    : ($adjustment->status === GroupWebinarAdjustmentRequest::STATUS_FAILED
                        ? 'The payment provider did not confirm the requested refund. The administrator will review it.'
                        : 'Your group webinar adjustment request was declined.'));
            $this->emails->notifyInApp(
                $adjustment->participant,
                'adjustment_'.$adjustment->status,
                'Group webinar request update',
                $message
            );
            try {
                $this->emails->sendAdjustmentUpdate($adjustment->participant, $message, $adjustment->status.'-'.$adjustment->id);
            } catch (Throwable $exception) {
                Log::error('Unable to queue a group webinar adjustment update email.', [
                    'adjustment_id' => $adjustment->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return redirect()->route('admin.group-webinars.show', $adjustment->group_webinar_request_id)
            ->with('success', 'The adjustment request was '.$adjustment->status.'.');
    }

    public function resendInvitation(GroupWebinarParticipant $participant)
    {
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 409);
        $organizerPayment = ! $participant->request->participants()
            ->where('role', 'participant')
            ->where('amount', '>', 0)
            ->exists();
        if (
            $organizerPayment
            && $participant->role !== 'organizer'
            && $participant->payment_status !== GroupWebinarParticipant::PAYMENT_PAID
        ) {
            return back()->withErrors(['email' => 'The organizer completes the group payment. Participant confirmations are sent after payment succeeds.']);
        }

        $token = Str::random(64);
        $participant->update([
            'invitation_token_hash' => hash('sha256', $token),
            'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
            'invitation_sent_at' => null,
        ]);

        try {
            $participant = $participant->fresh()->load('request');
            if ($organizerPayment && $participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
                $this->emails->sendGroupBookingConfirmation($participant, $token);
            } elseif ($organizerPayment && $participant->role === 'organizer') {
                $this->emails->sendGroupOrganizerPaymentLink($participant, $token);
            } else {
                $this->emails->sendInvitation($participant, $token);
            }
        } catch (Throwable $exception) {
            Log::error('Unable to resend a group webinar invitation from admin.', [
                'participant_id' => $participant->id,
                'exception' => $exception::class,
            ]);

            return back()->withErrors(['email' => 'The invitation could not be queued. Please check the configured mail service and try again.']);
        }

        return back()->with('success', $organizerPayment
            ? 'A Group Webinar booking email was queued for '.$participant->email.'.'
            : 'A new individual invitation was queued for '.$participant->email.'.');
    }
}
