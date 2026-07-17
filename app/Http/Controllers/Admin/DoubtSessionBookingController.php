<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DoubtSessionBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DoubtSessionBookingController extends Controller
{
    /**
     * Display all one-on-one session bookings.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'payment_status' => [
                'nullable',
                Rule::in([
                    DoubtSessionBooking::PAYMENT_NOT_REQUIRED,
                    DoubtSessionBooking::PAYMENT_PENDING,
                    DoubtSessionBooking::PAYMENT_PAID,
                    DoubtSessionBooking::PAYMENT_FAILED,
                    DoubtSessionBooking::PAYMENT_REFUNDED,
                ]),
            ],

            'booking_status' => [
                'nullable',
                Rule::in([
                    DoubtSessionBooking::STATUS_PENDING_PAYMENT,
                    DoubtSessionBooking::STATUS_PENDING_SCHEDULE,
                    DoubtSessionBooking::STATUS_CONFIRMED,
                    DoubtSessionBooking::STATUS_COMPLETED,
                    DoubtSessionBooking::STATUS_CANCELLED,
                ]),
            ],

            'session_type' => [
                'nullable',
                Rule::in([
                    'free',
                    'paid',
                ]),
            ],
        ]);

        $search = trim(
            (string) ($validated['search'] ?? '')
        );

        $paymentStatus =
            $validated['payment_status'] ?? null;

        $bookingStatus =
            $validated['booking_status'] ?? null;

        $sessionType =
            $validated['session_type'] ?? null;

        $bookings = DoubtSessionBooking::query()
            ->with([
                'user',
                'academicYear',
                'subject',
                'confirmedBy',
            ])

            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query
                            ->where(
                                'booking_reference',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'student_name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'student_email',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'phone',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'topic',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'razorpay_order_id',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'razorpay_payment_id',
                                'like',
                                "%{$search}%"
                            );
                    });
                }
            )

            ->when(
                $paymentStatus,
                fn ($query) =>
                $query->where(
                    'payment_status',
                    $paymentStatus
                )
            )

            ->when(
                $bookingStatus,
                fn ($query) =>
                $query->where(
                    'booking_status',
                    $bookingStatus
                )
            )

            ->when(
                $sessionType === 'free',
                fn ($query) =>
                $query->where('is_free', true)
            )

            ->when(
                $sessionType === 'paid',
                fn ($query) =>
                $query->where('is_free', false)
            )

            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'all' => DoubtSessionBooking::count(),

            'pending_payment' =>
                DoubtSessionBooking::where(
                    'booking_status',
                    DoubtSessionBooking::STATUS_PENDING_PAYMENT
                )->count(),

            'pending_schedule' =>
                DoubtSessionBooking::where(
                    'booking_status',
                    DoubtSessionBooking::STATUS_PENDING_SCHEDULE
                )->count(),

            'confirmed' =>
                DoubtSessionBooking::where(
                    'booking_status',
                    DoubtSessionBooking::STATUS_CONFIRMED
                )->count(),

            'completed' =>
                DoubtSessionBooking::where(
                    'booking_status',
                    DoubtSessionBooking::STATUS_COMPLETED
                )->count(),
        ];

        return view(
            'admin.doubt-session-bookings.index',
            compact(
                'bookings',
                'counts'
            )
        );
    }

    /**
     * Display an individual booking.
     */
    public function show(
        DoubtSessionBooking $doubtSessionBooking
    ) {
        $doubtSessionBooking->load([
            'user',
            'academicYear',
            'subject',
            'confirmedBy',
        ]);

        return view(
            'admin.doubt-session-bookings.show',
            [
                'booking' => $doubtSessionBooking,
            ]
        );
    }

    /**
     * Update schedule, meeting details and status.
     */
    public function update(
        Request $request,
        DoubtSessionBooking $doubtSessionBooking
    ) {
        $validated = $request->validate([
            'booking_status' => [
                'required',
                Rule::in([
                    DoubtSessionBooking::STATUS_PENDING_PAYMENT,
                    DoubtSessionBooking::STATUS_PENDING_SCHEDULE,
                    DoubtSessionBooking::STATUS_CONFIRMED,
                    DoubtSessionBooking::STATUS_COMPLETED,
                    DoubtSessionBooking::STATUS_CANCELLED,
                ]),
            ],

            'scheduled_at' => [
                'nullable',
                'date',
            ],

            'meeting_link' => [
                'nullable',
                'url',
                'max:2048',
            ],

            'admin_notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ], [
            'meeting_link.url' =>
                'Please enter a valid meeting URL.',

            'scheduled_at.date' =>
                'Please enter a valid session date and time.',
        ]);

        $newStatus = $validated['booking_status'];

        /*
         * Paid bookings cannot be confirmed or completed
         * until Razorpay payment has been verified.
         */
        if (
            $doubtSessionBooking->requiresPayment()
            && ! $doubtSessionBooking->isPaid()
            && ! in_array(
                $newStatus,
                [
                    DoubtSessionBooking::STATUS_PENDING_PAYMENT,
                    DoubtSessionBooking::STATUS_CANCELLED,
                ],
                true
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'booking_status' =>
                        'This paid booking cannot be scheduled or confirmed until payment has been verified.',
                ]);
        }

        /*
         * Free bookings and verified paid bookings
         * should not return to pending payment.
         */
        if (
            (
                $doubtSessionBooking->is_free
                || $doubtSessionBooking->isPaid()
            )
            && $newStatus
            === DoubtSessionBooking::STATUS_PENDING_PAYMENT
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'booking_status' =>
                        'This booking does not require a pending-payment status.',
                ]);
        }

        /*
         * Confirmed and completed sessions require
         * both schedule and meeting information.
         */
        if (
            in_array(
                $newStatus,
                [
                    DoubtSessionBooking::STATUS_CONFIRMED,
                    DoubtSessionBooking::STATUS_COMPLETED,
                ],
                true
            )
            && blank($validated['scheduled_at'] ?? null)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'scheduled_at' =>
                        'Session date and time are required when confirming or completing a booking.',
                ]);
        }

        if (
            in_array(
                $newStatus,
                [
                    DoubtSessionBooking::STATUS_CONFIRMED,
                    DoubtSessionBooking::STATUS_COMPLETED,
                ],
                true
            )
            && blank($validated['meeting_link'] ?? null)
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'meeting_link' =>
                        'Meeting link is required when confirming or completing a booking.',
                ]);
        }

        $data = [
            'booking_status' => $newStatus,

            'scheduled_at' =>
                filled($validated['scheduled_at'] ?? null)
                    ? $validated['scheduled_at']
                    : null,

            'meeting_link' =>
                filled($validated['meeting_link'] ?? null)
                    ? trim($validated['meeting_link'])
                    : null,

            'admin_notes' =>
                filled($validated['admin_notes'] ?? null)
                    ? trim($validated['admin_notes'])
                    : null,
        ];

        /*
         * Store the admin who confirmed or completed
         * the booking.
         */
        if (
            in_array(
                $newStatus,
                [
                    DoubtSessionBooking::STATUS_CONFIRMED,
                    DoubtSessionBooking::STATUS_COMPLETED,
                ],
                true
            )
        ) {
            $data['confirmed_by'] = auth()->id();
        }

        /*
         * Returning to pending schedule means the
         * booking is no longer confirmed.
         */
        if (
            $newStatus
            === DoubtSessionBooking::STATUS_PENDING_SCHEDULE
        ) {
            $data['confirmed_by'] = null;
        }

        $doubtSessionBooking->update($data);

        return redirect()
            ->route(
                'admin.doubt-session-bookings.show',
                $doubtSessionBooking
            )
            ->with(
                'success',
                'Doubt session booking updated successfully.'
            );
    }
}
