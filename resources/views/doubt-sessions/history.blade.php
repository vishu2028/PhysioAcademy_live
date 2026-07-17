@extends('layouts.frontend')

@section('title', 'Doubt Session History')

@section('content')
    <main class="doubt-history-page">
        <section class="doubt-history-hero">
            <div class="doubt-history-container">
                <span class="doubt-history-eyebrow">
                    <i class="bi bi-clock-history"></i>
                    My Sessions
                </span>

                <h1>Doubt Session History</h1>

                <p>
                    View your session status, schedule, meeting link
                    and message from the admin.
                </p>
            </div>
        </section>

        <section class="doubt-history-content">
            <div class="doubt-history-container">
                @if($bookings->isEmpty())
                    <div class="doubt-history-empty">
                        <div class="doubt-history-empty-icon">
                            <i class="bi bi-calendar2-x"></i>
                        </div>

                        <h2>No doubt session history found</h2>

                        <p>
                            You have not booked any doubt session yet.
                        </p>

                        <a
                            href="{{ route('doubt-sessions.create') }}"
                            class="doubt-history-book-button"
                        >
                            <i class="bi bi-plus-circle"></i>
                            Book Doubt Session
                        </a>
                    </div>
                @else
                    <div class="doubt-history-list">
                        @foreach($bookings as $booking)
                            @php
                                $statusLabel = match (
                                    $booking->booking_status
                                ) {
                                    \App\Models\DoubtSessionBooking::STATUS_PENDING_PAYMENT
                                        => 'Pending Payment',

                                    \App\Models\DoubtSessionBooking::STATUS_PENDING_SCHEDULE
                                        => 'Pending Schedule',

                                    \App\Models\DoubtSessionBooking::STATUS_CONFIRMED
                                        => 'Confirmed',

                                    \App\Models\DoubtSessionBooking::STATUS_COMPLETED
                                        => 'Completed',

                                    \App\Models\DoubtSessionBooking::STATUS_CANCELLED
                                        => 'Cancelled',

                                    default => ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $booking->booking_status
                                        )
                                    ),
                                };

                                $statusClass = str_replace(
                                    '_',
                                    '-',
                                    $booking->booking_status
                                );
                            @endphp

                            <article class="doubt-history-card">
                                {{-- Booking Status --}}
                                <div class="doubt-history-row">
                                    <div class="doubt-history-label">
                                        <span class="doubt-history-icon">
                                            <i class="bi bi-info-circle"></i>
                                        </span>

                                        <span>Booking Status</span>
                                    </div>

                                    <div class="doubt-history-value">
                                        <span
                                            class="booking-status-badge
                                                status-{{ $statusClass }}"
                                        >
                                            {{ $statusLabel }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Session Date and Time --}}
                                <div class="doubt-history-row">
                                    <div class="doubt-history-label">
                                        <span class="doubt-history-icon">
                                            <i class="bi bi-calendar-event"></i>
                                        </span>

                                        <span>Session Date & Time</span>
                                    </div>

                                    <div class="doubt-history-value">
                                        @if($booking->scheduled_at)
                                            <strong>
                                                {{ $booking->scheduled_at->format(
                                                    'd F Y'
                                                ) }}
                                            </strong>

                                            <small>
                                                {{ $booking->scheduled_at->format(
                                                    'h:i A'
                                                ) }}
                                            </small>
                                        @else
                                            <span class="history-not-available">
                                                Not scheduled yet
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Meeting Link --}}
                                <div class="doubt-history-row">
                                    <div class="doubt-history-label">
                                        <span class="doubt-history-icon">
                                            <i class="bi bi-camera-video"></i>
                                        </span>

                                        <span>Meeting Link</span>
                                    </div>

                                    <div class="doubt-history-value">
                                        @if(filled($booking->meeting_link))
                                            <a
                                                href="{{ $booking->meeting_link }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="history-meeting-button"
                                            >
                                                <i class="bi bi-camera-video-fill"></i>
                                                Join Meeting
                                            </a>
                                        @else
                                            <span class="history-not-available">
                                                Meeting link not available yet
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                {{-- Admin Note --}}
                                <div class="doubt-history-row admin-note-row">
                                    <div class="doubt-history-label">
                                        <span class="doubt-history-icon">
                                            <i class="bi bi-chat-left-text"></i>
                                        </span>

                                        <span>Admin Note</span>
                                    </div>

                                    <div class="doubt-history-value">
                                        @if(filled($booking->admin_notes))
                                            <div class="history-admin-note">
                                                {{ $booking->admin_notes }}
                                            </div>
                                        @else
                                            <span class="history-not-available">
                                                No admin note added yet
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if($bookings->hasPages())
                        <div class="doubt-history-pagination">
                            {{ $bookings->onEachSide(1)->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </section>
    </main>
@endsection

@push('styles')
    <style>
        .doubt-history-page {
            min-height: 100vh;
            background: #F5F8FC;
            color: #0F172A;
        }

        .doubt-history-container {
            width: min(1050px, calc(100% - 32px));
            margin: 0 auto;
        }

        .doubt-history-hero {
            padding: 145px 0 115px;
            background:
                radial-gradient(
                    circle at 80% 15%,
                    rgba(255, 255, 255, 0.15),
                    transparent 32%
                ),
                linear-gradient(
                    135deg,
                    #004AAD,
                    #003B8A
                );
            color: #FFFFFF;
        }

        .doubt-history-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 15px;
            padding: 8px 14px;
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.10);
            color: #FFFFFF;
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .doubt-history-hero h1 {
            margin: 0 0 14px;
            color: #FFFFFF;
            font-size: clamp(2.2rem, 5vw, 3.8rem);
            font-weight: 850;
            letter-spacing: -0.04em;
        }

        .doubt-history-hero p {
            max-width: 650px;
            margin: 0;
            color: rgba(255, 255, 255, 0.82);
            font-size: 1rem;
            line-height: 1.75;
        }

        .doubt-history-content {
            margin-top: -55px;
            padding-bottom: 90px;
        }

        .doubt-history-list {
            display: grid;
            gap: 24px;
        }

        .doubt-history-card {
            overflow: hidden;
            border: 1px solid rgba(0, 74, 173, 0.10);
            border-radius: 24px;
            background: #FFFFFF;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.08);
        }

        .doubt-history-row {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            gap: 30px;
            align-items: center;
            min-height: 90px;
            padding: 22px 30px;
            border-bottom: 1px solid #E5E7EB;
        }

        .doubt-history-row:last-child {
            border-bottom: 0;
        }

        .doubt-history-label {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #334155;
            font-size: 0.88rem;
            font-weight: 800;
        }

        .doubt-history-icon {
            display: grid;
            place-items: center;
            flex: 0 0 auto;
            width: 42px;
            height: 42px;
            border-radius: 13px;
            background: rgba(0, 74, 173, 0.09);
            color: #004AAD;
            font-size: 1rem;
        }

        .doubt-history-value {
            min-width: 0;
            color: #1E293B;
            font-size: 0.9rem;
        }

        .doubt-history-value strong {
            display: block;
            color: #0F172A;
            font-size: 0.95rem;
            font-weight: 800;
        }

        .doubt-history-value small {
            display: block;
            margin-top: 4px;
            color: #64748B;
            font-size: 0.8rem;
        }

        .booking-status-badge {
            display: inline-flex;
            align-items: center;
            min-height: 34px;
            padding: 0 13px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
        }

        .status-pending-payment {
            background: rgba(245, 158, 11, 0.12);
            color: #B45309;
        }

        .status-pending-schedule {
            background: rgba(37, 99, 235, 0.10);
            color: #1D4ED8;
        }

        .status-confirmed {
            background: rgba(22, 163, 74, 0.11);
            color: #15803D;
        }

        .status-completed {
            background: rgba(14, 116, 144, 0.11);
            color: #0E7490;
        }

        .status-cancelled {
            background: rgba(220, 38, 38, 0.10);
            color: #B91C1C;
        }

        .history-meeting-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 43px;
            padding: 0 18px;
            border-radius: 12px;
            background: #004AAD;
            color: #FFFFFF;
            font-size: 0.82rem;
            font-weight: 800;
            text-decoration: none;
            transition:
                background 0.2s ease,
                transform 0.2s ease;
        }

        .history-meeting-button:hover {
            background: #003B8A;
            color: #FFFFFF;
            transform: translateY(-2px);
        }

        .history-not-available {
            color: #94A3B8;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .admin-note-row {
            align-items: flex-start;
        }

        .history-admin-note {
            padding: 16px 18px;
            border-left: 4px solid #004AAD;
            border-radius: 0 12px 12px 0;
            background: rgba(0, 74, 173, 0.05);
            color: #475569;
            line-height: 1.7;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .doubt-history-empty {
            padding: 75px 25px;
            border: 1px solid rgba(0, 74, 173, 0.10);
            border-radius: 24px;
            background: #FFFFFF;
            box-shadow: 0 22px 55px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .doubt-history-empty-icon {
            display: grid;
            place-items: center;
            width: 72px;
            height: 72px;
            margin: 0 auto 20px;
            border-radius: 20px;
            background: rgba(0, 74, 173, 0.10);
            color: #004AAD;
            font-size: 1.7rem;
        }

        .doubt-history-empty h2 {
            margin: 0 0 10px;
            color: #0F172A;
            font-size: 1.35rem;
            font-weight: 850;
        }

        .doubt-history-empty p {
            margin: 0 0 24px;
            color: #64748B;
            font-size: 0.9rem;
        }

        .doubt-history-book-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 0 20px;
            border-radius: 12px;
            background: #004AAD;
            color: #FFFFFF;
            font-size: 0.84rem;
            font-weight: 800;
            text-decoration: none;
        }

        .doubt-history-book-button:hover {
            background: #003B8A;
            color: #FFFFFF;
        }

        .doubt-history-pagination {
            margin-top: 28px;
        }

        @media (max-width: 767px) {
            .doubt-history-container {
                width: min(100% - 24px, 1050px);
            }

            .doubt-history-hero {
                padding: 120px 0 95px;
            }

            .doubt-history-row {
                grid-template-columns: 1fr;
                gap: 15px;
                min-height: auto;
                padding: 21px 20px;
            }

            .doubt-history-label {
                font-size: 0.84rem;
            }

            .history-meeting-button {
                width: 100%;
            }
        }
    </style>
@endpush
