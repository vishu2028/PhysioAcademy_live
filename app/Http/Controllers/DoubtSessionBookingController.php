<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\DoubtSessionBooking;
use App\Models\Subject;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Razorpay\Api\Errors\ServerError;
use Razorpay\Api\Errors\SignatureVerificationError;
use Throwable;
use UnexpectedValueException;

class DoubtSessionBookingController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay
    ) {
    }

    /**
     * Display the student booking form.
     */
    public function create()
    {
        if (! $this->sessionsEnabled()) {
            return redirect()
                ->to(route('home') . '#ask-doubt')
                ->with(
                    'session_error',
                    'One-on-one doubt sessions are currently unavailable.'
                );
        }

        $years = AcademicYear::active()
            ->orderBy('order')
            ->get();

        $subjects = Subject::active()
            ->orderBy('name')
            ->get();

        $isFree = $this->sessionIsFree();

        $price = $isFree
            ? 0
            : max(
                (float) get_setting(
                    'doubt_session_price',
                    0
                ),
                0
            );

        $durationMinutes = $this->sessionDuration();

        return view(
            'doubt-sessions.create',
            compact(
                'years',
                'subjects',
                'isFree',
                'price',
                'durationMinutes'
            )
        );
    }

    /**
     * Display the authenticated student's doubt session history.
     */
    /**
     * Display the authenticated student's doubt session history.
     */
    public function history(Request $request)
    {
        $bookings = $request->user()
            ->doubtSessionBookings()
            ->latest('created_at')
            ->paginate(10);

        return view(
            'doubt-sessions.history',
            compact('bookings')
        );
    }

    /**
     * Store a free booking or initialise
     * the Razorpay paid-booking process.
     */
    public function store(Request $request)
    {
        if (! $this->sessionsEnabled()) {
            return redirect()
                ->to(route('home') . '#ask-doubt')
                ->with(
                    'session_error',
                    'One-on-one doubt sessions are currently unavailable.'
                );
        }

        $validated = $request->validate([
            'phone' => [
                'required',
                'string',
                'min:7',
                'max:30',
                'regex:/^[0-9+\-\s()]+$/',
            ],
            'academic_year_id' => [
                'required',
                'integer',
                'exists:academic_years,id',
            ],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
            ],
            'topic' => [
                'required',
                'string',
                'max:255',
            ],
            'doubt' => [
                'required',
                'string',
                'min:10',
                'max:5000',
            ],
        ], [
            'phone.regex' =>
                'Please enter a valid phone number.',

            'academic_year_id.required' =>
                'Please select your academic year.',

            'subject_id.required' =>
                'Please select a subject.',

            'doubt.min' =>
                'Please describe your doubt in at least 10 characters.',
        ]);

        /*
         * Always obtain price, duration and free/paid status
         * directly from current admin settings.
         */
        $isFree = $this->sessionIsFree();

        $amount = $isFree
            ? 0
            : max(
                (float) get_setting(
                    'doubt_session_price',
                    0
                ),
                0
            );

        $durationMinutes = $this->sessionDuration();
        $user = $request->user();

        /*
         * Free booking flow.
         */
        if ($isFree) {
            $booking = DB::transaction(function () use (
                $validated,
                $user,
                $durationMinutes
            ) {
                return DoubtSessionBooking::create([
                    'booking_reference' =>
                        $this->generateBookingReference(),

                    'user_id' => $user->id,
                    'student_name' => $user->name,
                    'student_email' => $user->email,
                    'phone' => $validated['phone'],

                    'academic_year_id' =>
                        $validated['academic_year_id'],

                    'subject_id' =>
                        $validated['subject_id'],

                    'topic' => $validated['topic'],
                    'doubt' => $validated['doubt'],

                    'duration_minutes' =>
                        $durationMinutes,

                    'amount' => 0,
                    'currency' => 'INR',
                    'is_free' => true,

                    'payment_status' =>
                        DoubtSessionBooking::PAYMENT_NOT_REQUIRED,

                    'booking_status' =>
                        DoubtSessionBooking::STATUS_PENDING_SCHEDULE,
                ]);
            });

            return redirect()->route(
                'doubt-sessions.success',
                $booking
            );
        }

        /*
         * Paid booking validation.
         */
        if ($amount <= 0) {
            return redirect()
                ->to(
                    route('doubt-sessions.create')
                    . '#booking-form'
                )
                ->withInput()
                ->with(
                    'session_error',
                    'The paid session price has not been configured by the admin.'
                );
        }

        if (! $this->razorpay->isConfigured()) {
            return redirect()
                ->to(
                    route('doubt-sessions.create')
                    . '#booking-form'
                )
                ->withInput()
                ->with(
                    'session_error',
                    'The online payment gateway is not configured yet. Please try again later.'
                );
        }

        /*
         * Create a local pending booking first.
         * The amount and duration are stored as snapshots.
         */
        $booking = DB::transaction(function () use (
            $validated,
            $user,
            $durationMinutes,
            $amount
        ) {
            return DoubtSessionBooking::create([
                'booking_reference' =>
                    $this->generateBookingReference(),

                'user_id' => $user->id,
                'student_name' => $user->name,
                'student_email' => $user->email,
                'phone' => $validated['phone'],

                'academic_year_id' =>
                    $validated['academic_year_id'],

                'subject_id' =>
                    $validated['subject_id'],

                'topic' => $validated['topic'],
                'doubt' => $validated['doubt'],

                'duration_minutes' =>
                    $durationMinutes,

                'amount' => $amount,
                'currency' => 'INR',
                'is_free' => false,

                'payment_status' =>
                    DoubtSessionBooking::PAYMENT_PENDING,

                'booking_status' =>
                    DoubtSessionBooking::STATUS_PENDING_PAYMENT,
            ]);
        });

        /*
         * Create the matching Razorpay order.
         */
        try {
            $order = $this->razorpay->createOrder(
                $booking
            );

            $booking->update([
                'razorpay_order_id' => $order['id'],
                'payment_error' => null,
            ]);
        } catch (ServerError $exception) {
            $httpStatus = (int) $exception->getHttpStatusCode();

            Log::error('Razorpay order creation failed.', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'http_status' => $httpStatus,
                'exception' => $exception::class,
                'message' => $exception->getMessage(),
            ]);

            $booking->update([
                'payment_status' =>
                    DoubtSessionBooking::PAYMENT_FAILED,

                'payment_error' => $httpStatus === 406
                    ? 'Razorpay rejected the server/network request with HTTP 406.'
                    : 'Unable to create the Razorpay payment order.',
            ]);

            $publicMessage =
                $httpStatus === 406
                && app()->environment('local')
                    ? 'Razorpay rejected the request from the current network (HTTP 406). Test from an India-based network or staging server.'
                    : 'Unable to start the online payment. Please try again.';

            return redirect()
                ->to(
                    route('doubt-sessions.create')
                    . '#booking-form'
                )
                ->withInput()
                ->with('session_error', $publicMessage);
        } catch (Throwable $exception) {
            report($exception);

            $booking->update([
                'payment_status' =>
                    DoubtSessionBooking::PAYMENT_FAILED,

                'payment_error' =>
                    'Unable to create the Razorpay payment order.',
            ]);

            return redirect()
                ->to(
                    route('doubt-sessions.create')
                    . '#booking-form'
                )
                ->withInput()
                ->with(
                    'session_error',
                    'Unable to start the online payment. Please try again.'
                );
        }

        return redirect()->route(
            'doubt-sessions.payment',
            $booking
        );
    }

    /**
     * Display Razorpay Checkout for a pending paid booking.
     */
    public function payment(
        Request $request,
        DoubtSessionBooking $booking
    ) {
        $this->ensureBookingOwner(
            $request,
            $booking
        );

        if ($booking->is_free) {
            return redirect()->route(
                'doubt-sessions.success',
                $booking
            );
        }

        if ($booking->isPaid()) {
            return redirect()->route(
                'doubt-sessions.success',
                $booking
            );
        }

        if (
            $booking->booking_status
            === DoubtSessionBooking::STATUS_CANCELLED
        ) {
            return redirect()
                ->route('doubt-sessions.create')
                ->with(
                    'session_error',
                    'This payment request is no longer valid. Please create a new booking.'
                );
        }

        if (
            blank($booking->razorpay_order_id)
            || ! $this->razorpay->isConfigured()
        ) {
            return redirect()
                ->route('doubt-sessions.create')
                ->with(
                    'session_error',
                    'The payment request could not be loaded. Please create a new booking.'
                );
        }

        $razorpayKeyId = $this->razorpay->keyId();

        $amountSubunits =
            $this->razorpay->amountToSubunits(
                $booking->amount
            );

        $siteName = get_setting(
            'site_name',
            'Physio Academy'
        );

        $paymentDescription =
            'One-on-One Doubt Session (' .
            $booking->duration_minutes .
            ' Minutes)';

        return view(
            'doubt-sessions.payment',
            compact(
                'booking',
                'razorpayKeyId',
                'amountSubunits',
                'siteName',
                'paymentDescription'
            )
        );
    }

    /**
     * Verify the successful Razorpay payment.
     */
    public function verifyPayment(
        Request $request,
        DoubtSessionBooking $booking
    ) {
        $this->ensureBookingOwner(
            $request,
            $booking
        );

        if ($booking->isPaid()) {
            return redirect()->route(
                'doubt-sessions.success',
                $booking
            );
        }

        abort_if(
            $booking->is_free,
            422
        );

        $validated = $request->validate([
            'razorpay_payment_id' => [
                'required',
                'string',
                'max:100',
            ],
            'razorpay_order_id' => [
                'required',
                'string',
                'max:100',
            ],
            'razorpay_signature' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        /*
         * Check browser-returned order ID against the order
         * previously stored by our backend.
         */
        if (
            blank($booking->razorpay_order_id)
            || ! hash_equals(
                (string) $booking->razorpay_order_id,
                (string) $validated['razorpay_order_id']
            )
        ) {
            $this->markVerificationFailure(
                $booking,
                'The Razorpay order ID did not match the booking.'
            );

            return redirect()
                ->route('doubt-sessions.create')
                ->with(
                    'session_error',
                    'Payment verification failed. Please create a new booking.'
                );
        }

        try {
            /*
             * Signature verification uses the order ID stored
             * on our own server.
             */
            $this->razorpay->verifySignature(
                $booking,
                $validated['razorpay_payment_id'],
                $validated['razorpay_signature']
            );

            /*
             * Fetch payment directly from Razorpay and validate
             * amount, currency and order ownership.
             */
            $payment = $this->razorpay->fetchPayment(
                $validated['razorpay_payment_id']
            );

            $this->razorpay
                ->assertPaymentMatchesBooking(
                    $booking,
                    $payment
                );
        } catch (
        SignatureVerificationError |
        UnexpectedValueException $exception
        ) {
            report($exception);

            $this->markVerificationFailure(
                $booking,
                'Razorpay payment verification failed.'
            );

            return redirect()
                ->route('doubt-sessions.create')
                ->with(
                    'session_error',
                    'Payment verification failed. Please create a new booking.'
                );
        } catch (Throwable $exception) {
            report($exception);

            $booking->update([
                'payment_error' =>
                    'Unable to verify the payment with Razorpay.',
            ]);

            return redirect()
                ->route(
                    'doubt-sessions.payment',
                    $booking
                )
                ->with(
                    'payment_error',
                    'We could not verify the payment at this time. Please try again.'
                );
        }

        /*
         * A verified signature confirms authenticity, but the
         * payment must also be captured.
         */
        if ($payment['status'] !== 'captured') {
            $booking->update([
                'payment_error' =>
                    'Razorpay payment status: ' .
                    $payment['status'],
            ]);

            return redirect()
                ->route(
                    'doubt-sessions.payment',
                    $booking
                )
                ->with(
                    'payment_error',
                    'The payment has not been captured yet. Please wait and try again.'
                );
        }

        try {
            $booking = DB::transaction(function () use (
                $booking,
                $validated
            ) {
                $lockedBooking =
                    DoubtSessionBooking::query()
                        ->lockForUpdate()
                        ->findOrFail($booking->id);

                /*
                 * Makes duplicate success callbacks harmless.
                 */
                if ($lockedBooking->isPaid()) {
                    return $lockedBooking;
                }

                $lockedBooking->update([
                    'razorpay_payment_id' =>
                        $validated['razorpay_payment_id'],

                    'razorpay_signature' =>
                        $validated['razorpay_signature'],

                    'payment_status' =>
                        DoubtSessionBooking::PAYMENT_PAID,

                    'booking_status' =>
                        DoubtSessionBooking::STATUS_PENDING_SCHEDULE,

                    'paid_at' => now(),
                    'payment_error' => null,
                ]);

                return $lockedBooking;
            });
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route(
                    'doubt-sessions.payment',
                    $booking
                )
                ->with(
                    'payment_error',
                    'Payment was received, but the booking could not be updated. Please contact support with your booking reference.'
                );
        }

        return redirect()->route(
            'doubt-sessions.success',
            $booking
        );
    }

    /**
     * Display the free or paid booking success page.
     */
    public function success(
        Request $request,
        DoubtSessionBooking $booking
    ) {
        $this->ensureBookingOwner(
            $request,
            $booking
        );

        /*
         * Prevent pending paid bookings from manually opening
         * the success URL.
         */
        abort_unless(
            $booking->is_free || $booking->isPaid(),
            404
        );

        $booking->load([
            'academicYear',
            'subject',
        ]);

        return view(
            'doubt-sessions.success',
            compact('booking')
        );
    }

    /**
     * Ensure that the booking belongs to the logged-in user.
     */
    private function ensureBookingOwner(
        Request $request,
        DoubtSessionBooking $booking
    ): void {
        abort_unless(
            (int) $booking->user_id
            === (int) $request->user()->id,
            403
        );
    }

    /**
     * Check whether doubt sessions are enabled.
     */
    private function sessionsEnabled(): bool
    {
        return (string) get_setting(
                'doubt_session_enabled',
                '0'
            ) === '1';
    }

    /**
     * Check whether the session is currently free.
     */
    private function sessionIsFree(): bool
    {
        return (string) get_setting(
                'doubt_session_is_free',
                '0'
            ) === '1';
    }

    /**
     * Return the configured doubt session duration.
     */
    private function sessionDuration(): int
    {
        return max(
            (int) get_setting(
                'doubt_session_duration_minutes',
                60
            ),
            15
        );
    }

    /**
     * Generate a unique public booking reference.
     */
    private function generateBookingReference(): string
    {
        do {
            $reference =
                'DS-' .
                now()->format('YmdHis') .
                '-' .
                Str::upper(Str::random(6));
        } while (
            DoubtSessionBooking::where(
                'booking_reference',
                $reference
            )->exists()
        );

        return $reference;
    }

    /**
     * Mark a booking as failed after payment verification failure.
     */
    private function markVerificationFailure(
        DoubtSessionBooking $booking,
        string $message
    ): void {
        $booking->update([
            'payment_status' =>
                DoubtSessionBooking::PAYMENT_FAILED,

            'booking_status' =>
                DoubtSessionBooking::STATUS_CANCELLED,

            'payment_error' => $message,
        ]);
    }
}
