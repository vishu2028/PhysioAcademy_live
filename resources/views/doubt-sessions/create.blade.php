@extends('layouts.frontend')

@section('title', 'Book a Doubt Session')

@section('content')
    <main class="doubt-booking-page">
        <section class="doubt-booking-hero">
            <div class="doubt-booking-container">
                <a
                    href="{{ route('home') }}#ask-doubt"
                    class="booking-back-link"
                >
                    <i class="bi bi-arrow-left"></i>
                    Back to Academic Support
                </a>

                <div class="booking-heading">
                    <span class="booking-badge">
                        <i class="bi bi-camera-video-fill"></i>
                        One-on-One Academic Support
                    </span>

                    <h1>Book a Doubt Session</h1>

                    <p>
                        Submit your physiotherapy doubt and booking details.
                        The admin will confirm your session date and time
                        after your booking is completed.
                    </p>
                </div>
            </div>
        </section>

        <section class="doubt-booking-content">
            <div class="doubt-booking-container">
                <div class="booking-layout">
                    <div
                        class="booking-form-card"
                        id="booking-form"
                    >
                        <div class="booking-card-header">
                            <div>
                                <span class="booking-card-eyebrow">
                                    Student Information
                                </span>

                                <h2>Session Booking Form</h2>
                            </div>

                            <div class="booking-security">
                                <i class="bi bi-shield-check"></i>
                                Secure Booking
                            </div>
                        </div>

                        {{-- General booking/payment error --}}
                        @if(session('session_error'))
                            <div
                                class="booking-alert booking-alert-danger"
                                id="session-error-alert"
                                role="alert"
                                aria-live="assertive"
                            >
                                <div class="booking-alert-icon">
                                    <i class="bi bi-exclamation-circle-fill"></i>
                                </div>

                                <div>
                                    <strong>Unable to continue</strong>

                                    <p>
                                        {{ session('session_error') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- Compatibility with the previous Phase 3 message --}}
                        @if(session('payment_required'))
                            <div
                                class="booking-alert booking-alert-warning"
                                id="payment-required-alert"
                                role="alert"
                                aria-live="assertive"
                            >
                                <div class="booking-alert-icon">
                                    <i class="bi bi-credit-card-fill"></i>
                                </div>

                                <div>
                                    <strong>Payment required</strong>

                                    <p>
                                        {{ session('payment_required') }}
                                    </p>
                                </div>
                            </div>
                        @endif

                        {{-- Form validation summary --}}
                        @if($errors->any())
                            <div
                                class="booking-alert booking-alert-danger"
                                id="validation-error-alert"
                                role="alert"
                                aria-live="assertive"
                            >
                                <div class="booking-alert-icon">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                </div>

                                <div>
                                    <strong>Please check the form</strong>

                                    <ul class="booking-error-list">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

                        <form
                            action="{{ route('doubt-sessions.store') }}"
                            method="POST"
                            id="doubtSessionBookingForm"
                        >
                            @csrf

                            <div class="booking-form-grid">
                                <div class="booking-field">
                                    <label for="studentName">
                                        Student Name
                                    </label>

                                    <input
                                        type="text"
                                        id="studentName"
                                        value="{{ auth()->user()->name }}"
                                        readonly
                                    >

                                    <small>
                                        Taken from your logged-in account.
                                    </small>
                                </div>

                                <div class="booking-field">
                                    <label for="studentEmail">
                                        Email Address
                                    </label>

                                    <input
                                        type="email"
                                        id="studentEmail"
                                        value="{{ auth()->user()->email }}"
                                        readonly
                                    >

                                    <small>
                                        Session updates will be linked to this account.
                                    </small>
                                </div>

                                <div class="booking-field booking-field-full">
                                    <label for="phone">
                                        Phone Number
                                        <span>*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="phone"
                                        id="phone"
                                        value="{{ old('phone') }}"
                                        class="@error('phone') is-invalid @enderror"
                                        placeholder="+91 98765 43210"
                                        maxlength="30"
                                        autocomplete="tel"
                                        required
                                    >

                                    @error('phone')
                                    <div class="booking-error">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="booking-field">
                                    <label for="academicYear">
                                        Academic Year
                                        <span>*</span>
                                    </label>

                                    <select
                                        name="academic_year_id"
                                        id="academicYear"
                                        class="@error('academic_year_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">
                                            Select Academic Year
                                        </option>

                                        @foreach($years as $year)
                                            <option
                                                value="{{ $year->id }}"
                                                @selected(
                                                    old('academic_year_id')
                                                    == $year->id
                                                )
                                            >
                                                {{ $year->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('academic_year_id')
                                    <div class="booking-error">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="booking-field">
                                    <label for="subject">
                                        Subject
                                        <span>*</span>
                                    </label>

                                    <select
                                        name="subject_id"
                                        id="subject"
                                        class="@error('subject_id') is-invalid @enderror"
                                        required
                                    >
                                        <option value="">
                                            Select Subject
                                        </option>

                                        @foreach($subjects as $subject)
                                            <option
                                                value="{{ $subject->id }}"
                                                @selected(
                                                    old('subject_id')
                                                    == $subject->id
                                                )
                                            >
                                                {{ $subject->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('subject_id')
                                    <div class="booking-error">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="booking-field booking-field-full">
                                    <label for="topic">
                                        Topic
                                        <span>*</span>
                                    </label>

                                    <input
                                        type="text"
                                        name="topic"
                                        id="topic"
                                        value="{{ old('topic') }}"
                                        class="@error('topic') is-invalid @enderror"
                                        placeholder="For example: Brachial Plexus"
                                        maxlength="255"
                                        required
                                    >

                                    @error('topic')
                                    <div class="booking-error">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>

                                <div class="booking-field booking-field-full">
                                    <label for="doubt">
                                        Describe Your Doubt
                                        <span>*</span>
                                    </label>

                                    <textarea
                                        name="doubt"
                                        id="doubt"
                                        rows="7"
                                        class="@error('doubt') is-invalid @enderror"
                                        placeholder="Explain the concept or question you need help with..."
                                        maxlength="5000"
                                        required
                                    >{{ old('doubt') }}</textarea>

                                    <div class="booking-field-footer">
                                        <small>
                                            Add enough detail so the session can be
                                            prepared properly.
                                        </small>

                                        <small>
                                            <span id="doubtCharacterCount">
                                                {{ mb_strlen(old('doubt', '')) }}
                                            </span>
                                            / 5,000 characters
                                        </small>
                                    </div>

                                    @error('doubt')
                                    <div class="booking-error">
                                        {{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>

                            <div class="booking-submit-area">
                                <div class="booking-submit-summary">
                                    @if($isFree)
                                        <span class="summary-price free">
                                            Free Session
                                        </span>
                                    @else
                                        <span class="summary-price">
                                            ₹{{ number_format((float) $price, 2) }}
                                        </span>
                                    @endif

                                    <span class="summary-duration">
                                        {{ $durationMinutes }} Minutes
                                    </span>
                                </div>

                                <button
                                    type="submit"
                                    class="booking-submit-button"
                                    id="bookingSubmitButton"
                                >
                                    <span id="bookingSubmitText">
                                        @if($isFree)
                                            Submit Free Booking
                                        @else
                                            Continue to Payment
                                        @endif
                                    </span>

                                    <i
                                        class="bi bi-arrow-right"
                                        id="bookingSubmitIcon"
                                    ></i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <aside class="booking-sidebar">
                        <div class="booking-summary-card">
                            <div class="summary-icon">
                                <i class="bi bi-camera-video-fill"></i>
                            </div>

                            <span class="summary-label">
                                Session Summary
                            </span>

                            <h3>One-on-One Doubt Session</h3>

                            <div class="summary-list">
                                <div class="summary-row">
                                    <span>
                                        <i class="bi bi-clock"></i>
                                        Duration
                                    </span>

                                    <strong>
                                        {{ $durationMinutes }} Minutes
                                    </strong>
                                </div>

                                <div class="summary-row">
                                    <span>
                                        <i class="bi bi-wallet2"></i>
                                        Session Fee
                                    </span>

                                    <strong>
                                        @if($isFree)
                                            Free
                                        @else
                                            ₹{{ number_format((float) $price, 2) }}
                                        @endif
                                    </strong>
                                </div>

                                <div class="summary-row">
                                    <span>
                                        <i class="bi bi-calendar-check"></i>
                                        Schedule
                                    </span>

                                    <strong>
                                        Admin Confirmation
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div class="booking-info-card">
                            <h4>
                                <i class="bi bi-info-circle-fill"></i>
                                What happens next?
                            </h4>

                            <ol>
                                <li>
                                    Submit your doubt and contact details.
                                </li>

                                @if(! $isFree)
                                    <li>
                                        Complete the online payment through Razorpay.
                                    </li>
                                @endif

                                <li>
                                    The admin will review your booking.
                                </li>

                                <li>
                                    The session date, time and meeting details
                                    will be confirmed afterward.
                                </li>
                            </ol>
                        </div>
                    </aside>
                </div>
            </div>
        </section>
    </main>

    @push('styles')
        <style>
            .doubt-booking-page {
                min-height: 100vh;
                background:
                    radial-gradient(
                        circle at 10% 10%,
                        rgba(0, 74, 173, 0.08),
                        transparent 32%
                    ),
                    #F7F9FC;
                color: #0F172A;
            }

            .doubt-booking-container {
                width: min(1180px, calc(100% - 32px));
                margin: 0 auto;
            }

            .doubt-booking-hero {
                padding: 55px 0 125px;
                background:
                    linear-gradient(
                        135deg,
                        rgba(0, 74, 173, 0.98),
                        #003B8A
                    );
                color: #FFFFFF;
            }

            .booking-back-link {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 36px;
                color: rgba(255, 255, 255, 0.84);
                font-size: 0.9rem;
                font-weight: 700;
                text-decoration: none;
            }

            .booking-back-link:hover {
                color: #FFFFFF;
            }

            .booking-heading {
                max-width: 720px;
            }

            .booking-badge {
                display: inline-flex;
                align-items: center;
                gap: 8px;
                margin-bottom: 18px;
                padding: 8px 15px;
                border: 1px solid rgba(255, 255, 255, 0.22);
                border-radius: 999px;
                background: rgba(255, 255, 255, 0.12);
                color: #FFFFFF;
                font-size: 0.78rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
            }

            .booking-heading h1 {
                margin: 0 0 16px;
                color: #FFFFFF;
                font-size: clamp(2.2rem, 5vw, 4rem);
                font-weight: 850;
                letter-spacing: -0.04em;
            }

            .booking-heading p {
                max-width: 650px;
                margin: 0;
                color: rgba(255, 255, 255, 0.80);
                font-size: 1.06rem;
                line-height: 1.8;
            }

            .doubt-booking-content {
                margin-top: -70px;
                padding: 0 0 90px;
            }

            .booking-layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 340px;
                gap: 28px;
                align-items: start;
            }

            .booking-form-card,
            .booking-summary-card,
            .booking-info-card {
                border: 1px solid rgba(0, 74, 173, 0.10);
                background: #FFFFFF;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.08);
            }

            .booking-form-card {
                padding: 38px;
                border-radius: 28px;
                scroll-margin-top: 110px;
            }

            .booking-card-header {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                gap: 20px;
                margin-bottom: 30px;
                padding-bottom: 25px;
                border-bottom: 1px solid #D9D9D9;
            }

            .booking-card-eyebrow {
                display: block;
                margin-bottom: 6px;
                color: #004AAD;
                font-size: 0.74rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .booking-card-header h2 {
                margin: 0;
                color: #0F172A;
                font-size: 1.65rem;
                font-weight: 800;
            }

            .booking-security {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                padding: 8px 12px;
                border-radius: 999px;
                background: rgba(0, 74, 173, 0.08);
                color: #004AAD;
                font-size: 0.78rem;
                font-weight: 700;
                white-space: nowrap;
            }

            /*
             * Flash and validation alerts
             */
            .booking-alert {
                display: flex;
                align-items: flex-start;
                gap: 13px;
                margin-bottom: 24px;
                padding: 16px 18px;
                border: 1px solid transparent;
                border-radius: 16px;
                line-height: 1.55;
                animation: bookingAlertAppear 0.3s ease;
            }

            .booking-alert-icon {
                flex: 0 0 auto;
                margin-top: 1px;
                font-size: 1.1rem;
            }

            .booking-alert strong {
                display: block;
                margin-bottom: 2px;
                font-size: 0.9rem;
            }

            .booking-alert p {
                margin: 0;
                font-size: 0.86rem;
            }

            .booking-alert-danger {
                border-color: rgba(220, 38, 38, 0.18);
                background: rgba(220, 38, 38, 0.08);
                color: #B91C1C;
            }

            .booking-alert-warning {
                border-color: rgba(217, 119, 6, 0.22);
                background: rgba(245, 158, 11, 0.10);
                color: #92400E;
            }

            .booking-error-list {
                margin: 6px 0 0;
                padding-left: 18px;
                font-size: 0.84rem;
            }

            .booking-error-list li + li {
                margin-top: 3px;
            }

            @keyframes bookingAlertAppear {
                from {
                    opacity: 0;
                    transform: translateY(-8px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .booking-form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 22px;
            }

            .booking-field-full {
                grid-column: 1 / -1;
            }

            .booking-field label {
                display: block;
                margin-bottom: 8px;
                color: #334155;
                font-size: 0.88rem;
                font-weight: 750;
            }

            .booking-field label span {
                color: #DC2626;
            }

            .booking-field input,
            .booking-field select,
            .booking-field textarea {
                width: 100%;
                border: 1px solid #D9D9D9;
                border-radius: 13px;
                background: #FFFFFF;
                color: #0F172A;
                font: inherit;
                outline: none;
                transition:
                    border-color 0.2s ease,
                    box-shadow 0.2s ease;
            }

            .booking-field input,
            .booking-field select {
                min-height: 50px;
                padding: 0 15px;
            }

            .booking-field textarea {
                padding: 14px 15px;
                resize: vertical;
            }

            .booking-field input:focus,
            .booking-field select:focus,
            .booking-field textarea:focus {
                border-color: #004AAD;
                box-shadow: 0 0 0 4px rgba(0, 74, 173, 0.10);
            }

            .booking-field input[readonly] {
                background: rgba(217, 217, 217, 0.20);
                color: #64748B;
                cursor: not-allowed;
            }

            .booking-field .is-invalid {
                border-color: #DC2626;
                box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.08);
            }

            .booking-field small {
                display: block;
                margin-top: 7px;
                color: #94A3B8;
                font-size: 0.76rem;
            }

            .booking-field-footer {
                display: flex;
                justify-content: space-between;
                gap: 12px;
            }

            .booking-error {
                margin-top: 7px;
                color: #DC2626;
                font-size: 0.78rem;
                font-weight: 600;
            }

            .booking-submit-area {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 24px;
                margin-top: 30px;
                padding-top: 25px;
                border-top: 1px solid #D9D9D9;
            }

            .booking-submit-summary {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .summary-price,
            .summary-duration {
                display: inline-flex;
                padding: 7px 11px;
                border-radius: 999px;
                font-size: 0.78rem;
                font-weight: 800;
            }

            .summary-price {
                background: rgba(0, 74, 173, 0.10);
                color: #004AAD;
            }

            .summary-price.free {
                background: rgba(22, 163, 74, 0.10);
                color: #15803D;
            }

            .summary-duration {
                background: rgba(217, 217, 217, 0.34);
                color: #475569;
            }

            .booking-submit-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                min-height: 50px;
                padding: 0 24px;
                border: 0;
                border-radius: 13px;
                background: #004AAD;
                color: #FFFFFF;
                font: inherit;
                font-weight: 800;
                cursor: pointer;
                transition:
                    transform 0.2s ease,
                    background 0.2s ease,
                    opacity 0.2s ease;
            }

            .booking-submit-button:hover {
                background: #003B8A;
                transform: translateY(-2px);
            }

            .booking-submit-button:disabled {
                opacity: 0.72;
                cursor: wait;
                transform: none;
            }

            .booking-submit-button.is-loading #bookingSubmitIcon {
                animation: bookingIconSpin 0.8s linear infinite;
            }

            @keyframes bookingIconSpin {
                to {
                    transform: rotate(360deg);
                }
            }

            .booking-sidebar {
                display: grid;
                gap: 20px;
                position: sticky;
                top: 100px;
            }

            .booking-summary-card,
            .booking-info-card {
                padding: 28px;
                border-radius: 24px;
            }

            .summary-icon {
                width: 52px;
                height: 52px;
                display: grid;
                place-items: center;
                margin-bottom: 18px;
                border-radius: 16px;
                background: #004AAD;
                color: #FFFFFF;
                font-size: 1.25rem;
            }

            .summary-label {
                display: block;
                margin-bottom: 5px;
                color: #004AAD;
                font-size: 0.72rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .booking-summary-card h3 {
                margin: 0 0 22px;
                color: #0F172A;
                font-size: 1.25rem;
                font-weight: 800;
            }

            .summary-list {
                display: grid;
                gap: 15px;
            }

            .summary-row {
                display: flex;
                justify-content: space-between;
                gap: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid rgba(217, 217, 217, 0.75);
                font-size: 0.84rem;
            }

            .summary-row:last-child {
                padding-bottom: 0;
                border-bottom: 0;
            }

            .summary-row span {
                display: inline-flex;
                align-items: center;
                gap: 7px;
                color: #64748B;
            }

            .summary-row strong {
                max-width: 145px;
                color: #0F172A;
                text-align: right;
            }

            .booking-info-card h4 {
                display: flex;
                align-items: center;
                gap: 8px;
                margin: 0 0 16px;
                color: #0F172A;
                font-size: 1rem;
                font-weight: 800;
            }

            .booking-info-card h4 i {
                color: #004AAD;
            }

            .booking-info-card ol {
                margin: 0;
                padding-left: 19px;
                color: #64748B;
                font-size: 0.84rem;
                line-height: 1.75;
            }

            .booking-info-card li + li {
                margin-top: 7px;
            }

            @media (max-width: 991px) {
                .booking-layout {
                    grid-template-columns: 1fr;
                }

                .booking-sidebar {
                    position: static;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 767px) {
                .doubt-booking-container {
                    width: min(100% - 24px, 1180px);
                }

                .doubt-booking-hero {
                    padding: 38px 0 105px;
                }

                .booking-heading h1 {
                    font-size: 2.25rem;
                }

                .booking-form-card {
                    padding: 25px 20px;
                    border-radius: 22px;
                    scroll-margin-top: 85px;
                }

                .booking-card-header {
                    flex-direction: column;
                }

                .booking-form-grid {
                    grid-template-columns: 1fr;
                }

                .booking-field-full {
                    grid-column: auto;
                }

                .booking-submit-area {
                    flex-direction: column;
                    align-items: stretch;
                }

                .booking-submit-summary {
                    justify-content: center;
                }

                .booking-submit-button {
                    width: 100%;
                }

                .booking-sidebar {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 480px) {
                .booking-heading h1 {
                    font-size: 1.95rem;
                }

                .booking-heading p {
                    font-size: 0.94rem;
                }

                .booking-field-footer {
                    flex-direction: column;
                    gap: 0;
                }

                .summary-row {
                    flex-direction: column;
                }

                .summary-row strong {
                    max-width: none;
                    text-align: left;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const bookingForm = document.getElementById(
                    'doubtSessionBookingForm'
                );

                const bookingFormCard = document.getElementById(
                    'booking-form'
                );

                const sessionErrorAlert = document.getElementById(
                    'session-error-alert'
                );

                const paymentRequiredAlert = document.getElementById(
                    'payment-required-alert'
                );

                const validationErrorAlert = document.getElementById(
                    'validation-error-alert'
                );

                const submitButton = document.getElementById(
                    'bookingSubmitButton'
                );

                const submitText = document.getElementById(
                    'bookingSubmitText'
                );

                const submitIcon = document.getElementById(
                    'bookingSubmitIcon'
                );

                const doubtField = document.getElementById('doubt');

                const doubtCharacterCount = document.getElementById(
                    'doubtCharacterCount'
                );

                /*
                 * Scroll to the booking form whenever the backend
                 * returns an error or the URL contains its anchor.
                 */
                const shouldScrollToForm =
                    sessionErrorAlert
                    || paymentRequiredAlert
                    || validationErrorAlert
                    || window.location.hash === '#booking-form';

                if (shouldScrollToForm && bookingFormCard) {
                    window.setTimeout(function () {
                        bookingFormCard.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 150);
                }

                /*
                 * Put keyboard focus on the alert for accessibility.
                 */
                const activeAlert =
                    sessionErrorAlert
                    || paymentRequiredAlert
                    || validationErrorAlert;

                if (activeAlert) {
                    activeAlert.setAttribute('tabindex', '-1');

                    window.setTimeout(function () {
                        activeAlert.focus({
                            preventScroll: true
                        });
                    }, 450);
                }

                /*
                 * Live character counter.
                 */
                if (doubtField && doubtCharacterCount) {
                    const updateCharacterCount = function () {
                        doubtCharacterCount.textContent =
                            doubtField.value.length.toLocaleString();
                    };

                    doubtField.addEventListener(
                        'input',
                        updateCharacterCount
                    );

                    updateCharacterCount();
                }

                /*
                 * Prevent duplicate submissions after the browser's
                 * native required-field validation has passed.
                 */
                if (bookingForm && submitButton) {
                    bookingForm.addEventListener('submit', function () {
                        submitButton.disabled = true;
                        submitButton.classList.add('is-loading');

                        if (submitText) {
                            submitText.textContent =
                            @json(
                                $isFree
                                    ? 'Submitting Booking...'
                                    : 'Checking Payment...'
                            );
                        }

                        if (submitIcon) {
                            submitIcon.className =
                                'bi bi-arrow-repeat';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
