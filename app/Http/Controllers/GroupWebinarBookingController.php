<?php

namespace App\Http\Controllers;

use App\Models\GroupWebinarParticipant;
use App\Models\GroupWebinarParticipantPayment;
use App\Models\GroupWebinarRequest;
use App\Models\User;
use App\Services\CouponService;
use App\Services\GroupWebinarEmailService;
use App\Services\RazorpayService;
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
    ) {
    }

    public function create()
    {
        $this->ensureRequestsEnabled();

        return view('group-webinars.create', [
            'price' => $this->webinarIsFree() ? 0 : (float) get_setting('webinar_group_price_per_person', 0),
            'isFree' => $this->webinarIsFree(),
            'minimumParticipants' => (int) get_setting('webinar_group_min_participants', 2),
            'maximumParticipants' => (int) get_setting('webinar_group_max_participants', 10),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureRequestsEnabled();

        $maxParticipants = max(1, (int) get_setting('webinar_group_max_participants', 10));
        $minParticipants = max(1, (int) get_setting('webinar_group_min_participants', 2));
        $additionalMin = max(0, $minParticipants - 1);
        $additionalMax = max(0, $maxParticipants - 1);

        $validated = $request->validate([
            'topic' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:10', 'max:5000'],
            'participants' => ['nullable', 'array', 'min:'.$additionalMin, 'max:'.$additionalMax],
            'participants.*.name' => ['nullable', 'string', 'max:255'],
            'participants.*.email' => ['required', 'email:rfc', 'max:255', 'distinct:ignore_case'],
        ], [
            'participants.min' => 'This request requires at least '.$minParticipants.' participants, including you as the organizer.',
            'participants.max' => 'The maximum group size is '.$maxParticipants.' participants, including you as the organizer.',
        ]);

        $user = $request->user();
        $emails = array_map(
            static fn (array $participant): string => mb_strtolower(trim($participant['email'])),
            $validated['participants'] ?? []
        );
        if (in_array(mb_strtolower($user->email), $emails, true)) {
            throw ValidationException::withMessages([
                'participants' => 'The organizer is already included. Do not add the organizer email again.',
            ]);
        }

        $participantCount = count($emails) + 1;
        if ($participantCount < $minParticipants || $participantCount > $maxParticipants) {
            throw ValidationException::withMessages([
                'participants' => 'The participant count must be between '.$minParticipants.' and '.$maxParticipants.'.',
            ]);
        }

        $isFree = $this->webinarIsFree();
        $price = $isFree ? 0 : (float) get_setting('webinar_group_price_per_person', 0);
        if (! $isFree && $price <= 0) {
            throw ValidationException::withMessages([
                'topic' => 'Group webinar pricing is not configured. Please contact the administrator.',
            ]);
        }

        $groupRequest = null;
        $invitations = [];
        DB::transaction(function () use ($validated, $user, $participantCount, $minParticipants, $maxParticipants, $isFree, $price, &$groupRequest, &$invitations): void {
            $this->ensureRequestsEnabled();
            $currentMaximum = max(1, (int) get_setting('webinar_group_max_participants', 10));
            if ($participantCount > $currentMaximum) {
                throw ValidationException::withMessages([
                    'participants' => 'The maximum group size has changed. Please update the participant list and try again.',
                ]);
            }

            $groupRequest = GroupWebinarRequest::create([
                'organizer_id' => $user->id,
                'reference' => 'GW-'.now()->format('Ymd').'-'.strtoupper(Str::random(8)),
                'organizer_name' => $user->name,
                'organizer_email' => $user->email,
                'topic' => $validated['topic'],
                'description' => $validated['description'],
                'participant_count' => $participantCount,
                'minimum_participants' => $minParticipants,
                'maximum_participants' => $maxParticipants,
                'duration_minutes' => max(1, (int) get_setting('webinar_group_duration_minutes', 60)),
                'price_per_person' => $price,
                'currency' => 'INR',
                'is_free' => $isFree,
                'status' => GroupWebinarRequest::STATUS_PENDING,
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
                        'name' => filled($participant['name'] ?? null) ? trim($participant['name']) : null,
                        'email' => $email,
                        'role' => 'participant',
                        'user_id' => $account?->id,
                    ];
                }, $validated['participants'] ?? [])
            );

            foreach ($entries as $entry) {
                $token = Str::random(64);
                $participant = $groupRequest->participants()->create([
                    ...$entry,
                    'email_normalized' => mb_strtolower(trim($entry['email'])),
                    'status' => GroupWebinarParticipant::STATUS_INVITATION_SENT,
                    'payment_status' => $isFree
                        ? GroupWebinarParticipant::PAYMENT_NOT_REQUIRED
                        : GroupWebinarParticipant::PAYMENT_PENDING,
                    'amount' => $price,
                    'amount_due' => $price,
                    'invitation_token_hash' => hash('sha256', $token),
                    'invitation_expires_at' => now()->addHours(max(1, (int) get_setting('webinar_group_invitation_expiry_hours', 72))),
                ]);
                $invitations[] = [$participant, $token];
            }
        });

        $emailFailures = 0;
        foreach ($invitations as [$participant, $token]) {
            try {
                $this->emails->sendInvitation($participant->load('request'), $token);
            } catch (Throwable $exception) {
                $emailFailures++;
                Log::error('Unable to queue a group webinar invitation.', [
                    'group_webinar_reference' => $groupRequest->reference,
                    'participant_id' => $participant->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        $message = 'Your group webinar request '.$groupRequest->reference.' has been submitted. Each participant will receive an individual invitation and payment link.';
        if ($emailFailures > 0) {
            $message .= ' One or more invitation emails could not be queued; the administrator can retry delivery.';
        }

        return redirect()->route('webinars.index')->with('success', $message);
    }

    public function invitation(string $token)
    {
        $participant = $this->participantFromToken($token, allowExpired: true);
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 410);

        return view('group-webinars.invitation', [
            'participant' => $participant,
            'expired' => ! $participant->invitation_expires_at || $participant->invitation_expires_at->isPast(),
            'token' => $token,
        ]);
    }

    public function quoteCoupon(Request $request, string $token): JsonResponse
    {
        $participant = $this->participantFromToken($token);
        abort_if(
            $participant->request->status === GroupWebinarRequest::STATUS_CANCELLED
                || $participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID
                || $participant->request->is_free,
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
                (float) $participant->amount,
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
            'discount_amount' => $quote['discount_amount'],
            'final_amount' => $quote['final_amount'],
        ]);
    }

    public function startPayment(Request $request, string $token)
    {
        $participant = $this->participantFromToken($token);
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 410);
        abort_if($participant->request->is_free, 404);
        if ($participant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
            return redirect()->route('group-webinars.invitation', $token)->with('success', 'Your payment has already been confirmed.');
        }

        $validated = $request->validate(['coupon_code' => ['nullable', 'string', 'max:64']]);
        $couponCode = trim((string) ($validated['coupon_code'] ?? ''));
        $baseAmount = (float) $participant->amount;

        try {
            $attempt = DB::transaction(function () use ($participant, $couponCode, $baseAmount): array {
                $lockedParticipant = GroupWebinarParticipant::query()
                    ->with('request')
                    ->lockForUpdate()
                    ->findOrFail($participant->id);

                if ($lockedParticipant->payment_status === GroupWebinarParticipant::PAYMENT_PAID) {
                    return ['already_paid' => true];
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
            try {
                $this->emails->sendPaymentConfirmation($participant->fresh()->load('request'));
            } catch (Throwable $exception) {
                Log::error('Unable to queue a group webinar zero-payment confirmation email.', [
                    'participant_id' => $participant->id,
                    'exception' => $exception::class,
                ]);
            }
            return redirect()->route('group-webinars.invitation', $token)->with('success', 'Your coupon covered the full individual amount. Your payment is confirmed.');
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
        $participant = $this->participantFromToken($token);
        $payment = $participant->payments()
            ->where('public_reference', $reference)
            ->whereIn('status', [
                GroupWebinarParticipantPayment::STATUS_PENDING,
                GroupWebinarParticipantPayment::STATUS_ABANDONED,
            ])
            ->firstOrFail();
        abort_if($participant->request->status === GroupWebinarRequest::STATUS_CANCELLED, 410);

        return view('group-webinars.payment', [
            'participant' => $participant,
            'payment' => $payment,
            'token' => $token,
            'keyId' => $this->razorpay->keyId(),
        ]);
    }

    public function verifyPayment(Request $request, string $token, string $reference)
    {
        $participant = $this->participantFromToken($token);
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
            if ($razorpayPayment['status'] !== 'captured') {
                return redirect()->route('group-webinars.invitation', $token)->with('webinar_error', 'Payment is not captured yet. Please wait a moment and retry verification.');
            }

            DB::transaction(function () use ($payment, $participant, $data): void {
                $lockedParticipant = GroupWebinarParticipant::query()->lockForUpdate()->findOrFail($participant->id);
                $lockedPayment = GroupWebinarParticipantPayment::query()->lockForUpdate()->findOrFail($payment->id);
                if ($lockedPayment->status === GroupWebinarParticipantPayment::STATUS_PAID) {
                    return;
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
                    'status' => $lockedParticipant->request->status === GroupWebinarRequest::STATUS_CANCELLED
                        ? GroupWebinarParticipant::STATUS_CANCELLED
                        : GroupWebinarParticipant::STATUS_PAID,
                    'payment_status' => GroupWebinarParticipant::PAYMENT_PAID,
                ]);
                $this->coupons->redeem($lockedPayment);
            });

            try {
                $this->emails->sendPaymentConfirmation($participant->fresh()->load('request'));
            } catch (Throwable $exception) {
                Log::error('Unable to queue a group webinar payment confirmation email.', [
                    'participant_id' => $participant->id,
                    'exception' => $exception::class,
                ]);
            }

            return redirect()->route('group-webinars.invitation', $token)->with('success', 'Payment successful. Your individual payment is confirmed.');
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
        $participant = $this->participantFromToken($token);
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
        $participant = $this->participantFromToken($token);
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
                $this->emails->sendInvitation($participant->fresh()->load('request'), $token);
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

    private function ensureRequestsEnabled(): void
    {
        abort_unless(
            (string) get_setting('webinar_group_enabled', '0') === '1'
                && (string) get_setting('webinar_group_allow_requests', '1') === '1',
            404
        );
    }

    private function webinarIsFree(): bool
    {
        return (string) get_setting('webinar_group_is_free', '0') === '1';
    }
}
