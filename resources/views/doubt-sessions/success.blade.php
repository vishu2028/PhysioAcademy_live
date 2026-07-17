@extends('layouts.frontend')

@section('title', 'Booking Submitted')

@section('content')
    <main class="booking-success-page">
        <div class="booking-success-container">
            <div class="booking-success-card">
                <div class="booking-success-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <span class="booking-success-label">
                Booking Submitted
            </span>

                <h1>Your Doubt Session Request Has Been Received</h1>

                <p class="booking-success-intro">
                    @if($booking->is_free)
                        Your free one-on-one doubt session booking has been
                        submitted successfully.
                    @else
                        Your Razorpay payment has been verified and your
                        one-on-one doubt session booking has been submitted
                        successfully.
                    @endif

                    The admin will confirm the session date and time
                    with you afterward.
                </p>

                <div class="booking-reference-box">
                    <span>Booking Reference</span>

                    <strong>
                        {{ $booking->booking_reference }}
                    </strong>
                </div>

                <div class="booking-success-details">
                    <div class="success-detail">
                        <span>Student</span>
                        <strong>{{ $booking->student_name }}</strong>
                    </div>

                    <div class="success-detail">
                        <span>Academic Year</span>
                        <strong>
                            {{ $booking->academicYear->name ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="success-detail">
                        <span>Subject</span>
                        <strong>
                            {{ $booking->subject->name ?? 'N/A' }}
                        </strong>
                    </div>

                    <div class="success-detail">
                        <span>Topic</span>
                        <strong>{{ $booking->topic }}</strong>
                    </div>

                    <div class="success-detail">
                        <span>Duration</span>
                        <strong>
                            {{ $booking->duration_minutes }} Minutes
                        </strong>
                    </div>

                    <div class="success-detail">
                        <span>Payment</span>

                        <strong class="success-free">
                            @if($booking->is_free)
                                Not Required
                            @else
                                Paid — ₹{{ number_format($booking->amount, 2) }}
                            @endif
                        </strong>
                    </div>

                    <div class="success-detail">
                        <span>Booking Status</span>
                        <strong>
                            Pending Schedule
                        </strong>
                    </div>

                    <div class="success-detail">
                        <span>Submitted</span>
                        <strong>
                            {{ $booking->created_at->format('d M Y, h:i A') }}
                        </strong>
                    </div>
                </div>

                <div class="booking-next-note">
                    <i class="bi bi-calendar-check-fill"></i>

                    <div>
                        <strong>What happens next?</strong>

                        <p>
                            The admin will review your request and confirm the
                            session date, time and meeting details.
                        </p>
                    </div>
                </div>

                <div class="booking-success-actions">
                    <a
                        href="{{ route('home') }}"
                        class="success-primary-button"
                    >
                        Return to Home
                    </a>

                    <a
                        href="{{ route('doubt-sessions.create') }}"
                        class="success-secondary-button"
                    >
                        Book Another Session
                    </a>
                </div>
            </div>
        </div>
    </main>

    @push('styles')
        <style>
            .booking-success-page {
                min-height: calc(100vh - 80px);
                display: grid;
                place-items: center;
                padding: 70px 16px;
                background:
                    radial-gradient(
                        circle at top,
                        rgba(0, 74, 173, 0.10),
                        transparent 38%
                    ),
                    #F7F9FC;
            }

            .booking-success-container {
                width: min(760px, 100%);
            }

            .booking-success-card {
                padding: 46px;
                border: 1px solid rgba(0, 74, 173, 0.12);
                border-radius: 30px;
                background: #FFFFFF;
                box-shadow: 0 30px 70px rgba(15, 23, 42, 0.10);
                text-align: center;
            }

            .booking-success-icon {
                width: 76px;
                height: 76px;
                display: grid;
                place-items: center;
                margin: 0 auto 20px;
                border-radius: 50%;
                background: rgba(22, 163, 74, 0.11);
                color: #15803D;
                font-size: 2rem;
            }

            .booking-success-label {
                display: inline-block;
                margin-bottom: 10px;
                color: #004AAD;
                font-size: 0.76rem;
                font-weight: 800;
                letter-spacing: 0.09em;
                text-transform: uppercase;
            }

            .booking-success-card h1 {
                max-width: 600px;
                margin: 0 auto 14px;
                color: #0F172A;
                font-size: clamp(1.8rem, 4vw, 2.65rem);
                font-weight: 850;
                line-height: 1.18;
                letter-spacing: -0.03em;
            }

            .booking-success-intro {
                max-width: 600px;
                margin: 0 auto 26px;
                color: #64748B;
                font-size: 0.98rem;
                line-height: 1.75;
            }

            .booking-reference-box {
                max-width: 420px;
                margin: 0 auto 30px;
                padding: 17px 20px;
                border: 1px dashed rgba(0, 74, 173, 0.30);
                border-radius: 15px;
                background: rgba(0, 74, 173, 0.06);
            }

            .booking-reference-box span {
                display: block;
                margin-bottom: 5px;
                color: #64748B;
                font-size: 0.74rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .booking-reference-box strong {
                color: #004AAD;
                font-size: 1.15rem;
                letter-spacing: 0.04em;
                overflow-wrap: anywhere;
            }

            .booking-success-details {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-bottom: 26px;
                border: 1px solid #D9D9D9;
                border-radius: 18px;
                overflow: hidden;
                text-align: left;
            }

            .success-detail {
                padding: 17px 20px;
                border-bottom: 1px solid #D9D9D9;
            }

            .success-detail:nth-child(odd) {
                border-right: 1px solid #D9D9D9;
            }

            .success-detail:nth-last-child(-n + 2) {
                border-bottom: 0;
            }

            .success-detail span {
                display: block;
                margin-bottom: 5px;
                color: #94A3B8;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .success-detail strong {
                display: block;
                color: #0F172A;
                font-size: 0.9rem;
                overflow-wrap: anywhere;
            }

            .success-detail .success-free {
                color: #15803D;
            }

            .booking-next-note {
                display: flex;
                align-items: flex-start;
                gap: 14px;
                margin-bottom: 28px;
                padding: 18px;
                border-radius: 16px;
                background: rgba(0, 74, 173, 0.07);
                color: #334155;
                text-align: left;
            }

            .booking-next-note i {
                color: #004AAD;
                font-size: 1.3rem;
            }

            .booking-next-note strong {
                display: block;
                margin-bottom: 3px;
                color: #0F172A;
            }

            .booking-next-note p {
                margin: 0;
                color: #64748B;
                font-size: 0.86rem;
                line-height: 1.6;
            }

            .booking-success-actions {
                display: flex;
                justify-content: center;
                gap: 12px;
            }

            .success-primary-button,
            .success-secondary-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-height: 48px;
                padding: 0 22px;
                border-radius: 12px;
                font-weight: 800;
                text-decoration: none;
            }

            .success-primary-button {
                background: #004AAD;
                color: #FFFFFF;
            }

            .success-primary-button:hover {
                background: #003B8A;
                color: #FFFFFF;
            }

            .success-secondary-button {
                border: 1px solid rgba(0, 74, 173, 0.20);
                background: #FFFFFF;
                color: #004AAD;
            }

            .success-secondary-button:hover {
                background: rgba(0, 74, 173, 0.06);
                color: #004AAD;
            }

            @media (max-width: 600px) {
                .booking-success-page {
                    padding: 40px 12px;
                }

                .booking-success-card {
                    padding: 32px 18px;
                    border-radius: 24px;
                }

                .booking-success-details {
                    grid-template-columns: 1fr;
                }

                .success-detail,
                .success-detail:nth-child(odd),
                .success-detail:nth-last-child(-n + 2) {
                    border-right: 0;
                    border-bottom: 1px solid #D9D9D9;
                }

                .success-detail:last-child {
                    border-bottom: 0;
                }

                .booking-success-actions {
                    flex-direction: column;
                }
            }
        </style>
    @endpush
@endsection
