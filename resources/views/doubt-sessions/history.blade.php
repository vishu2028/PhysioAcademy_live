@extends('layouts.frontend')

@section('title', 'Doubt Session History')

@section('content')
    <main class="doubt-history-page">
        <section class="doubt-history-hero">
            <div class="doubt-history-grid" aria-hidden="true"></div>
            <div class="doubt-history-orb doubt-history-orb-one" aria-hidden="true"></div>
            <div class="doubt-history-orb doubt-history-orb-two" aria-hidden="true"></div>

            <div class="doubt-history-container doubt-history-hero-content">
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
            --history-primary: #2F80ED;
            --history-primary-dark: #0F5ABD;
            --history-primary-soft: #EAF4FF;
            --history-heading: #0B1530;
            --history-text: #334155;
            --history-muted: #718198;
            --history-faint: #94A3B8;
            --history-border: #E3EBF5;
            --history-background: #F3F7FC;
            --history-surface: #FFFFFF;

            min-height: 100vh;
            overflow: hidden;
            background: var(--history-background);
            color: var(--history-heading);
        }

        .doubt-history-container {
            width: min(1120px, calc(100% - 40px));
            margin: 0 auto;
        }

        /*
         * Light hero theme matching the main website.
         * Navbar styling is intentionally not overridden here.
         */
        .doubt-history-hero {
            position: relative;
            isolation: isolate;
            overflow: hidden;
            min-height: 510px;
            padding: 155px 0 135px;
            background:
                radial-gradient(
                    circle at 12% 18%,
                    rgba(56, 189, 248, 0.20),
                    transparent 32%
                ),
                radial-gradient(
                    circle at 88% 12%,
                    rgba(47, 128, 237, 0.15),
                    transparent 31%
                ),
                linear-gradient(
                    120deg,
                    #EAF5FF 0%,
                    #F8FBFF 45%,
                    #FFFFFF 72%,
                    #EEF5FF 100%
                );
        }

        .doubt-history-grid {
            position: absolute;
            inset: 0;
            z-index: -3;
            opacity: 0.58;
            background-image:
                linear-gradient(
                    rgba(47, 128, 237, 0.055) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(47, 128, 237, 0.055) 1px,
                    transparent 1px
                );
            background-size: 72px 72px;
            -webkit-mask-image: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.82),
                transparent 96%
            );
            mask-image: linear-gradient(
                to bottom,
                rgba(0, 0, 0, 0.82),
                transparent 96%
            );
        }

        .doubt-history-orb {
            position: absolute;
            z-index: -2;
            border: 1px solid rgba(47, 128, 237, 0.11);
            border-radius: 50%;
            pointer-events: none;
        }

        .doubt-history-orb-one {
            top: 110px;
            right: 7%;
            width: 310px;
            height: 310px;
            box-shadow:
                0 0 0 42px rgba(255, 255, 255, 0.30),
                0 0 0 84px rgba(47, 128, 237, 0.035);
        }

        .doubt-history-orb-two {
            top: 40px;
            left: -110px;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.22);
            filter: blur(1px);
        }

        .doubt-history-hero-content {
            position: relative;
            z-index: 2;
        }

        .doubt-history-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
            padding: 9px 15px;
            border: 1px solid rgba(47, 128, 237, 0.18);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.74);
            color: var(--history-primary-dark);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            line-height: 1;
            text-transform: uppercase;
            box-shadow: 0 10px 28px rgba(47, 128, 237, 0.08);
            backdrop-filter: blur(12px);
        }

        .doubt-history-eyebrow i {
            font-size: 0.88rem;
        }

        .doubt-history-hero h1 {
            max-width: 820px;
            margin: 0 0 22px;
            color: var(--history-heading);
            font-size: clamp(2.45rem, 5.8vw, 4.85rem);
            font-weight: 850;
            line-height: 1.03;
            letter-spacing: -0.055em;
        }

        .doubt-history-hero p {
            max-width: 690px;
            margin: 0;
            color: #60728C;
            font-size: clamp(0.98rem, 1.5vw, 1.12rem);
            line-height: 1.8;
        }

        .doubt-history-content {
            position: relative;
            z-index: 4;
            margin-top: -82px;
            padding-bottom: 96px;
        }

        .doubt-history-list {
            display: grid;
            gap: 26px;
        }

        .doubt-history-card,
        .doubt-history-empty {
            border: 1px solid rgba(148, 163, 184, 0.22);
            background: rgba(255, 255, 255, 0.96);
            box-shadow:
                0 28px 70px rgba(15, 23, 42, 0.09),
                0 2px 8px rgba(47, 128, 237, 0.035);
            backdrop-filter: blur(12px);
        }

        .doubt-history-card {
            overflow: hidden;
            border-radius: 26px;
        }

        .doubt-history-row {
            display: grid;
            grid-template-columns: 280px minmax(0, 1fr);
            gap: 34px;
            align-items: center;
            min-height: 100px;
            padding: 24px 32px;
            border-bottom: 1px solid var(--history-border);
            transition: background 0.2s ease;
        }

        .doubt-history-row:hover {
            background: #FBFDFF;
        }

        .doubt-history-row:last-child {
            border-bottom: 0;
        }

        .doubt-history-label {
            display: flex;
            align-items: center;
            gap: 13px;
            color: #25334B;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .doubt-history-icon {
            display: grid;
            place-items: center;
            flex: 0 0 44px;
            width: 44px;
            height: 44px;
            border: 1px solid rgba(47, 128, 237, 0.08);
            border-radius: 14px;
            background: var(--history-primary-soft);
            color: var(--history-primary);
            font-size: 1.02rem;
        }

        .doubt-history-value {
            min-width: 0;
            color: var(--history-text);
            font-size: 0.92rem;
        }

        .doubt-history-value strong {
            display: block;
            color: var(--history-heading);
            font-size: 0.96rem;
            font-weight: 800;
        }

        .doubt-history-value small {
            display: block;
            margin-top: 5px;
            color: var(--history-muted);
            font-size: 0.8rem;
        }

        .booking-status-badge {
            display: inline-flex;
            align-items: center;
            min-height: 36px;
            padding: 0 14px;
            border-radius: 999px;
            font-size: 0.76rem;
            font-weight: 800;
            line-height: 1;
        }

        .status-pending-payment {
            background: #FFF2DD;
            color: #B45309;
        }

        .status-pending-schedule {
            background: #EAF3FF;
            color: #1D5FBA;
        }

        .status-confirmed {
            background: #E9F8EF;
            color: #16803D;
        }

        .status-completed {
            background: #E8F7FA;
            color: #0E7490;
        }

        .status-cancelled {
            background: #FDECEC;
            color: #B91C1C;
        }

        .history-meeting-button,
        .doubt-history-book-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border-radius: 13px;
            background: linear-gradient(
                135deg,
                var(--history-primary),
                #38BDF8
            );
            color: #FFFFFF;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 12px 26px rgba(47, 128, 237, 0.20);
            transition:
                transform 0.2s ease,
                box-shadow 0.2s ease,
                filter 0.2s ease;
        }

        .history-meeting-button {
            min-height: 44px;
            padding: 0 19px;
            font-size: 0.82rem;
        }

        .history-meeting-button:hover,
        .doubt-history-book-button:hover {
            color: #FFFFFF;
            filter: brightness(0.97);
            transform: translateY(-2px);
            box-shadow: 0 16px 34px rgba(47, 128, 237, 0.26);
        }

        .history-not-available {
            color: var(--history-faint);
            font-size: 0.86rem;
            font-weight: 650;
        }

        .admin-note-row {
            align-items: flex-start;
        }

        .history-admin-note {
            padding: 17px 19px;
            border-left: 4px solid var(--history-primary);
            border-radius: 0 14px 14px 0;
            background: #F2F7FD;
            color: #4D5E75;
            line-height: 1.72;
            white-space: pre-line;
            overflow-wrap: anywhere;
        }

        .doubt-history-empty {
            padding: 82px 26px;
            border-radius: 26px;
            text-align: center;
        }

        .doubt-history-empty-icon {
            display: grid;
            place-items: center;
            width: 76px;
            height: 76px;
            margin: 0 auto 22px;
            border: 1px solid rgba(47, 128, 237, 0.10);
            border-radius: 22px;
            background: var(--history-primary-soft);
            color: var(--history-primary);
            font-size: 1.75rem;
        }

        .doubt-history-empty h2 {
            margin: 0 0 10px;
            color: var(--history-heading);
            font-size: 1.4rem;
            font-weight: 850;
        }

        .doubt-history-empty p {
            margin: 0 0 26px;
            color: var(--history-muted);
            font-size: 0.92rem;
        }

        .doubt-history-book-button {
            min-height: 48px;
            padding: 0 22px;
            font-size: 0.85rem;
        }

        .doubt-history-pagination {
            margin-top: 30px;
        }

        .doubt-history-pagination nav {
            display: flex;
            justify-content: center;
        }

        @media (max-width: 991.98px) {
            .doubt-history-hero {
                min-height: 470px;
                padding: 140px 0 120px;
            }

            .doubt-history-orb-one {
                right: -100px;
                opacity: 0.65;
            }

            .doubt-history-row {
                grid-template-columns: 230px minmax(0, 1fr);
                gap: 24px;
            }
        }

        @media (max-width: 767.98px) {
            .doubt-history-container {
                width: min(100% - 24px, 1120px);
            }

            .doubt-history-hero {
                min-height: 430px;
                padding: 118px 0 100px;
            }

            .doubt-history-hero h1 {
                max-width: 620px;
                font-size: clamp(2.15rem, 11vw, 3.45rem);
                line-height: 1.08;
            }

            .doubt-history-hero p {
                font-size: 0.94rem;
                line-height: 1.7;
            }

            .doubt-history-content {
                margin-top: -58px;
                padding-bottom: 72px;
            }

            .doubt-history-row {
                grid-template-columns: 1fr;
                gap: 16px;
                min-height: auto;
                padding: 22px 20px;
            }

            .doubt-history-label {
                font-size: 0.86rem;
            }

            .history-meeting-button {
                width: 100%;
            }

            .doubt-history-card,
            .doubt-history-empty {
                border-radius: 21px;
            }
        }

        @media (max-width: 479.98px) {
            .doubt-history-hero {
                padding-top: 112px;
            }

            .doubt-history-eyebrow {
                margin-bottom: 20px;
                padding: 8px 13px;
                font-size: 0.7rem;
            }

            .doubt-history-row {
                padding: 20px 17px;
            }

            .doubt-history-empty {
                padding: 62px 20px;
            }
        }
    </style>
@endpush
