@extends('layouts.admin')

@section('title', 'Doubt Session Bookings')

@section('content')
    @php
        $summaryCards = [
            [
                'label' => 'All Bookings',
                'value' => $counts['all'],
                'icon' => 'bi-collection',
                'class' => 'summary-neutral',
            ],
            [
                'label' => 'Pending Payment',
                'value' => $counts['pending_payment'],
                'icon' => 'bi-credit-card',
                'class' => 'summary-warning',
            ],
            [
                'label' => 'Needs Scheduling',
                'value' => $counts['pending_schedule'],
                'icon' => 'bi-calendar-event',
                'class' => 'summary-primary',
            ],
            [
                'label' => 'Confirmed',
                'value' => $counts['confirmed'],
                'icon' => 'bi-calendar-check',
                'class' => 'summary-info',
            ],
            [
                'label' => 'Completed',
                'value' => $counts['completed'],
                'icon' => 'bi-check-circle',
                'class' => 'summary-success',
            ],
        ];
    @endphp

    <div class="doubt-sessions-admin-page">
        {{-- Page heading --}}
        <div class="page-heading-section">
            <div>
                <h2 class="fw-bold mb-1">
                    One-on-One Doubt Sessions
                </h2>

                <p class="text-secondary mb-0">
                    Review payments, schedule sessions and manage booking statuses.
                </p>
            </div>
        </div>

        {{-- Success message --}}
        @if(session('success'))
            <div class="alert alert-success rounded-3">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
            </div>
        @endif

        {{-- Error messages --}}
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

        {{-- Summary cards --}}
        <div class="booking-summary-grid">
            @foreach($summaryCards as $card)
                <div class="booking-summary-card {{ $card['class'] }}">
                    <div class="summary-card-content">
                        <div>
                            <div class="summary-card-label">
                                {{ $card['label'] }}
                            </div>

                            <div class="summary-card-value">
                                {{ $card['value'] }}
                            </div>
                        </div>

                        <div class="summary-card-icon">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Filters --}}
        <div class="card border-0 shadow-sm rounded-4 booking-filter-card">
            <div class="card-body p-4">
                <form
                    action="{{ route('admin.doubt-session-bookings.index') }}"
                    method="GET"
                >
                    <div class="booking-filter-grid">
                        <div class="filter-field filter-search">
                            <label
                                for="search"
                                class="form-label fw-bold"
                            >
                                Search
                            </label>

                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="bi bi-search text-secondary"></i>
                                </span>

                                <input
                                    type="text"
                                    name="search"
                                    id="search"
                                    class="form-control"
                                    value="{{ request('search') }}"
                                    placeholder="Reference, student, email, phone or topic..."
                                >
                            </div>
                        </div>

                        <div class="filter-field">
                            <label
                                for="sessionType"
                                class="form-label fw-bold"
                            >
                                Session Type
                            </label>

                            <select
                                name="session_type"
                                id="sessionType"
                                class="form-select"
                            >
                                <option value="">All Types</option>

                                <option
                                    value="free"
                                    @selected(request('session_type') === 'free')
                                >
                                    Free
                                </option>

                                <option
                                    value="paid"
                                    @selected(request('session_type') === 'paid')
                                >
                                    Paid
                                </option>
                            </select>
                        </div>

                        <div class="filter-field">
                            <label
                                for="paymentStatus"
                                class="form-label fw-bold"
                            >
                                Payment
                            </label>

                            <select
                                name="payment_status"
                                id="paymentStatus"
                                class="form-select"
                            >
                                <option value="">All Payments</option>

                                <option
                                    value="not_required"
                                    @selected(
                                        request('payment_status') === 'not_required'
                                    )
                                >
                                    Not Required
                                </option>

                                <option
                                    value="pending"
                                    @selected(
                                        request('payment_status') === 'pending'
                                    )
                                >
                                    Pending
                                </option>

                                <option
                                    value="paid"
                                    @selected(
                                        request('payment_status') === 'paid'
                                    )
                                >
                                    Paid
                                </option>

                                <option
                                    value="failed"
                                    @selected(
                                        request('payment_status') === 'failed'
                                    )
                                >
                                    Failed
                                </option>

                                <option
                                    value="refunded"
                                    @selected(
                                        request('payment_status') === 'refunded'
                                    )
                                >
                                    Refunded
                                </option>
                            </select>
                        </div>

                        <div class="filter-field">
                            <label
                                for="bookingStatus"
                                class="form-label fw-bold"
                            >
                                Booking Status
                            </label>

                            <select
                                name="booking_status"
                                id="bookingStatus"
                                class="form-select"
                            >
                                <option value="">All Statuses</option>

                                <option
                                    value="pending_payment"
                                    @selected(
                                        request('booking_status')
                                        === 'pending_payment'
                                    )
                                >
                                    Pending Payment
                                </option>

                                <option
                                    value="pending_schedule"
                                    @selected(
                                        request('booking_status')
                                        === 'pending_schedule'
                                    )
                                >
                                    Pending Schedule
                                </option>

                                <option
                                    value="confirmed"
                                    @selected(
                                        request('booking_status')
                                        === 'confirmed'
                                    )
                                >
                                    Confirmed
                                </option>

                                <option
                                    value="completed"
                                    @selected(
                                        request('booking_status')
                                        === 'completed'
                                    )
                                >
                                    Completed
                                </option>

                                <option
                                    value="cancelled"
                                    @selected(
                                        request('booking_status')
                                        === 'cancelled'
                                    )
                                >
                                    Cancelled
                                </option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                <i class="bi bi-funnel me-1"></i>
                                Filter
                            </button>

                            <a
                                href="{{ route(
                                    'admin.doubt-session-bookings.index'
                                ) }}"
                                class="btn btn-light border"
                            >
                                <i class="bi bi-arrow-counterclockwise me-1"></i>
                                Clear
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Booking list --}}
        <div class="card border-0 shadow-sm rounded-4 booking-list-card">
            <div class="card-header bg-white border-0 booking-list-header">
                <div>
                    <h5 class="fw-bold mb-1">
                        Session Bookings
                    </h5>

                    <div class="small text-secondary">
                        Showing {{ $bookings->firstItem() ?? 0 }}
                        to {{ $bookings->lastItem() ?? 0 }}
                        of {{ $bookings->total() }} bookings
                    </div>
                </div>

                <div class="table-scroll-hint d-none d-lg-flex">
                    <i class="bi bi-arrow-left-right"></i>
                    Scroll table to view all details
                </div>
            </div>

            <div class="card-body p-0">
                {{-- Desktop table --}}
                <div class="booking-table-wrapper d-none d-lg-block">
                    <div class="booking-table-scroll">
                        <table class="table booking-table mb-0">
                            <colgroup>
                                <col class="column-reference">
                                <col class="column-student">
                                <col class="column-session">
                                <col class="column-payment">
                                <col class="column-status">
                                <col class="column-schedule">
                                <col class="column-submitted">
                                <col class="column-action">
                            </colgroup>

                            <thead>
                            <tr>
                                <th>Reference</th>
                                <th>Student</th>
                                <th>Session</th>
                                <th>Payment</th>
                                <th>Booking Status</th>
                                <th>Schedule</th>
                                <th>Submitted</th>
                                <th class="action-column text-end">
                                    Action
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse($bookings as $booking)
                                @php
                                    $paymentBadge = match(
                                        $booking->payment_status
                                    ) {
                                        'paid' => 'success',
                                        'failed' => 'danger',
                                        'refunded' => 'info',
                                        'pending' => 'warning',
                                        default => 'secondary',
                                    };

                                    $bookingBadge = match(
                                        $booking->booking_status
                                    ) {
                                        'confirmed' => 'info',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        'pending_payment' => 'warning',
                                        default => 'primary',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <a
                                            href="{{ route(
                                                    'admin.doubt-session-bookings.show',
                                                    $booking
                                                ) }}"
                                            class="booking-reference-link"
                                        >
                                            {{ $booking->booking_reference }}
                                        </a>

                                        <div class="small text-muted mt-1">
                                            Booking ID: {{ $booking->id }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="booking-student-name">
                                            {{ $booking->student_name }}
                                        </div>

                                        <div class="booking-secondary-text">
                                            <i class="bi bi-envelope me-1"></i>
                                            {{ $booking->student_email }}
                                        </div>

                                        <div class="booking-secondary-text">
                                            <i class="bi bi-telephone me-1"></i>
                                            {{ $booking->phone }}
                                        </div>
                                    </td>

                                    <td>
                                        <div class="booking-primary-text">
                                            {{ $booking->subject?->name ?? 'N/A' }}
                                        </div>

                                        <div class="booking-secondary-text">
                                            {{ $booking->academicYear?->name ?? 'N/A' }}
                                        </div>

                                        <div
                                            class="booking-topic"
                                            title="{{ $booking->topic }}"
                                        >
                                            {{ \Illuminate\Support\Str::limit(
                                                $booking->topic,
                                                60
                                            ) }}
                                        </div>
                                    </td>

                                    <td>
                                        @if($booking->is_free)
                                            <span class="badge bg-success">
                                                    Free
                                                </span>

                                            <div class="booking-secondary-text mt-2">
                                                Not Required
                                            </div>
                                        @else
                                            <div class="booking-price">
                                                ₹{{ number_format(
                                                        (float) $booking->amount,
                                                        2
                                                    ) }}
                                            </div>

                                            <span
                                                class="badge bg-{{ $paymentBadge }}"
                                            >
                                                    {{ ucfirst(
                                                        str_replace(
                                                            '_',
                                                            ' ',
                                                            $booking->payment_status
                                                        )
                                                    ) }}
                                                </span>
                                        @endif
                                    </td>

                                    <td>
                                            <span
                                                class="badge booking-status-badge bg-{{ $bookingBadge }}"
                                            >
                                                {{ ucfirst(
                                                    str_replace(
                                                        '_',
                                                        ' ',
                                                        $booking->booking_status
                                                    )
                                                ) }}
                                            </span>
                                    </td>

                                    <td>
                                        @if($booking->scheduled_at)
                                            <div class="booking-primary-text">
                                                {{ $booking->scheduled_at->format(
                                                    'd M Y'
                                                ) }}
                                            </div>

                                            <div class="booking-secondary-text">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ $booking->scheduled_at->format(
                                                    'h:i A'
                                                ) }}
                                            </div>
                                        @else
                                            <span class="not-scheduled-text">
                                                    <i class="bi bi-calendar-x me-1"></i>
                                                    Not scheduled
                                                </span>
                                        @endif
                                    </td>

                                    <td>
                                        <div class="booking-primary-text">
                                            {{ $booking->created_at->format(
                                                'd M Y'
                                            ) }}
                                        </div>

                                        <div class="booking-secondary-text">
                                            {{ $booking->created_at->format(
                                                'h:i A'
                                            ) }}
                                        </div>
                                    </td>

                                    <td class="action-column text-end">
                                        <a
                                            href="{{ route(
                                                    'admin.doubt-session-bookings.show',
                                                    $booking
                                                ) }}"
                                            class="btn btn-primary btn-sm booking-view-button"
                                        >
                                            <i class="bi bi-eye me-1"></i>
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td
                                        colspan="8"
                                        class="text-center py-5"
                                    >
                                        <div class="empty-bookings">
                                            <div class="empty-bookings-icon">
                                                <i class="bi bi-calendar-x"></i>
                                            </div>

                                            <h6 class="fw-bold">
                                                No bookings found
                                            </h6>

                                            <p class="text-muted mb-0">
                                                Try changing or clearing the filters.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Mobile and tablet booking cards --}}
                <div class="booking-mobile-list d-lg-none">
                    @forelse($bookings as $booking)
                        @php
                            $paymentBadge = match(
                                $booking->payment_status
                            ) {
                                'paid' => 'success',
                                'failed' => 'danger',
                                'refunded' => 'info',
                                'pending' => 'warning',
                                default => 'secondary',
                            };

                            $bookingBadge = match(
                                $booking->booking_status
                            ) {
                                'confirmed' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'pending_payment' => 'warning',
                                default => 'primary',
                            };
                        @endphp

                        <div class="mobile-booking-card">
                            <div class="mobile-booking-header">
                                <div>
                                    <a
                                        href="{{ route(
                                            'admin.doubt-session-bookings.show',
                                            $booking
                                        ) }}"
                                        class="booking-reference-link"
                                    >
                                        {{ $booking->booking_reference }}
                                    </a>

                                    <div class="small text-muted">
                                        ID: {{ $booking->id }}
                                    </div>
                                </div>

                                <span
                                    class="badge bg-{{ $bookingBadge }}"
                                >
                                    {{ ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $booking->booking_status
                                        )
                                    ) }}
                                </span>
                            </div>

                            <div class="mobile-booking-grid">
                                <div class="mobile-detail">
                                    <span>Student</span>

                                    <strong>
                                        {{ $booking->student_name }}
                                    </strong>

                                    <small>
                                        {{ $booking->student_email }}
                                    </small>

                                    <small>
                                        {{ $booking->phone }}
                                    </small>
                                </div>

                                <div class="mobile-detail">
                                    <span>Session</span>

                                    <strong>
                                        {{ $booking->subject?->name ?? 'N/A' }}
                                    </strong>

                                    <small>
                                        {{ $booking->academicYear?->name ?? 'N/A' }}
                                    </small>

                                    <small>
                                        {{ $booking->topic }}
                                    </small>
                                </div>

                                <div class="mobile-detail">
                                    <span>Payment</span>

                                    @if($booking->is_free)
                                        <strong class="text-success">
                                            Free
                                        </strong>

                                        <small>Not Required</small>
                                    @else
                                        <strong>
                                            ₹{{ number_format(
                                                (float) $booking->amount,
                                                2
                                            ) }}
                                        </strong>

                                        <span
                                            class="badge bg-{{ $paymentBadge }} mobile-payment-badge"
                                        >
                                            {{ ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $booking->payment_status
                                                )
                                            ) }}
                                        </span>
                                    @endif
                                </div>

                                <div class="mobile-detail">
                                    <span>Schedule</span>

                                    @if($booking->scheduled_at)
                                        <strong>
                                            {{ $booking->scheduled_at->format(
                                                'd M Y'
                                            ) }}
                                        </strong>

                                        <small>
                                            {{ $booking->scheduled_at->format(
                                                'h:i A'
                                            ) }}
                                        </small>
                                    @else
                                        <strong class="text-muted">
                                            Not scheduled
                                        </strong>
                                    @endif
                                </div>

                                <div class="mobile-detail">
                                    <span>Submitted</span>

                                    <strong>
                                        {{ $booking->created_at->format(
                                            'd M Y'
                                        ) }}
                                    </strong>

                                    <small>
                                        {{ $booking->created_at->format(
                                            'h:i A'
                                        ) }}
                                    </small>
                                </div>
                            </div>

                            <a
                                href="{{ route(
                                    'admin.doubt-session-bookings.show',
                                    $booking
                                ) }}"
                                class="btn btn-primary w-100"
                            >
                                <i class="bi bi-eye me-1"></i>
                                View Booking
                            </a>
                        </div>
                    @empty
                        <div class="empty-bookings py-5">
                            <div class="empty-bookings-icon">
                                <i class="bi bi-calendar-x"></i>
                            </div>

                            <h6 class="fw-bold">
                                No bookings found
                            </h6>

                            <p class="text-muted mb-0">
                                Try changing or clearing the filters.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            @if($bookings->hasPages())
                <div class="card-footer bg-white border-0 p-4">
                    {{ $bookings->links() }}
                </div>
            @endif
        </div>
    </div>

    @push('styles')
        <style>
            /*
             * Prevent the admin flex content area from growing
             * wider than the visible screen.
             */
            #page-content-wrapper {
                flex: 1 1 auto !important;
                width: auto !important;
                min-width: 0 !important;
                max-width: 100% !important;
                overflow-x: hidden !important;
            }

            .doubt-sessions-admin-page {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow-x: hidden;
            }

            .doubt-sessions-admin-page *,
            .doubt-sessions-admin-page *::before,
            .doubt-sessions-admin-page *::after {
                box-sizing: border-box;
            }

            .doubt-sessions-admin-page .card,
            .doubt-sessions-admin-page .card-body,
            .doubt-sessions-admin-page .card-header,
            .doubt-sessions-admin-page .card-footer {
                min-width: 0;
                max-width: 100%;
            }

            .page-heading-section {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 24px;
            }

            /*
             * Summary cards
             */
            .booking-summary-grid {
                display: grid;
                grid-template-columns: repeat(5, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 24px;
            }

            .booking-summary-card {
                min-width: 0;
                padding: 22px;
                border: 1px solid rgba(15, 23, 42, 0.05);
                border-radius: 20px;
                background: #FFFFFF;
                box-shadow: 0 8px 25px rgba(15, 23, 42, 0.06);
            }

            .summary-card-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 14px;
            }

            .summary-card-label {
                margin-bottom: 5px;
                color: #64748B;
                font-size: 0.9rem;
                font-weight: 600;
            }

            .summary-card-value {
                color: #0F172A;
                font-size: 2rem;
                font-weight: 800;
                line-height: 1;
            }

            .summary-card-icon {
                width: 46px;
                height: 46px;
                flex: 0 0 46px;
                display: grid;
                place-items: center;
                border-radius: 14px;
                font-size: 1.15rem;
            }

            .summary-neutral .summary-card-icon {
                background: rgba(15, 23, 42, 0.08);
                color: #0F172A;
            }

            .summary-warning .summary-card-icon {
                background: rgba(245, 158, 11, 0.12);
                color: #D97706;
            }

            .summary-warning .summary-card-value {
                color: #D97706;
            }

            .summary-primary .summary-card-icon {
                background: rgba(0, 74, 173, 0.10);
                color: #004AAD;
            }

            .summary-primary .summary-card-value {
                color: #004AAD;
            }

            .summary-info .summary-card-icon {
                background: rgba(14, 165, 233, 0.12);
                color: #0284C7;
            }

            .summary-info .summary-card-value {
                color: #0284C7;
            }

            .summary-success .summary-card-icon {
                background: rgba(22, 163, 74, 0.11);
                color: #15803D;
            }

            .summary-success .summary-card-value {
                color: #15803D;
            }

            /*
             * Filters
             */
            .booking-filter-card {
                margin-bottom: 24px;
                overflow: hidden;
            }

            .booking-filter-grid {
                display: grid;
                grid-template-columns:
                    minmax(280px, 2fr)
                    repeat(3, minmax(160px, 1fr))
                    minmax(180px, auto);
                gap: 16px;
                align-items: end;
            }

            .filter-field,
            .filter-actions {
                min-width: 0;
            }

            .filter-field .form-control,
            .filter-field .form-select,
            .filter-field .input-group {
                width: 100%;
                min-width: 0;
            }

            .filter-field .form-control,
            .filter-field .form-select,
            .filter-field .input-group-text {
                min-height: 46px;
            }

            .filter-actions {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }

            .filter-actions .btn {
                min-width: 0;
                min-height: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                white-space: nowrap;
            }

            /*
             * Booking table
             */
            .booking-list-card {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow: hidden;
            }

            .booking-list-header {
                min-height: 84px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 20px;
                padding: 22px 24px;
                border-bottom: 1px solid #E8EDF5 !important;
            }

            .table-scroll-hint {
                align-items: center;
                gap: 7px;
                color: #64748B;
                font-size: 0.78rem;
            }

            .booking-table-wrapper {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow: hidden;
            }

            .booking-table-scroll {
                display: block;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                overflow-x: auto;
                overflow-y: visible;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: thin;
                scrollbar-color: #004AAD #E8EDF5;
            }

            .booking-table {
                width: 100%;
                min-width: 1120px;
                table-layout: fixed;
            }

            .booking-table .column-reference {
                width: 185px;
            }

            .booking-table .column-student {
                width: 190px;
            }

            .booking-table .column-session {
                width: 195px;
            }

            .booking-table .column-payment {
                width: 120px;
            }

            .booking-table .column-status {
                width: 145px;
            }

            .booking-table .column-schedule {
                width: 145px;
            }

            .booking-table .column-submitted {
                width: 130px;
            }

            .booking-table .column-action {
                width: 95px;
            }

            .booking-table thead th {
                position: sticky;
                top: 0;
                z-index: 2;
                padding: 16px 14px;
                border-bottom: 1px solid #E8EDF5;
                background: #F8FAFC;
                color: #94A3B8;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                white-space: nowrap;
            }

            .booking-table tbody td {
                padding: 18px 14px;
                border-bottom: 1px solid #EDF1F6;
                background: #FFFFFF;
                vertical-align: middle;
            }

            .booking-table tbody tr:last-child td {
                border-bottom: 0;
            }

            .booking-table tbody tr:hover td {
                background: #FAFCFF;
            }

            .booking-table td {
                overflow-wrap: anywhere;
            }

            .booking-reference-link {
                display: inline-block;
                color: #004AAD;
                font-weight: 800;
                line-height: 1.35;
                text-decoration: none;
                overflow-wrap: anywhere;
            }

            .booking-reference-link:hover {
                color: #003B8A;
                text-decoration: underline;
            }

            .booking-student-name,
            .booking-primary-text {
                color: #1E293B;
                font-size: 0.88rem;
                font-weight: 750;
            }

            .booking-secondary-text {
                margin-top: 3px;
                color: #64748B;
                font-size: 0.76rem;
                line-height: 1.4;
                overflow-wrap: anywhere;
            }

            .booking-topic {
                margin-top: 4px;
                color: #64748B;
                font-size: 0.76rem;
                line-height: 1.4;
            }

            .booking-price {
                margin-bottom: 5px;
                color: #004AAD;
                font-weight: 800;
            }

            .booking-status-badge {
                white-space: normal;
                line-height: 1.3;
                text-align: center;
            }

            .not-scheduled-text {
                color: #64748B;
                font-size: 0.8rem;
                white-space: nowrap;
            }

            /*
             * Keep Action visible while scrolling the table.
             */
            .booking-table .action-column {
                position: sticky;
                right: 0;
                z-index: 3;
                border-left: 1px solid #EDF1F6;
                background: #FFFFFF;
                box-shadow: -8px 0 18px rgba(15, 23, 42, 0.04);
            }

            .booking-table thead .action-column {
                z-index: 4;
                background: #F8FAFC;
            }

            .booking-table tbody tr:hover .action-column {
                background: #FAFCFF;
            }

            .booking-view-button {
                min-width: 72px;
                white-space: nowrap;
            }

            .booking-table-scroll::-webkit-scrollbar {
                height: 9px;
            }

            .booking-table-scroll::-webkit-scrollbar-track {
                border-radius: 999px;
                background: #E8EDF5;
            }

            .booking-table-scroll::-webkit-scrollbar-thumb {
                border-radius: 999px;
                background: #004AAD;
            }

            .booking-table-scroll::-webkit-scrollbar-thumb:hover {
                background: #003B8A;
            }

            /*
             * Empty state
             */
            .empty-bookings {
                text-align: center;
            }

            .empty-bookings-icon {
                width: 54px;
                height: 54px;
                display: grid;
                place-items: center;
                margin: 0 auto 12px;
                border-radius: 16px;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                font-size: 1.35rem;
            }

            /*
             * Tablet/mobile card list
             */
            .booking-mobile-list {
                padding: 16px;
            }

            .mobile-booking-card {
                margin-bottom: 16px;
                padding: 18px;
                border: 1px solid #E4EAF2;
                border-radius: 18px;
                background: #FFFFFF;
            }

            .mobile-booking-card:last-child {
                margin-bottom: 0;
            }

            .mobile-booking-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 14px;
                margin-bottom: 18px;
                padding-bottom: 14px;
                border-bottom: 1px solid #EDF1F6;
            }

            .mobile-booking-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
                margin-bottom: 18px;
            }

            .mobile-detail {
                min-width: 0;
            }

            .mobile-detail > span:first-child {
                display: block;
                margin-bottom: 4px;
                color: #94A3B8;
                font-size: 0.68rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .mobile-detail strong,
            .mobile-detail small {
                display: block;
                overflow-wrap: anywhere;
            }

            .mobile-detail strong {
                color: #1E293B;
                font-size: 0.86rem;
            }

            .mobile-detail small {
                margin-top: 3px;
                color: #64748B;
                line-height: 1.4;
            }

            .mobile-payment-badge {
                display: inline-block !important;
                width: fit-content;
                margin-top: 5px;
            }

            @media (max-width: 1500px) {
                .booking-summary-grid {
                    grid-template-columns: repeat(3, minmax(0, 1fr));
                }

                .booking-filter-grid {
                    grid-template-columns:
                        minmax(260px, 2fr)
                        repeat(2, minmax(170px, 1fr));
                }

                .filter-actions {
                    grid-column: auto;
                }
            }

            @media (max-width: 1199.98px) {
                .booking-summary-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .booking-filter-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }

                .filter-search {
                    grid-column: 1 / -1;
                }
            }

            @media (max-width: 767.98px) {
                .page-heading-section h2 {
                    font-size: 1.6rem;
                }

                .booking-summary-grid {
                    gap: 12px;
                }

                .booking-summary-card {
                    padding: 18px;
                }

                .summary-card-value {
                    font-size: 1.65rem;
                }

                .booking-filter-card .card-body {
                    padding: 18px !important;
                }

                .booking-filter-grid {
                    grid-template-columns: 1fr;
                }

                .filter-search {
                    grid-column: auto;
                }

                .booking-list-header {
                    align-items: flex-start;
                    padding: 18px;
                }

                .mobile-booking-grid {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 575.98px) {
                .booking-summary-grid {
                    grid-template-columns: 1fr;
                }

                .filter-actions {
                    grid-template-columns: 1fr;
                }

                .mobile-booking-header {
                    flex-direction: column;
                }
            }
        </style>
    @endpush
@endsection
