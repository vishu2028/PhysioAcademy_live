<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RazorpayService;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\DoubtSessionBookingResource;
use App\Models\DoubtSessionBooking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DoubtSessionController extends Controller
{
    public function __construct(
        private readonly RazorpayService $razorpay
    ) {
    }

    /**
     * Return the current one-on-one doubt-session settings.
     */
    public function index(
        Request $request
    ): AnonymousResourceCollection {
        $validated = $request->validate([
            'booking_status' => [
                'nullable',
                'string',
                Rule::in([
                    DoubtSessionBooking::STATUS_PENDING_PAYMENT,
                    DoubtSessionBooking::STATUS_PENDING_SCHEDULE,
                    DoubtSessionBooking::STATUS_CONFIRMED,
                    DoubtSessionBooking::STATUS_COMPLETED,
                    DoubtSessionBooking::STATUS_CANCELLED,
                ]),
            ],

            'payment_status' => [
                'nullable',
                'string',
                Rule::in([
                    DoubtSessionBooking::PAYMENT_NOT_REQUIRED,
                    DoubtSessionBooking::PAYMENT_PENDING,
                    DoubtSessionBooking::PAYMENT_PAID,
                    DoubtSessionBooking::PAYMENT_FAILED,
                    DoubtSessionBooking::PAYMENT_REFUNDED,
                ]),
            ],

            'q' => [
                'nullable',
                'string',
                'max:255',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
        ]);

        $bookingStatus = $validated['booking_status'] ?? null;
        $paymentStatus = $validated['payment_status'] ?? null;
        $search = trim($validated['q'] ?? '');
        $perPage = (int) ($validated['per_page'] ?? 15);

        /*
         * Sirf logged-in user ki bookings.
         */
        $bookings = $request->user()
            ->doubtSessionBookings()

            ->with([
                'academicYear:id,name,slug',
                'subject:id,name,slug',
            ])

            /*
             * Optional booking-status filter.
             */
            ->when(
                $bookingStatus,
                function ($query) use ($bookingStatus) {
                    $query->where(
                        'booking_status',
                        $bookingStatus
                    );
                }
            )

            /*
             * Optional payment-status filter.
             */
            ->when(
                $paymentStatus,
                function ($query) use ($paymentStatus) {
                    $query->where(
                        'payment_status',
                        $paymentStatus
                    );
                }
            )

            /*
             * Reference, topic, doubt aur subject mein search.
             */
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery
                        ->where(
                            'booking_reference',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'topic',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhere(
                            'doubt',
                            'like',
                            "%{$search}%"
                        )
                        ->orWhereHas(
                            'subject',
                            function ($subjectQuery) use ($search) {
                                $subjectQuery->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                );
                            }
                        );
                });
            })

            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();

        return DoubtSessionBookingResource::collection(
            $bookings
        );
    }
    /**
     * Create a new one-on-one doubt-session booking.
     */
    /**
     * Return one doubt-session booking belonging
     * to the authenticated user.
     */
    public function show(
        Request $request,
        int $id
    ): DoubtSessionBookingResource {
        $booking = $request->user()
            ->doubtSessionBookings()
            ->with([
                'academicYear:id,name,slug',
                'subject:id,name,slug',
            ])
            ->whereKey($id)
            ->firstOrFail();

        return new DoubtSessionBookingResource($booking);
    }
    public function store(Request $request): JsonResponse
    {
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
                Rule::exists('academic_years', 'id')
                    ->where('status', true),
            ],

            'subject_id' => [
                'required',
                'integer',
                Rule::exists('subjects', 'id')
                    ->where('status', true),
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
         * Check whether admin has enabled sessions.
         */
        if (! $this->sessionsEnabled()) {
            return response()->json([
                'message' =>
                    'One-on-one doubt sessions are currently unavailable.',
            ], 422);
        }

        $isFree = $this->sessionIsFree();
        $durationMinutes = $this->sessionDuration();

        /*
         * Amount request se nahi, admin setting se aayegi.
         */
        $amount = $isFree
            ? 0
            : $this->sessionAmount();

        if (! $isFree && $amount <= 0) {
            return response()->json([
                'message' =>
                    'The paid session price has not been configured.',
            ], 422);
        }

        if (
            ! $isFree
            && ! $this->razorpay->isConfigured()
        ) {
            return response()->json([
                'message' =>
                    'The online payment gateway is not configured.',
            ], 503);
        }

        $user = $request->user();

        /*
         * First create booking in local database.
         */
        $booking = DB::transaction(function () use (
            $validated,
            $user,
            $isFree,
            $durationMinutes,
            $amount
        ) {
            return DoubtSessionBooking::query()->create([
                'booking_reference' =>
                    $this->generateBookingReference(),

                'user_id' => $user->id,
                'student_name' => $user->name,
                'student_email' => $user->email,
                'phone' => trim($validated['phone']),

                'academic_year_id' =>
                    $validated['academic_year_id'],

                'subject_id' =>
                    $validated['subject_id'],

                'topic' => trim($validated['topic']),
                'doubt' => trim($validated['doubt']),

                'duration_minutes' => $durationMinutes,
                'amount' => $amount,
                'currency' => 'INR',
                'is_free' => $isFree,

                'payment_status' => $isFree
                    ? DoubtSessionBooking::PAYMENT_NOT_REQUIRED
                    : DoubtSessionBooking::PAYMENT_PENDING,

                'booking_status' => $isFree
                    ? DoubtSessionBooking::STATUS_PENDING_SCHEDULE
                    : DoubtSessionBooking::STATUS_PENDING_PAYMENT,
            ]);
        });

        /*
         * Free session mein Razorpay order nahi banega.
         */
        if ($isFree) {
            $booking->load([
                'academicYear:id,name,slug',
                'subject:id,name,slug',
            ]);

            return response()->json([
                'message' =>
                    'Doubt session booking created successfully.',

                'checkout_required' => false,

                'data' => (
                new DoubtSessionBookingResource($booking)
                )->resolve($request),
            ], 201);
        }

        /*
         * Paid session ke liye Razorpay order create karo.
         */
        try {
            $order = $this->razorpay->createOrder($booking);

            $booking->update([
                'razorpay_order_id' => $order['id'],
                'payment_error' => null,
            ]);
        } catch (Throwable $exception) {
            report($exception);

            /*
             * Existing web flow ki tarah booking database
             * mein rahegi, lekin payment failed mark hogi.
             */
            $booking->update([
                'payment_status' =>
                    DoubtSessionBooking::PAYMENT_FAILED,

                'payment_error' =>
                    'Unable to create the Razorpay payment order.',
            ]);

            return response()->json([
                'message' =>
                    'Unable to start the online payment. Please try again.',

                'data' => [
                    'booking_id' => $booking->id,

                    'booking_reference' =>
                        $booking->booking_reference,
                ],
            ], 502);
        }

        $booking->refresh()->load([
            'academicYear:id,name,slug',
            'subject:id,name,slug',
        ]);

        return response()->json([
            'message' =>
                'Doubt session booking created. Complete the payment.',

            'checkout_required' => true,

            'data' => (
            new DoubtSessionBookingResource($booking)
            )->resolve($request),

            /*
             * Mobile app Razorpay Checkout open karne
             * ke liye yeh data use karegi.
             */
            'checkout' => [
                'gateway' => 'razorpay',
                'key_id' => $this->razorpay->keyId(),
                'order_id' => $order['id'],
                'amount_subunits' => $order['amount'],
                'currency' => $order['currency'],

                'name' => (string) get_setting(
                    'site_name',
                    'Physio Academy'
                ),

                'description' =>
                    'One-on-One Doubt Session (' .
                    $durationMinutes .
                    ' Minutes)',

                'prefill' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => trim($validated['phone']),
                ],
            ],
        ], 201);
    }
    public function config(): JsonResponse
    {
        $enabled = (string) get_setting(
                'doubt_session_enabled',
                '0'
            ) === '1';

        $isFree = (string) get_setting(
                'doubt_session_is_free',
                '0'
            ) === '1';

        $durationMinutes = max(
            (int) get_setting(
                'doubt_session_duration_minutes',
                60
            ),
            15
        );

        $amount = $isFree
            ? 0
            : max(
                (float) get_setting(
                    'doubt_session_price',
                    0
                ),
                0
            );

        $currency = 'INR';

        $razorpayConfigured = $this->razorpay
            ->isConfigured();

        $paymentRequired = (
            $enabled
            && ! $isFree
            && $amount > 0
        );

        /*
         * Free session can be booked without Razorpay.
         * Paid session requires a valid amount and
         * configured Razorpay credentials.
         */
        $canBook = $enabled && (
                $isFree
                || (
                    $amount > 0
                    && $razorpayConfigured
                )
            );

        $razorpayKeyId = null;

        /*
         * Razorpay Key ID public hoti hai aur mobile
         * Checkout mein use hoti hai.
         *
         * Key Secret kabhi response mein nahi jayegi.
         */
        if (
            $paymentRequired
            && $razorpayConfigured
        ) {
            $razorpayKeyId = $this->razorpay->keyId();
        }

        return response()->json([
            'data' => [
                'enabled' => $enabled,
                'can_book' => $canBook,
                'is_free' => $isFree,
                'payment_required' => $paymentRequired,

                'duration_minutes' => $durationMinutes,

                'price' => [
                    'amount' => number_format(
                        $amount,
                        2,
                        '.',
                        ''
                    ),
                    'amount_subunits' => (int) round(
                        $amount * 100
                    ),
                    'currency' => $currency,
                ],

                'payment_gateway' => [
                    'name' => $paymentRequired
                        ? 'razorpay'
                        : null,

                    'configured' => $razorpayConfigured,

                    'key_id' => $razorpayKeyId,
                ],
            ],

            'message' => $this->configurationMessage(
                $enabled,
                $isFree,
                $amount,
                $razorpayConfigured
            ),
        ]);
    }

    /**
     * Return a mobile-friendly configuration message.
     */
    private function configurationMessage(
        bool $enabled,
        bool $isFree,
        float $amount,
        bool $razorpayConfigured
    ): string {
        if (! $enabled) {
            return 'One-on-one doubt sessions are currently unavailable.';
        }

        if ($isFree) {
            return 'One-on-one doubt sessions are currently free.';
        }

        if ($amount <= 0) {
            return 'The paid session price has not been configured.';
        }

        if (! $razorpayConfigured) {
            return 'The online payment gateway is not configured.';
        }

        return 'One-on-one doubt session booking is available.';
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
     * Check whether the current session is free.
     */
    private function sessionIsFree(): bool
    {
        return (string) get_setting(
                'doubt_session_is_free',
                '0'
            ) === '1';
    }

    /**
     * Get the configured session duration.
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
     * Get the configured paid-session amount.
     */
    private function sessionAmount(): float
    {
        return max(
            (float) get_setting(
                'doubt_session_price',
                0
            ),
            0
        );
    }

    /**
     * Generate a unique booking reference.
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
            DoubtSessionBooking::query()
                ->where('booking_reference', $reference)
                ->exists()
        );

        return $reference;
    }
}
