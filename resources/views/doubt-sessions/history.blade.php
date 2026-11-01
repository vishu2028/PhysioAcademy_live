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
                @if(session('success'))
                    <div class="alert alert-success rounded-3 mb-4" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger rounded-3 mb-4" role="alert">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

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

                            <article class="doubt-history-card" id="session-booking-{{ $booking->id }}">
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

                                <section class="session-conversation" aria-label="Session conversation">
                                    <div class="session-conversation-actions">
                                        @if(filled($booking->admin_notes) || $booking->liked_by_current_user)
                                            @if($booking->liked_by_current_user)
                                                <form action="{{ route('doubt-sessions.likes.destroy', $booking) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="session-action-button is-liked" aria-pressed="true">
                                                        <i class="bi bi-hand-thumbs-up-fill" aria-hidden="true"></i>
                                                        Unlike
                                                        <span class="session-like-count">{{ $booking->likes_count }}</span>
                                                    </button>
                                                </form>
                                            @else
                                                <form action="{{ route('doubt-sessions.likes.store', $booking) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="session-action-button" aria-pressed="false">
                                                        <i class="bi bi-hand-thumbs-up" aria-hidden="true"></i>
                                                        Like
                                                        <span class="session-like-count">{{ $booking->likes_count }}</span>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif

                                        <button
                                            type="button"
                                            class="session-action-button session-follow-up-trigger"
                                            data-follow-up-dialog="follow-up-dialog-{{ $booking->id }}"
                                        >
                                            <i class="bi bi-chat-left-dots" aria-hidden="true"></i>
                                            Ask Follow-up Question
                                        </button>
                                    </div>

                                    @if($booking->followUps->isNotEmpty())
                                        <div class="session-follow-up-list">
                                            <h3>Follow-up conversation</h3>
                                            @foreach($booking->followUps as $followUp)
                                                <article class="session-follow-up">
                                                    <div class="session-follow-up-heading">
                                                        <strong>Your question</strong>
                                                        <time datetime="{{ $followUp->created_at?->toIso8601String() }}">
                                                            {{ $followUp->created_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                                        </time>
                                                        <span class="session-follow-up-status {{ $followUp->status === \App\Models\DoubtSessionFollowUp::STATUS_ANSWERED ? 'is-answered' : '' }}">
                                                            {{ ucfirst($followUp->status) }}
                                                        </span>
                                                    </div>
                                                    <p class="mb-2">{{ $followUp->question }}</p>

                                                    @if($followUp->answer)
                                                        <div class="session-follow-up-answer">
                                                            <strong>Admin response</strong>
                                                            <p class="mb-1">{{ $followUp->answer }}</p>
                                                            <small>
                                                                @if($followUp->answeredBy)
                                                                    {{ $followUp->answeredBy->name }} ·
                                                                @endif
                                                                {{ $followUp->answered_at?->copy()->setTimezone('Asia/Kolkata')->format('d M Y, h:i A') }}
                                                            </small>
                                                        </div>
                                                    @else
                                                        <span class="session-follow-up-pending">Waiting for an admin response</span>
                                                    @endif
                                                </article>
                                            @endforeach
                                        </div>
                                    @endif
                                </section>

                                <dialog class="session-follow-up-dialog" id="follow-up-dialog-{{ $booking->id }}" aria-labelledby="follow-up-title-{{ $booking->id }}">
                                    <form action="{{ route('doubt-sessions.follow-ups.store', $booking) }}" method="POST" class="session-follow-up-form">
                                        @csrf
                                        <div class="session-follow-up-dialog-heading">
                                            <div>
                                                <span class="doubt-history-eyebrow">Session {{ $booking->booking_reference }}</span>
                                                <h2 id="follow-up-title-{{ $booking->id }}">Ask a follow-up question</h2>
                                            </div>
                                            <button type="button" class="session-follow-up-close" data-follow-up-close aria-label="Close dialog">&times;</button>
                                        </div>
                                        <label for="follow-up-question-{{ $booking->id }}">Your question</label>
                                        <textarea
                                            id="follow-up-question-{{ $booking->id }}"
                                            name="question"
                                            rows="6"
                                            maxlength="5000"
                                            required
                                            placeholder="Add more details about what you would like to ask..."
                                        >{{ old('question') }}</textarea>
                                        <div class="session-follow-up-dialog-actions">
                                            <button type="button" class="session-action-button" data-follow-up-close>Cancel</button>
                                            <button type="submit" class="session-action-button session-follow-up-submit">
                                                <i class="bi bi-send" aria-hidden="true"></i>
                                                Submit question
                                            </button>
                                        </div>
                                    </form>
                                </dialog>
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

@push('scripts')
    <script>
        document.querySelectorAll('[data-follow-up-dialog]').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById(button.dataset.followUpDialog)?.showModal();
            });
        });

        document.querySelectorAll('.session-follow-up-dialog').forEach((dialog) => {
            dialog.querySelectorAll('[data-follow-up-close]').forEach((button) => {
                button.addEventListener('click', () => dialog.close());
            });

            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        });
    </script>
