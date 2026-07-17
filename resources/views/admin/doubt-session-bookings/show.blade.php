@extends('layouts.admin')

@section('title', 'Doubt Session Booking')

@section('content')
    @php
        $paymentBadge = match($booking->payment_status) {
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            'pending' => 'warning',
            default => 'secondary',
        };

        $bookingBadge = match($booking->booking_status) {
            'confirmed' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            'pending_payment' => 'warning',
            default => 'primary',
        };

        $isUnpaidPaidBooking =
            $booking->requiresPayment()
            && ! $booking->isPaid();

        $availableStatuses = $isUnpaidPaidBooking
            ? [
                'pending_payment' => 'Pending Payment',
                'cancelled' => 'Cancelled',
            ]
            : [
                'pending_schedule' => 'Pending Schedule',
                'confirmed' => 'Confirmed',
                'completed' => 'Completed',
                'cancelled' => 'Cancelled',
            ];
    @endphp

    <div class="mb-4">
        <a
            href="{{ route('admin.doubt-session-bookings.index') }}"
            class="text-decoration-none small text-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back to Doubt Sessions
        </a>

        <div
            class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mt-2"
        >
            <div>
                <h2 class="fw-bold text-dark mb-1">
                    {{ $booking->booking_reference }}
                </h2>

                <p class="text-secondary mb-0">
                    Submitted on
                    {{ $booking->created_at->format(
                        'd M Y, h:i A'
                    ) }}
                </p>
            </div>

            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-{{ $paymentBadge }} fs-6">
                    Payment:
                    {{ ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $booking->payment_status
                        )
                    ) }}
                </span>

                <span class="badge bg-{{ $bookingBadge }} fs-6">
                    Booking:
                    {{ ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $booking->booking_status
                        )
                    ) }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success rounded-3">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger rounded-3">
            <div class="fw-bold mb-1">
                Please fix the following errors:
            </div>

            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($isUnpaidPaidBooking)
        <div class="alert alert-warning rounded-3">
            <i class="bi bi-exclamation-triangle me-2"></i>

            This is a paid booking, but its Razorpay payment has
            not been verified. It cannot be confirmed or completed.
        </div>
    @endif

    <div class="row g-4">
        {{-- Booking information --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0">
                        Student Information
                    </h5>
                </div>

                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Student Name
                            </div>

                            <div class="fw-bold">
                                {{ $booking->student_name }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                User Account
                            </div>

                            <div class="fw-bold">
                                {{ $booking->user?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Email Address
                            </div>

                            <a
                                href="mailto:{{ $booking->student_email }}"
                                class="text-decoration-none"
                            >
                                {{ $booking->student_email }}
                            </a>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Phone Number
                            </div>

                            <a
                                href="tel:{{ $booking->phone }}"
                                class="text-decoration-none"
                            >
                                {{ $booking->phone }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0">
                        Session Details
                    </h5>
                </div>

                <div class="card-body p-4 pt-0">
                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Academic Year
                            </div>

                            <div class="fw-bold">
                                {{ $booking->academicYear?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Subject
                            </div>

                            <div class="fw-bold">
                                {{ $booking->subject?->name ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Topic
                            </div>

                            <div class="fw-bold">
                                {{ $booking->topic }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Duration
                            </div>

                            <div class="fw-bold">
                                {{ $booking->duration_minutes }}
                                Minutes
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="small text-secondary mb-2">
                            Student's Doubt
                        </div>

                        <div
                            class="p-3 bg-light border rounded-3"
                            style="white-space: pre-wrap;"
                        >{{ $booking->doubt }}</div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0">
                        Payment Information
                    </h5>
                </div>

                <div class="card-body p-4 pt-0">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <div class="small text-secondary mb-1">
                                Session Type
                            </div>

                            <div class="fw-bold">
                                {{ $booking->is_free ? 'Free' : 'Paid' }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small text-secondary mb-1">
                                Amount
                            </div>

                            <div class="fw-bold">
                                ₹{{ number_format(
                                    (float) $booking->amount,
                                    2
                                ) }}
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="small text-secondary mb-1">
                                Payment Status
                            </div>

                            <span class="badge bg-{{ $paymentBadge }}">
                                {{ ucfirst(
                                    str_replace(
                                        '_',
                                        ' ',
                                        $booking->payment_status
                                    )
                                ) }}
                            </span>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Razorpay Order ID
                            </div>

                            <div class="fw-semibold text-break">
                                {{ $booking->razorpay_order_id ?: 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Razorpay Payment ID
                            </div>

                            <div class="fw-semibold text-break">
                                {{ $booking->razorpay_payment_id ?: 'N/A' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Paid At
                            </div>

                            <div class="fw-semibold">
                                {{ $booking->paid_at
                                    ? $booking->paid_at->format(
                                        'd M Y, h:i A'
                                    )
                                    : 'N/A'
                                }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="small text-secondary mb-1">
                                Currency
                            </div>

                            <div class="fw-semibold">
                                {{ $booking->currency }}
                            </div>
                        </div>

                        @if($booking->payment_error)
                            <div class="col-12">
                                <div class="small text-secondary mb-1">
                                    Payment Error
                                </div>

                                <div class="alert alert-danger mb-0">
                                    {{ $booking->payment_error }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin management form --}}
        <div class="col-lg-4">
            <form
                action="{{ route(
                    'admin.doubt-session-bookings.update',
                    $booking
                ) }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-0">
                        <h5 class="fw-bold mb-0">
                            Schedule & Status
                        </h5>
                    </div>

                    <div class="card-body p-4 pt-0">
                        <div class="mb-3">
                            <label
                                for="bookingStatus"
                                class="form-label fw-bold"
                            >
                                Booking Status
                            </label>

                            <select
                                name="booking_status"
                                id="bookingStatus"
                                class="form-select @error('booking_status') is-invalid @enderror"
                                required
                            >
                                @foreach($availableStatuses as $value => $label)
                                    <option
                                        value="{{ $value }}"
                                        @selected(
                                            old(
                                                'booking_status',
                                                $booking->booking_status
                                            ) === $value
                                        )
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            @error('booking_status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label
                                for="scheduledAt"
                                class="form-label fw-bold"
                            >
                                Session Date & Time
                            </label>

                            <input
                                type="datetime-local"
                                name="scheduled_at"
                                id="scheduledAt"
                                class="form-control @error('scheduled_at') is-invalid @enderror"
                                value="{{ old(
                                    'scheduled_at',
                                    $booking->scheduled_at?->format(
                                        'Y-m-d\TH:i'
                                    )
                                ) }}"
                            >

                            <div class="form-text">
                                Required when booking is confirmed or completed.
                            </div>

                            @error('scheduled_at')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label
                                for="meetingLink"
                                class="form-label fw-bold"
                            >
                                Meeting Link
                            </label>

                            <input
                                type="url"
                                name="meeting_link"
                                id="meetingLink"
                                class="form-control @error('meeting_link') is-invalid @enderror"
                                value="{{ old(
                                    'meeting_link',
                                    $booking->meeting_link
                                ) }}"
                                placeholder="https://meet.google.com/..."
                            >

                            <div class="form-text">
                                Required when booking is confirmed or completed.
                            </div>

                            @error('meeting_link')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label
                                for="adminNotes"
                                class="form-label fw-bold"
                            >
                                Admin Notes
                            </label>

                            <textarea
                                name="admin_notes"
                                id="adminNotes"
                                class="form-control @error('admin_notes') is-invalid @enderror"
                                rows="6"
                                maxlength="5000"
                                placeholder="Internal notes about this booking..."
                            >{{ old(
                                'admin_notes',
                                $booking->admin_notes
                            ) }}</textarea>

                            @error('admin_notes')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        @if($booking->confirmedBy)
                            <div class="alert alert-light border mb-0">
                                <div class="small text-secondary">
                                    Confirmed By
                                </div>

                                <div class="fw-bold">
                                    {{ $booking->confirmedBy->name }}
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-light p-3 d-grid">
                        <button
                            type="submit"
                            class="btn btn-primary btn-lg"
                            @disabled(
                                $isUnpaidPaidBooking
                                && ! in_array(
                                    old(
                                        'booking_status',
                                        $booking->booking_status
                                    ),
                                    [
                                        'pending_payment',
                                        'cancelled',
                                    ],
                                    true
                                )
                            )
                        >
                            <i class="bi bi-check-lg me-1"></i>
                            Save Booking
                        </button>
                    </div>
                </div>
            </form>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <h6 class="fw-bold">
                        Current Schedule
                    </h6>

                    @if($booking->scheduled_at)
                        <p class="mb-2">
                            <i class="bi bi-calendar-check me-2 text-primary"></i>

                            {{ $booking->scheduled_at->format(
                                'd M Y, h:i A'
                            ) }}
                        </p>
                    @else
                        <p class="text-muted mb-2">
                            No session date has been scheduled.
                        </p>
                    @endif

                    @if($booking->meeting_link)
                        <a
                            href="{{ $booking->meeting_link }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="btn btn-outline-primary w-100"
                        >
                            <i class="bi bi-camera-video me-1"></i>
                            Open Meeting Link
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
