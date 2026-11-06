<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\GroupWebinarAdjustmentRequest;
use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarParticipantPayment;
use App\Models\GroupWebinarRequest;
use App\Models\Subject;
use App\Models\User;
use App\Services\CouponService;
use App\Services\GroupWebinarEmailService;
use App\Services\GroupWebinarPricing;
use App\Services\PreferredSlotService;
use App\Services\RazorpayService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GroupWebinarBookingController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay,
        private readonly CouponService $coupons,
        private readonly GroupWebinarEmailService $emails
    ) {}

    public function create()
    {
        $this->ensureRequestsEnabled();

        $years = AcademicYear::active()->orderBy('order')->get();
        $subjects = Subject::active()->orderBy('name')->get();

        return view('doubt-sessions.create', [
            'bookingType' => 'group',
            'submissionKey' => (string) Str::uuid(),
            'years' => $years,
            'subjects' => $subjects,
            'isFree' => GroupWebinarPricing::isFree(),
            'price' => GroupWebinarPricing::totalFor(1),
            'baseRate' => GroupWebinarPricing::baseRate(),
            'durationMinutes' => max(1, (int) get_setting('webinar_group_duration_minutes', 60)),
            'minimumParticipants' => 1,
            'maximumParticipants' => max(1, (int) get_setting('webinar_group_max_participants', 50)),
            'policyVersion' => config('webinar.group_policy_version'),
            'policySections' => config('webinar.group_policy'),
            'guidelines' => config('webinar.group_guidelines'),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureRequestsEnabled();

        $maxParticipants = max(1, (int) get_setting('webinar_group_max_participants', 50));
        $minParticipants = 1;
        $additionalMin = max(0, $minParticipants - 1);
        $additionalMax = max(0, $maxParticipants - 1);

        $participants = collect($request->input('participants', []))
            ->filter(static fn (mixed $participant): bool => is_array($participant)
                && filled($participant['email'] ?? null))
            ->values()
            ->all();
        $request->merge(['participants' => $participants]);

        $user = $request->user();
        $organizerErrors = [];
        if (! filled($user?->name)) {
            $organizerErrors['organizer_name'] = 'Your Student Representative name is required.';
        }
        if (! filter_var($user?->email, FILTER_VALIDATE_EMAIL)) {
            $organizerErrors['organizer_email'] = 'Your Student Representative email address must be valid.';
        }
        if ($organizerErrors !== []) {
            throw ValidationException::withMessages($organizerErrors);
        }

        $validated = $request->validate([
            'submission_key' => ['required', 'uuid'],
            'topic' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'preferred_schedule' => ['nullable', 'string', 'max:255'],
            'preferred_slots' => ['required', 'array', 'size:3'],
            'preferred_slots.*.date' => ['required', 'date_format:Y-m-d'],
            'preferred_slots.*.time' => ['required', 'date_format:H:i'],
            'academic_year_id' => ['nullable', 'integer', 'exists:academic_years,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'organizer_whatsapp' => ['required', 'string', 'max:16', 'regex:/^\+?[1-9][0-9]{7,14}$/'],
            'participants' => ['nullable', 'array', 'min:'.$additionalMin, 'max:'.$additionalMax],
            'participants.*.email' => ['nullable', 'email:rfc', 'max:255', 'distinct:ignore_case'],
            'terms_accepted' => ['accepted'],
        ], [
            'participants.min' => 'This request requires at least '.$minParticipants.' participants, including you as the Student Representative.',
            'participants.max' => 'The maximum group size is '.$maxParticipants.' participants, including you as the Student Representative.',
            'terms_accepted.accepted' => 'You must accept the Webinar Terms & Conditions and Privacy Policy before continuing.',
        ]);
        $validated['preferred_slots'] = PreferredSlotService::validate($validated['preferred_slots']);

        $existingRequest = GroupWebinarRequest::query()
            ->where('submission_key', $validated['submission_key'])
            ->first();
        if ($existingRequest) {
            abort_unless($existingRequest->organizer_id === $user->id, 409);

            return $this->redirectToExistingSubmission($existingRequest);
        }

        $emails = array_map(
            static fn (array $participant): string => mb_strtolower(trim($participant['email'])),
            $validated['participants'] ?? []
        );
        if (in_array(mb_strtolower($user->email), $emails, true)) {
            throw ValidationException::withMessages([
                'participants' => 'The Student Representative is already included. Do not add the Student Representative email again.',
            ]);
        }

        $participantCount = count($emails) + 1;
        if ($participantCount < $minParticipants || $participantCount > $maxParticipants) {
            throw ValidationException::withMessages([
                'participants' => 'The participant count must be between '.$minParticipants.' and '.$maxParticipants.'.',
            ]);
        }

        $isFree = GroupWebinarPricing::isFree();
        $totalAmount = GroupWebinarPricing::totalFor($participantCount);
        $participantAmounts = array_merge([$totalAmount], array_fill(0, $participantCount - 1, 0));
        $price = $totalAmount;
        $policyVersion = (string) config('webinar.group_policy_version');
        $descriptionParts = [
            filled($validated['academic_year_id'] ?? null)
                ? 'Academic year: '.AcademicYear::findOrFail($validated['academic_year_id'])->name
                : null,
            filled($validated['subject_id'] ?? null)
                ? 'Subject: '.Subject::findOrFail($validated['subject_id'])->name
                : null,
            filled($validated['preferred_schedule'] ?? null)
                ? 'Scheduling notes: '.trim($validated['preferred_schedule'])
                : null,
            filled($validated['preferred_slots'] ?? null)
                ? 'Preferred date and time slots: '.PreferredSlotService::summary($validated['preferred_slots'])
                : null,
            filled($validated['description'] ?? null) ? trim($validated['description']) : null,
        ];
        $description = implode("\n\n", array_filter($descriptionParts, static fn (?string $line): bool => filled($line)));
        $description = $description !== '' ? $description : null;

        $groupRequest = null;
        $organizerInvitation = null;
        $participantInvitations = [];
        try {
            DB::transaction(function () use ($validated, $description, $user, $participantCount, $minParticipants, $maxParticipants, $isFree, $price, $totalAmount, $participantAmounts, $policyVersion, &$groupRequest, &$organizerInvitation, &$participantInvitations): void {
                $this->ensureRequestsEnabled();
                $currentMaximum = max(1, (int) get_setting('webinar_group_max_participants', 50));
                if ($participantCount > $currentMaximum) {
                    throw ValidationException::withMessages([
                        'participants' => 'The maximum group size has changed. Please update the participant list and try again.',
                    ]);
                }

                $groupRequest = GroupWebinarRequest::create([
                    'organizer_id' => $user->id,
                    'submission_key' => $validated['submission_key'],
                    'reference' => 'GW-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                    'organizer_name' => $user->name,
                    'organizer_email' => $user->email,
                    'organizer_whatsapp' => $validated['organizer_whatsapp'],
                    // Keep writes compatible until the nullable-fields migration is deployed.
                    'topic' => filled($validated['topic'] ?? null) ? trim($validated['topic']) : '',
                    'description' => $description ?? '',
                    'preferred_slots' => $validated['preferred_slots'],
                    'participant_count' => $participantCount,
                    'minimum_participants' => $minParticipants,
                    'maximum_participants' => $maxParticipants,
                    'duration_minutes' => max(1, (int) get_setting('webinar_group_duration_minutes', 60)),
                    'price_per_person' => $price,
                    'total_amount' => $totalAmount,
                    'currency' => 'INR',
                    'is_free' => $isFree,
                    'status' => GroupWebinarRequest::STATUS_PENDING,
                    'policy_version' => $policyVersion,
                    'terms_accepted_at' => now(),
                ]);

                $entries = array_merge(
                    [[
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => 'organizer',
                        'user_id' => $user->id,
                    ]],
                    array_map(static function (array $participant): array {
                        $email = trim($participant['email']);
                        $account = User::query()->whereRaw('LOWER(email) = ?', [mb_strtolower($email)])->first();

                        return [
                            'name' => null,
                            'email' => $email,
                            'role' => 'participant',
                            'user_id' => $account?->id,
                        ];
                    }, $validated['participants'] ?? [])
                );

                foreach ($entries as $index => $entry) {
                    $token = Str::random(64);
                    $participant = $groupRequest->participants()->create([
                        ...$entry,
                        'email_normalized' => mb_strtolower(trim($entry['email'])),
                        'status' => GroupWebinarParticipant::STATUS_INVITATION_SENT,
                        'payment_status' => $isFree
                            ? GroupWebinarParticipant::PAYMENT_NOT_REQUIRED
                            : GroupWebinarParticipant::PAYMENT_PENDING,
                        'amount' => $participantAmounts[$index],
                        'amount_due' => $participantAmounts[$index],
                        'policy_version' => $policyVersion,
                        'terms_accepted_at' => $entry['role'] === 'organizer' ? now() : null,
                        'invitation_token_hash' => hash('sha256', $token),
                        'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
                    ]);
                    if ($entry['role'] === 'organizer') {
                        $organizerInvitation = [$participant, $token];
                    } else {
                        $participantInvitations[] = [$participant, $token];
                    }
                }
            });
        } catch (QueryException $exception) {
            $existingRequest = GroupWebinarRequest::query()
                ->where('submission_key', $validated['submission_key'])
                ->first();
            if ($existingRequest && $existingRequest->organizer_id === $user->id) {
                return $this->redirectToExistingSubmission($existingRequest);
            }

            throw $exception;
        }

        $organizerParticipant = $organizerInvitation[0];
        $organizerToken = $organizerInvitation[1];
        $emailQueueFailed = false;
        if (! $isFree) {
            $this->emails->notifyInApp(
                $organizerParticipant,
                'group_payment_link',
                'Complete your Group Webinar booking',
                'Your group request '.$groupRequest->reference.' is ready. Complete one payment of ₹'.number_format((float) $groupRequest->total_amount, 2).' for everyone included.'
            );
        }
        try {
            $this->emails->sendBookingRequestConfirmation(
                $organizerParticipant->fresh()->load('request'),
                $organizerToken,
                true,
                ! $isFree
            );
        } catch (Throwable $exception) {
            $emailQueueFailed = true;
            Log::error('Unable to queue the Group Webinar organizer booking email.', [
                'group_webinar_reference' => $groupRequest->reference,
                'participant_id' => $organizerParticipant->id,
                'exception' => $exception::class,
            ]);
        }
        foreach ($participantInvitations as [$participant, $token]) {
            try {
                $this->emails->sendBookingRequestConfirmation(
                    $participant->fresh()->load('request'),
                    $token,
                    false,
                    ! $isFree
                );
            } catch (Throwable $exception) {
                $emailQueueFailed = true;
                Log::error('Unable to queue a Group Webinar participant booking email.', [
                    'group_webinar_reference' => $groupRequest->reference,
                    'participant_id' => $participant->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        if ($emailQueueFailed) {
            return redirect()
                ->route('group-webinars.invitation', $organizerToken)
                ->with('webinar_error', 'Your Group Webinar request '.$groupRequest->reference.' was received, but one or more email notifications could not be queued. Please contact support with this reference.');
        }

        if ($isFree) {
            return redirect()
                ->route('group-webinars.invitation', $organizerToken)
                ->with('success', 'Your free Group Webinar request '.$groupRequest->reference.' has been received. Confirmation emails have been sent to everyone included.');
        }

        return redirect()
            ->route('group-webinars.invitation', $organizerToken)
            ->with('success', 'Your group request '.$groupRequest->reference.' has been received. Complete the group payment to confirm the booking; request emails have been sent to everyone included.');
    }

    public function requestAdjustment(Request $request, GroupWebinarRequest $groupWebinar)
    {
        abort_unless($groupWebinar->organizer_id === $request->user()->id, 404);

        $validated = $request->validate([
            'type' => ['required', 'in:dropout_refund,session_credit'],
            'participant_id' => ['nullable', 'integer', 'exists:group_webinar_participants,id'],
        ]);

        if ($validated['type'] === GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND) {
            $validated = $request->validate([
                'participant_id' => ['required', 'integer', 'exists:group_webinar_participants,id'],
            ]);
            abort_if($groupWebinar->participant_count <= 10, 422, 'Dropout refunds are available only for groups larger than 10 students.');
            abort_unless($groupWebinar->status === GroupWebinarRequest::STATUS_CONFIRMED && $groupWebinar->scheduled_at, 422, 'A confirmed schedule is required before requesting a dropout refund.');

            $deadline = $groupWebinar->scheduled_at->copy()->timezone('Asia/Kolkata')->subDay()->setTime(21, 0);
            abort_if(now('Asia/Kolkata')->gt($deadline), 422, 'Dropout refund requests must be submitted before 9:00 PM on the day before the session.');

            $participant = $groupWebinar->participants()->findOrFail($validated['participant_id']);
            $payment = $participant->payments()
                ->where('status', GroupWebinarParticipantPayment::STATUS_PAID)
                ->latest('paid_at')
                ->first();
            if (! $payment && $this->usesOrganizerPayment($groupWebinar)) {
                $payment = $groupWebinar->participants()
                    ->where('role', 'organizer')
                    ->firstOrFail()
                    ->payments()
                    ->where('status', GroupWebinarParticipantPayment::STATUS_PAID)
                    ->latest('paid_at')
                    ->first();
            }
            abort_unless($payment, 422, 'A verified group payment is required before requesting a dropout refund.');
            $adjustment = DB::transaction(function () use ($groupWebinar, $participant, $payment, $request): GroupWebinarAdjustmentRequest {
                GroupWebinarParticipant::query()->lockForUpdate()->findOrFail($participant->id);
                $hasExisting = GroupWebinarAdjustmentRequest::query()
                    ->where('participant_id', $participant->id)
                    ->where('type', GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND)
                    ->whereIn('status', [
                        GroupWebinarAdjustmentRequest::STATUS_PENDING,
                        GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                        GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
                        GroupWebinarAdjustmentRequest::STATUS_FAILED,
                    ])
                    ->exists();
                abort_if($hasExisting, 409, 'A dropout refund request already exists for this participant.');

                $alreadyRefunded = (float) $payment->adjustments()
                    ->where('type', GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND)
                    ->where('status', GroupWebinarAdjustmentRequest::STATUS_COMPLETED)
                    ->sum('amount');
                $amount = min(10, max(0, (float) $payment->amount - $alreadyRefunded));
                abort_if($amount <= 0, 422, 'There is no refundable amount remaining for this participant.');

                return GroupWebinarAdjustmentRequest::create([
                    'group_webinar_request_id' => $groupWebinar->id,
                    'participant_id' => $participant->id,
                    'payment_id' => $payment->id,
                    'requested_by' => $request->user()->id,
                    'type' => GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND,
                    'amount' => $amount,
                    'status' => GroupWebinarAdjustmentRequest::STATUS_PENDING,
                ]);
            });
        } else {
            abort_unless(
                $groupWebinar->status === GroupWebinarRequest::STATUS_PENDING
                    && ! $groupWebinar->scheduled_at
                    && filled($groupWebinar->admin_response),
                422,
                'A session credit may be requested after schedule discussions have started but no schedule is agreed.'
            );
            $organizerParticipant = $groupWebinar->participants()
                ->where('role', 'organizer')
                ->firstOrFail();
            $hasExistingCredit = $groupWebinar->adjustments()
                ->where('type', GroupWebinarAdjustmentRequest::TYPE_SESSION_CREDIT)
                ->whereIn('status', [
                    GroupWebinarAdjustmentRequest::STATUS_PENDING,
                    GroupWebinarAdjustmentRequest::STATUS_PROCESSING,
                    GroupWebinarAdjustmentRequest::STATUS_COMPLETED,
                ])
                ->exists();
            abort_if($hasExistingCredit, 409, 'A session credit request already exists for this booking.');

            $paidAmount = (float) $groupWebinar->participants()
                ->with('payments')
                ->get()
                ->sum(fn (GroupWebinarParticipant $participant) => $participant->payments
                    ->where('status', GroupWebinarParticipantPayment::STATUS_PAID)
                    ->sum(fn ($payment) => (float) $payment->amount));
            abort_if($paidAmount <= 0, 422, 'A session credit request requires a paid booking.');

            $adjustment = $groupWebinar->adjustments()->create([
                'participant_id' => $organizerParticipant->id,
                'requested_by' => $request->user()->id,
                'type' => GroupWebinarAdjustmentRequest::TYPE_SESSION_CREDIT,
                'amount' => $paidAmount,
                'status' => GroupWebinarAdjustmentRequest::STATUS_PENDING,
            ]);
        }

        $participantForNotification = $adjustment->participant;
        if ($participantForNotification) {
            $adjustmentMessage = $adjustment->type === GroupWebinarAdjustmentRequest::TYPE_DROPOUT_REFUND
                ? 'Your ₹'.number_format((float) $adjustment->amount, 2).' dropout refund request is awaiting admin review.'
                : 'Your future session credit request is awaiting admin review.';
            $this->emails->notifyInApp(
                $participantForNotification,
                'adjustment_requested',
                'Group webinar request received',
                $adjustmentMessage
            );
            try {
                $this->emails->sendAdjustmentUpdate($participantForNotification, $adjustmentMessage, 'requested-'.$adjustment->id);
            } catch (Throwable $exception) {
                Log::error('Unable to queue a group webinar adjustment email.', [
                    'adjustment_id' => $adjustment->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return redirect()->route('webinars.my')->with('success', 'Your request has been submitted for admin review.');
    }

    public function invitation(string $token)
    {
        $participant = $this->participantFromToken($token, allowExpired: true);
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 410);

        if (
            auth()->check()
            && ! $participant->user_id
            && hash_equals($participant->email_normalized, mb_strtolower(trim((string) auth()->user()->email)))
        ) {
            $participant->update(['user_id' => auth()->id()]);
        }

        return view('group-webinars.invitation', [
            'participant' => $participant,
            'expired' => ! $participant->invitation_expires_at || $participant->invitation_expires_at->isPast(),
            'token' => $token,
            'policyVersion' => $participant->request->policy_version,
            'policySections' => config('webinar.group_policy'),
            'organizerPayment' => $this->usesOrganizerPayment($participant->request),
        ]);
    }

    public function quoteCoupon(Request $request, string $token): JsonResponse
    {
        $participant = $this->participantFromToken($token);
        $organizerPayment = $this->usesOrganizerPayment($participant->request);
        abort_if(
            $participant->request->status === GroupWebinarRequest::STATUS_CANCELLED
                || $participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID
                || $participant->request->is_free
                || ($organizerPayment && $participant->role !== 'organizer'),
            404
        );

        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
        ]);

        try {
            $quote = $this->coupons->quote(
                $data['code'],
                $participant->user_id,
                'webinar',
                $organizerPayment ? (float) $participant->request->total_amount : (float) $participant->amount,
                email: $participant->email
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'valid' => false,
                'message' => $exception->errors()['coupon_code'][0] ?? 'Invalid coupon code.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Coupon applied successfully.',
            'code' => $quote['coupon']->code,
            'original_amount' => $quote['original_amount'],
            'discount_description' => $quote['discount_description'],
            'discount_amount' => $quote['discount_amount'],
            'final_amount' => $quote['final_amount'],
        ]);
    }

    public function startPayment(Request $request, string $token)
    {
        $participant = $this->participantFromToken($token);
        $organizerPayment = $this->usesOrganizerPayment($participant->request);
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 410);
        abort_if($participant->request->is_free, 404);
        abort_if($organizerPayment && $participant->role !== 'organizer', 404);
        if ($participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
            return redirect()->route('group-webinars.invitation', $token)->with('success', 'Your payment has already been confirmed.');
        }

        $rules = ['coupon_code' => ['nullable', 'string', 'max:64']];
        if ($participant->request->policy_version && ! $participant->terms_accepted_at) {
            $rules['terms_accepted'] = ['accepted'];
        }
        $validated = $request->validate($rules, [
            'terms_accepted.accepted' => 'You must accept the Webinar Terms & Conditions and Privacy Policy before continuing.',
        ]);
        $couponCode = trim((string) ($validated['coupon_code'] ?? ''));
        $baseAmount = $organizerPayment
            ? (float) $participant->request->total_amount
            : (float) $participant->amount;

        try {
            $attempt = DB::transaction(function () use ($participant, $couponCode, $baseAmount, $request): array {
                $lockedParticipant = GroupWebinarParticipant::query()
                    ->with('request')
                    ->lockForUpdate()
                    ->findOrFail($participant->id);

                if ($lockedParticipant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
                    return ['already_paid' => true];
                }
                if ($lockedParticipant->request->policy_version && ! $lockedParticipant->terms_accepted_at) {
                    if ($request->input('terms_accepted') !== '1' && $request->input('terms_accepted') !== 'on') {
                        throw ValidationException::withMessages([
                            'terms_accepted' => 'You must accept the Webinar Terms & Conditions and Privacy Policy before continuing.',
                        ]);
                    }

                    $lockedParticipant->update(['terms_accepted_at' => now()]);
                }

                $pending = $lockedParticipant->payments()
                    ->whereIn('status', [
                        GroupWebinarParticipantPayment::STATUS_PENDING,
                        GroupWebinarParticipantPayment::STATUS_ABANDONED,
                    ])
                    ->latest('id')
                    ->lockForUpdate()
                    ->first();
                if ($pending) {
                    if ($pending->coupon_code === ($couponCode ?: null) && filled($pending->razorpay_order_id)) {
                        $pending->update(['status' => GroupWebinarParticipantPayment::STATUS_PENDING]);

                        return ['payment' => $pending, 'reused' => true];
                    }

                    throw ValidationException::withMessages([
                        'coupon_code' => 'A payment attempt is already open. Complete it or wait for it to expire before starting another.',
                    ]);
                }

                $quote = $couponCode !== ''
                    ? $this->coupons->quote($couponCode, $lockedParticipant->user_id, 'webinar', $baseAmount, true, $lockedParticipant->email)
                    : [
                        'coupon' => null,
                        'original_amount' => round($baseAmount, 2),
                        'discount_amount' => 0,
                        'final_amount' => round($baseAmount, 2),
                    ];

                $payment = $lockedParticipant->payments()->create([
                    'public_reference' => (string) Str::uuid(),
                    'coupon_id' => $quote['coupon']?->id,
                    'coupon_code' => $quote['coupon']?->code,
                    'original_amount' => $quote['original_amount'],
                    'discount_amount' => $quote['discount_amount'],
                    'amount' => $quote['final_amount'],
                    'currency' => 'INR',
                    'status' => GroupWebinarParticipantPayment::STATUS_PENDING,
                ]);
                if ($quote['coupon']) {
                    $this->coupons->reserve($quote, $lockedParticipant->user_id, $payment, $lockedParticipant->email);
                }

                $lockedParticipant->update([
                    'coupon_id' => $quote['coupon']?->id,
                    'coupon_code' => $quote['coupon']?->code,
                    'amount_due' => $quote['final_amount'],
                    'status' => GroupWebinarParticipant::STATUS_PAYMENT_PENDING,
                ]);

                if ($quote['final_amount'] <= 0) {
                    $payment->update(['status' => GroupWebinarParticipantPayment::STATUS_PAID, 'paid_at' => now()]);
                    $lockedParticipant->update([
                        'status' => GroupWebinarParticipant::STATUS_PAID,
                        'payment_status' => GroupWebinarParticipant::PAYMENT_PAID,
                    ]);

                    return ['payment' => $payment, 'zero_payment' => true];
                }

                if (! $this->razorpay->isConfigured()) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Online payment is not configured yet. Please contact the administrator.',
                    ]);
                }

                try {
                    $order = $this->razorpay->createGroupWebinarParticipantOrder($payment);
                    $payment->update(['razorpay_order_id' => $order['id']]);
                } catch (Throwable $exception) {
                    $payment->update([
                        'status' => GroupWebinarParticipantPayment::STATUS_FAILED,
                        'payment_error' => 'Unable to create payment order.',
                    ]);
                    $lockedParticipant->update([
                        'status' => GroupWebinarParticipant::STATUS_PAYMENT_FAILED,
                        'payment_status' => GroupWebinarParticipant::PAYMENT_FAILED,
                    ]);
                    $this->coupons->release($payment);

                    return ['payment' => $payment, 'order_failed' => true];
                }

                return ['payment' => $payment->fresh(), 'order' => $order];
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Unable to initialize group webinar participant payment.', [
                'participant_id' => $participant->id,
                'exception' => $exception::class,
            ]);

            return back()->withInput()->with('webinar_error', 'Unable to start your payment. Please try again or contact support.');
        }

        if (! empty($attempt['already_paid'])) {
            return redirect()->route('group-webinars.invitation', $token)->with('success', 'Your payment has already been confirmed.');
        }
        if (! empty($attempt['zero_payment'])) {
            $confirmationsQueued = true;
            if ($organizerPayment) {
                $confirmationsQueued = $this->sendGroupBookingConfirmations($participant->request, $participant, $token);
            } else {
                try {
                    $this->emails->sendPaymentConfirmation($participant->fresh()->load('request'));
                } catch (Throwable $exception) {
                    $confirmationsQueued = false;
                    Log::error('Unable to queue a group webinar zero-payment confirmation email.', [
                        'participant_id' => $participant->id,
                        'exception' => $exception::class,
                    ]);
                }
            }

            return redirect()->route('group-webinars.invitation', $token)->with(
                $confirmationsQueued ? 'success' : 'webinar_error',
                $organizerPayment
                    ? ($confirmationsQueued
                        ? 'Your coupon covered the full group total. The booking is confirmed and confirmation emails have been queued.'
                        : 'Your coupon covered the full group total and the booking is confirmed, but one or more confirmation emails could not be queued. Please contact support.')
                    : ($confirmationsQueued
                        ? 'Your coupon covered the full individual amount. Your payment is confirmed.'
                        : 'Your coupon covered the full individual amount, but the confirmation email could not be queued. Please contact support.')
            );
        }
        if (! empty($attempt['order_failed'])) {
            return redirect()->route('group-webinars.invitation', $token)->with('webinar_error', 'Unable to start payment. Please try again.');
        }

        return redirect()->route('group-webinars.payment', [
            'token' => $token,
            'payment' => $attempt['payment']->public_reference,
        ]);
    }

    public function payment(string $token, string $reference)
    {
        $participant = $this->participantFromToken($token, allowExpired: true);
        $payment = $participant->payments()
            ->where('public_reference', $reference)
            ->whereIn('status', [
                GroupWebinarParticipantPayment::STATUS_PENDING,
                GroupWebinarParticipantPayment::STATUS_ABANDONED,
            ])
            ->firstOrFail();
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 410);
        $payment->load('coupon');

        return view('group-webinars.payment', [
            'participant' => $participant,
            'payment' => $payment,
            'token' => $token,
            'keyId' => $this->razorpay->keyId(),
            'amountSubunits' => $this->razorpay->amountToSubunits($payment->amount),
            'paymentDescription' => $payment->coupon_code
                ? sprintf(
                    'Coupon %s (%s): ₹%s − ₹%s = ₹%s',
                    $payment->coupon_code,
                    $payment->coupon?->discount_type === 'percentage'
                        ? rtrim(rtrim(number_format((float) $payment->coupon->discount_value, 2, '.', ''), '0'), '.').'%'
                        : 'fixed discount',
                    number_format((float) $payment->original_amount, 2),
                    number_format((float) $payment->discount_amount, 2),
                    number_format((float) $payment->amount, 2)
                )
                : $participant->request->topic,
        ]);
    }

    public function verifyPayment(Request $request, string $token, string $reference)
    {
        $participant = $this->participantFromToken($token, allowExpired: true);
        $organizerPayment = $this->usesOrganizerPayment($participant->request);
        abort_if($organizerPayment && $participant->role !== 'organizer', 404);
        $payment = $participant->payments()
            ->where('public_reference', $reference)
            ->whereIn('status', [
                GroupWebinarParticipantPayment::STATUS_PENDING,
                GroupWebinarParticipantPayment::STATUS_ABANDONED,
            ])
            ->firstOrFail();
        $data = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
            'razorpay_signature' => ['required', 'string', 'max:255'],
        ]);

        try {
            $this->razorpay->verifyGroupWebinarParticipantSignature($payment, $data['razorpay_payment_id'], $data['razorpay_signature']);
            $razorpayPayment = $this->razorpay->fetchPayment($data['razorpay_payment_id']);
            $this->razorpay->assertPaymentMatchesGroupWebinarParticipant($payment, $razorpayPayment);
            if ($razorpayPayment['status'] === 'authorized') {
                $razorpayPayment = $this->razorpay->capturePayment(
                    $data['razorpay_payment_id'],
                    $razorpayPayment
                );
                $this->razorpay->assertPaymentMatchesGroupWebinarParticipant($payment, $razorpayPayment);
            }
            if ($razorpayPayment['status'] !== 'captured') {
                return redirect()->route('group-webinars.invitation', $token)->with('webinar_error', 'Payment is not captured yet. Please wait a moment and retry verification.');
            }

            $bookingCancelled = DB::transaction(function () use ($payment, $participant, $data): bool {
                $lockedParticipant = GroupWebinarParticipant::query()->lockForUpdate()->findOrFail($participant->id);
                $lockedRequest = GroupWebinarRequest::query()
                    ->lockForUpdate()
                    ->findOrFail($lockedParticipant->group_webinar_request_id);
                $lockedPayment = GroupWebinarParticipantPayment::query()->lockForUpdate()->findOrFail($payment->id);
                if ($lockedPayment->status === GroupWebinarParticipantPayment::STATUS_PAID) {
                    return $lockedRequest->status === GroupWebinarRequest::STATUS_CANCELLED;
                }
                if ($lockedParticipant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
                    throw ValidationException::withMessages(['payment' => 'This participant payment is already complete.']);
                }

                $lockedPayment->update([
                    'razorpay_payment_id' => $data['razorpay_payment_id'],
                    'razorpay_signature' => $data['razorpay_signature'],
                    'status' => GroupWebinarParticipantPayment::STATUS_PAID,
                    'paid_at' => now(),
                    'payment_error' => null,
                ]);
                $lockedParticipant->update([
                    'status' => $lockedRequest->status === GroupWebinarRequest::STATUS_CANCELLED
                        ? GroupWebinarParticipant::STATUS_CANCELLED
                        : GroupWebinarParticipant::STATUS_PAID,
                    'payment_status' => GroupWebinarParticipant::PAYMENT_PAID,
                ]);
                $this->coupons->redeem($lockedPayment);

                return $lockedRequest->status === GroupWebinarRequest::STATUS_CANCELLED;
            });

            if ($organizerPayment && $bookingCancelled) {
                try {
                    $this->emails->sendRequestUpdate(
                        $participant->fresh()->load('request'),
                        'Razorpay verified a payment of ₹'.number_format((float) $payment->amount, 2).' after this Group Webinar request was cancelled. The booking remains cancelled; please contact the administrator about the payment.',
                        'payment_after_cancellation'
                    );
                } catch (Throwable $exception) {
                    Log::error('Unable to queue a Group Webinar cancelled-payment notice.', [
                        'group_webinar_reference' => $participant->request->reference,
                        'participant_id' => $participant->id,
                        'payment_id' => $payment->id,
                        'exception' => $exception::class,
                    ]);
                }

                return redirect()->route('webinars.my')
                    ->with('webinar_error', 'Payment was verified, but the Group Webinar request remains cancelled. Please contact the administrator about the payment.');
            }

            if ($organizerPayment) {
                $confirmationsQueued = $this->sendGroupBookingConfirmations($participant->request, $participant, $token);
            } else {
                $confirmationsQueued = true;
                try {
                    $this->emails->sendPaymentConfirmation($participant->fresh()->load('request'));
                } catch (Throwable $exception) {
                    $confirmationsQueued = false;
                    Log::error('Unable to queue a group webinar payment confirmation email.', [
                        'participant_id' => $participant->id,
                        'exception' => $exception::class,
                    ]);
                }
            }

            return redirect()->route('group-webinars.invitation', $token)->with(
                $confirmationsQueued ? 'success' : 'webinar_error',
                $organizerPayment
                    ? ($confirmationsQueued
                        ? 'Payment successful. The group booking is confirmed and confirmation emails have been queued.'
                        : 'Payment successful and the group booking is confirmed, but one or more confirmation emails could not be queued. Please contact support.')
                    : ($confirmationsQueued
                        ? 'Payment successful. Your individual payment is confirmed.'
                        : 'Payment successful, but the confirmation email could not be queued. Please contact support.')
            );
        } catch (Throwable $exception) {
            Log::warning('Group webinar payment verification failed.', [
                'participant_id' => $participant->id,
                'payment_id' => $payment->id,
                'exception' => $exception::class,
            ]);

            return redirect()->route('group-webinars.invitation', $token)->with('webinar_error', 'Payment could not be verified. No paid status was recorded. Please retry or contact support.');
        }
    }

    public function reportPaymentFailure(Request $request, string $token, string $reference)
    {
        $participant = $this->participantFromToken($token, allowExpired: true);
        $payment = $participant->payments()
            ->where('public_reference', $reference)
            ->whereIn('status', [
                GroupWebinarParticipantPayment::STATUS_PENDING,
                GroupWebinarParticipantPayment::STATUS_ABANDONED,
            ])
            ->firstOrFail();
        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string', 'max:100'],
        ]);

        try {
            $razorpayPayment = $this->razorpay->fetchPayment($validated['razorpay_payment_id']);
            $this->razorpay->assertPaymentMatchesGroupWebinarParticipant($payment, $razorpayPayment);
            if ($razorpayPayment['status'] !== 'failed') {
                return redirect()->route('group-webinars.invitation', $token)
                    ->with('webinar_error', 'The payment provider has not confirmed a failed payment. Your status remains pending.');
            }

            DB::transaction(function () use ($payment, $participant, $validated): void {
                $lockedParticipant = GroupWebinarParticipant::query()->lockForUpdate()->findOrFail($participant->id);
                $lockedPayment = GroupWebinarParticipantPayment::query()->lockForUpdate()->findOrFail($payment->id);
                if ($lockedPayment->status === GroupWebinarParticipantPayment::STATUS_PAID) {
                    return;
                }

                $lockedPayment->update([
                    'razorpay_payment_id' => $validated['razorpay_payment_id'],
                    'status' => GroupWebinarParticipantPayment::STATUS_FAILED,
                    'payment_error' => 'Razorpay reported that this payment attempt failed.',
                ]);
                if ($lockedParticipant->payment_status !== GroupWebinarParticipant::PAYMENT_PAID) {
                    $lockedParticipant->update([
                        'status' => $lockedParticipant->request->status === GroupWebinarRequest::STATUS_CANCELLED
                            ? GroupWebinarParticipant::STATUS_CANCELLED
                            : GroupWebinarParticipant::STATUS_PAYMENT_FAILED,
                        'payment_status' => GroupWebinarParticipant::PAYMENT_FAILED,
                    ]);
                    $this->coupons->release($lockedPayment);
                }
            });

            return redirect()->route('group-webinars.invitation', $token)
                ->with('webinar_error', 'Payment failed. You can try again using the same individual invitation.');
        } catch (Throwable $exception) {
            Log::warning('Unable to verify a reported group webinar payment failure.', [
                'participant_id' => $participant->id,
                'payment_id' => $payment->id,
                'exception' => $exception::class,
            ]);

            return redirect()->route('group-webinars.invitation', $token)
                ->with('webinar_error', 'The payment status could not be verified. Your participant status has not been changed.');
        }
    }

    public function abandonPayment(string $token, string $reference)
    {
        $participant = $this->participantFromToken($token, allowExpired: true);
        DB::transaction(function () use ($participant, $reference): void {
            $payment = $participant->payments()
                ->where('public_reference', $reference)
                ->lockForUpdate()
                ->first();
            if ($payment?->status === GroupWebinarParticipantPayment::STATUS_PENDING) {
                $payment->update(['status' => GroupWebinarParticipantPayment::STATUS_ABANDONED]);
            }
        });

        return redirect()->route('group-webinars.invitation', $token)
            ->with('webinar_error', 'Payment was not completed. Your payment remains pending; you can safely reopen the same invitation to continue.');
    }

    public function resendInvitation(Request $request)
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email:rfc', 'max:255'],
        ]);

        $participant = GroupWebinarParticipant::query()
            ->whereHas('request', fn ($query) => $query->where('reference', $validated['reference']))
            ->where('email_normalized', mb_strtolower(trim($validated['email'])))
            ->first();

        if ($participant && $participant->request->status !== GroupWebinarRequest::STATUS_CANCELLED) {
            $organizerPayment = $this->usesOrganizerPayment($participant->request);
            if (
                $organizerPayment
                && $participant->role !== 'organizer'
                && $participant->payment_status !== GroupWebinarParticipant::PAYMENT_PAID
            ) {
                return back()->with('success', 'If the reference and email match a confirmed booking, a participant confirmation has been sent.');
            }

            $token = Str::random(64);
            DB::transaction(function () use ($participant, $token): void {
                $participant->refresh();
                $participant->update([
                    'invitation_token_hash' => hash('sha256', $token),
                    'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
                    'invitation_sent_at' => null,
                ]);
            });
            try {
                if ($organizerPayment) {
                    if ($participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
                        $this->emails->sendGroupBookingConfirmation($participant->fresh()->load('request'), $token);
                    } elseif ($participant->role === 'organizer') {
                        $this->emails->sendGroupOrganizerPaymentLink($participant->fresh()->load('request'), $token);
                    }
                } else {
                    $this->emails->sendInvitation($participant->fresh()->load('request'), $token);
                }
            } catch (Throwable $exception) {
                Log::error('Unable to resend a group webinar invitation.', [
                    'participant_id' => $participant->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return back()->with('success', 'If the reference and email match an active participant, a new invitation has been sent.');
    }

    private function participantFromToken(string $token, bool $allowExpired = false): GroupWebinarParticipant
    {
        abort_unless(strlen($token) === 64 && ctype_alnum($token), 404);

        $participant = GroupWebinarParticipant::query()
            ->with('request')
            ->where('invitation_token_hash', hash('sha256', $token))
            ->firstOrFail();

        if (! $allowExpired) {
            abort_if(
                ! $participant->invitation_expires_at || $participant->invitation_expires_at->isPast(),
                410,
                'This invitation has expired. Request a new invitation using the reference and email sent to you.'
            );
        }

        return $participant;
    }

    private function usesOrganizerPayment(GroupWebinarRequest $groupRequest): bool
    {
        return ! $groupRequest->participants()
            ->where('role', 'participant')
            ->where('amount', '>', 0)
            ->exists();
    }

    private function sendGroupBookingConfirmations(
        GroupWebinarRequest $groupRequest,
        GroupWebinarParticipant $organizer,
        string $organizerToken
    ): bool {
        $payment = $organizer->payments()
            ->where('status', GroupWebinarParticipantPayment::STATUS_PAID)
            ->latest('id')
            ->first();
        $confirmationMessage = match (true) {
            $groupRequest->is_free => 'This Group Webinar is free; no payment is required.',
            $payment && (float) $payment->amount > 0 => "The Student Representative's combined payment of ₹".number_format((float) $payment->amount, 2).' has been verified.',
            $payment?->coupon_id !== null => 'A coupon covered the full group total; no payment was required.',
            default => 'The group payment status is being confirmed.',
        };

        DB::transaction(function () use ($groupRequest): void {
            $groupRequest->participants()->update([
                'payment_status' => GroupWebinarParticipant::PAYMENT_PAID,
                'status' => GroupWebinarParticipant::STATUS_PAID,
            ]);
        });

        $groupRequest->load('participants.user');
        $emailsQueued = true;
        foreach ($groupRequest->participants as $participant) {
            if ($participant->is($organizer)) {
                $token = $organizerToken;
            } else {
                $token = Str::random(64);
                $participant->update([
                    'invitation_token_hash' => hash('sha256', $token),
                    'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
                ]);
            }

            $message = 'You are included in Group Webinar booking '.$groupRequest->reference.'. '.$confirmationMessage;
            $this->emails->notifyInApp(
                $participant,
                'group_booking_confirmation',
                'Group webinar booking confirmed',
                $message
            );

            try {
                $this->emails->sendGroupBookingConfirmation($participant->load('request'), $token);
            } catch (Throwable $exception) {
                $emailsQueued = false;
                Log::error('Unable to queue a Group Webinar booking confirmation email.', [
                    'group_webinar_reference' => $groupRequest->reference,
                    'participant_id' => $participant->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return $emailsQueued;
    }

    private function ensureRequestsEnabled(): void
    {
        abort_unless(
            (string) get_setting('webinar_group_enabled', '0') === '1'
                && (string) get_setting('webinar_group_allow_requests', '1') === '1',
            404
        );
    }

    private function redirectToExistingSubmission(GroupWebinarRequest $groupRequest)
    {
        $organizer = $groupRequest->participants()
            ->where('role', 'organizer')
            ->first();
        $delivery = $organizer?->emailDeliveries()
            ->whereIn('notification_type', ['group_payment_link', 'invitation'])
            ->latest('id')
            ->first();
        $token = $delivery?->action_url
            ? basename((string) parse_url($delivery->action_url, PHP_URL_PATH))
            : null;

        if ($token && strlen($token) === 64 && ctype_alnum($token)) {
            return redirect()->route('group-webinars.invitation', $token)
                ->with('success', 'Your Group Webinar request '.$groupRequest->reference.' has already been received.');
        }

        return redirect()->route('webinars.my')
            ->with('success', 'Your Group Webinar request '.$groupRequest->reference.' has already been received.');
    }
}