@endpush

@push('styles')
    <style>
        .doubt-history-page {
            --history-primary: #004AAD;
            --history-primary-dark: #003B8A;
            --history-primary-soft: rgba(0, 74, 173, 0.08);
            --history-heading: #0F172A;
            --history-text: #334155;
            --history-muted: #64748B;
            --history-faint: #94A3B8;
            --history-border: #D9D9D9;
            --history-background: #F7F9FC;
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
                    rgba(0, 74, 173, 0.08),
                    transparent 32%
                ),
                linear-gradient(
                    120deg,
                    rgba(0, 74, 173, 0.06) 0%,
                    rgba(217, 217, 217, 0.16) 44%,
                    #FFFFFF 72%,
                    rgba(0, 74, 173, 0.04) 100%
                );
        }

        .doubt-history-grid {
            position: absolute;
            inset: 0;
            z-index: -3;
            opacity: 0.52;
            background-image:
                linear-gradient(
                    rgba(0, 74, 173, 0.04) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(0, 74, 173, 0.04) 1px,
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
            border: 1px solid rgba(0, 74, 173, 0.10);
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
                0 0 0 84px rgba(0, 74, 173, 0.025);
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
            border: 1px solid rgba(0, 74, 173, 0.16);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.74);
            color: var(--history-primary-dark);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.06em;
            line-height: 1;
            text-transform: uppercase;
            box-shadow: 0 10px 28px rgba(0, 74, 173, 0.08);
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
            border: 1px solid rgba(0, 74, 173, 0.10);
            background: rgba(255, 255, 255, 0.96);
            box-shadow:
                0 18px 40px rgba(0, 74, 173, 0.08),
                0 2px 8px rgba(0, 74, 173, 0.03);
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
            border: 1px solid rgba(0, 74, 173, 0.10);
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
            background: #004AAD;
            color: #FFFFFF;
            font-weight: 800;
            text-decoration: none;
            box-shadow: 0 12px 26px rgba(0, 74, 173, 0.18);
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
            box-shadow: 0 16px 34px rgba(0, 74, 173, 0.22);
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
            border: 1px solid rgba(0, 74, 173, 0.10);
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

        .session-conversation {
            padding: 24px 32px 30px;
            border-top: 1px solid var(--history-border);
            background: #FCFDFF;
        }

        .session-conversation-actions,
        .session-follow-up-dialog-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
        }

        .session-conversation-actions form {
            margin: 0;
        }

        .session-action-button {
            display: inline-flex;
            min-height: 44px;
            align-items: center;
            justify-content: center;
            gap: 9px;
            padding: 0 16px;
            border: 1px solid rgba(0, 74, 173, 0.18);
            border-radius: 12px;
            background: #FFFFFF;
            color: var(--history-primary-dark);
            font: inherit;
            font-size: 0.86rem;
            font-weight: 750;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, transform 0.2s ease;
        }

        .session-action-button:hover {
            background: var(--history-primary-soft);
            transform: translateY(-1px);
        }

        .session-action-button.is-liked,
        .session-follow-up-submit {
            background: var(--history-primary);
            color: #FFFFFF;
        }

        .session-action-button.is-liked:hover,
        .session-follow-up-submit:hover {
            background: var(--history-primary-dark);
            color: #FFFFFF;
        }

        .session-like-count {
            display: inline-grid;
            min-width: 24px;
            height: 24px;
            place-items: center;
            padding: 0 6px;
            border-radius: 999px;
            background: rgba(0, 74, 173, 0.10);
            font-size: 0.78rem;
        }

        .session-action-button.is-liked .session-like-count {
            background: rgba(255, 255, 255, 0.2);
        }

        .session-follow-up-list {
            display: grid;
            gap: 14px;
            margin-top: 24px;
        }

        .session-follow-up-list h3 {
            margin: 0;
            color: var(--history-heading);
            font-size: 1rem;
            font-weight: 850;
        }

        .session-follow-up {
            padding: 16px;
            border: 1px solid #E2E8F0;
            border-radius: 14px;
            background: #FFFFFF;
            color: var(--history-text);
            white-space: pre-wrap;
            overflow-wrap: anywhere;
        }

        .session-follow-up-heading {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 12px;
            margin-bottom: 10px;
        }

        .session-follow-up-heading time,
        .session-follow-up-answer small {
            color: var(--history-muted);
            font-size: 0.78rem;
        }

        .session-follow-up-status {
            padding: 4px 9px;
            border-radius: 999px;
            background: #FFF7ED;
            color: #9A3412;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .session-follow-up-status.is-answered {
            background: #E9F8EF;
            color: #16803D;
        }

        .session-follow-up-answer {
            margin-top: 14px;
            padding: 14px;
            border-radius: 12px;
            background: #F0F7FF;
            color: var(--history-heading);
        }

        .session-follow-up-answer p {
            margin-top: 8px;
        }

        .session-follow-up-pending {
            color: #9A3412;
            font-size: 0.82rem;
            font-weight: 700;
        }

        .session-follow-up-dialog {
            width: min(560px, calc(100% - 28px));
            max-height: min(90vh, 760px);
            padding: 0;
            border: 0;
            border-radius: 20px;
            color: var(--history-heading);
            box-shadow: 0 24px 80px rgba(15, 23, 42, 0.28);
        }

        .session-follow-up-dialog::backdrop {
            background: rgba(15, 23, 42, 0.58);
            backdrop-filter: blur(3px);
        }

        .session-follow-up-form {
            display: grid;
            gap: 12px;
            padding: 26px;
        }

        .session-follow-up-dialog-heading {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 8px;
        }

        .session-follow-up-dialog-heading .doubt-history-eyebrow {
            margin-bottom: 12px;
            font-size: 0.68rem;
        }

        .session-follow-up-dialog-heading h2 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 850;
        }

        .session-follow-up-close {
            display: grid;
            width: 38px;
            height: 38px;
            flex: 0 0 38px;
            place-items: center;
            border: 0;
            border-radius: 50%;
            background: #F1F5F9;
            color: #334155;
            font-size: 1.6rem;
            cursor: pointer;
        }

        .session-follow-up-form label {
            font-weight: 750;
        }

        .session-follow-up-form textarea {
            width: 100%;
            resize: vertical;
            padding: 13px 14px;
            border: 1px solid #CBD5E1;
            border-radius: 12px;
            color: var(--history-heading);
            font: inherit;
            line-height: 1.6;
        }

        .session-follow-up-form textarea:focus {
            outline: 3px solid rgba(0, 74, 173, 0.15);
            border-color: var(--history-primary);
        }

        .session-follow-up-dialog-actions {
            justify-content: flex-end;
            margin-top: 6px;
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

            .session-conversation {
                padding: 20px;
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

            .session-conversation {
                padding: 18px 17px;
            }

            .session-conversation-actions,
            .session-conversation-actions form,
            .session-conversation-actions .session-action-button {
                width: 100%;
            }

            .session-follow-up-form {
                padding: 20px;
            }

            .doubt-history-empty {
                padding: 62px 20px;
            }
        }
    </style>
@endpush
