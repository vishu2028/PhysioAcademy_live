@extends('layouts.frontend')

@section('title', 'Complete Session Payment')

@section('content')
    <main class="session-payment-page">
        <div class="session-payment-container">
            <div class="session-payment-card">
                <div class="payment-icon">
                    <i class="bi bi-shield-lock-fill"></i>
                </div>

                <span class="payment-eyebrow">
                Secure Razorpay Checkout
            </span>

                <h1>Complete Your Session Payment</h1>

                <p class="payment-intro">
                    Complete the payment to submit your one-on-one
                    doubt session booking. The admin will confirm the
                    session date and time afterward.
                </p>

                @if(session('payment_error'))
                    <div class="payment-alert error">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ session('payment_error') }}</span>
                    </div>
                @endif

                <div
                    id="checkoutMessage"
                    class="payment-alert d-none"
                ></div>

                <div class="payment-reference">
                    <span>Booking Reference</span>
                    <strong>
                        {{ $booking->booking_reference }}
                    </strong>
                </div>

                <div class="payment-details">
                    <div class="payment-detail">
                        <span>Student</span>
                        <strong>
                            {{ $booking->student_name }}
                        </strong>
                    </div>

                    <div class="payment-detail">
                        <span>Topic</span>
                        <strong>
                            {{ $booking->topic }}
                        </strong>
                    </div>

                    <div class="payment-detail">
                        <span>Duration</span>
                        <strong>
                            {{ $booking->duration_minutes }} Minutes
                        </strong>
                    </div>

                    <div class="payment-detail">
                        <span>Amount</span>
                        <strong class="payment-amount">
                            ₹{{ number_format($booking->amount, 2) }}
                        </strong>
                    </div>
                </div>

                <button
                    type="button"
                    id="payNowButton"
                    class="pay-now-button"
                >
                    <i class="bi bi-credit-card-fill"></i>

                    Pay ₹{{ number_format($booking->amount, 2) }}
                </button>

                <a
                    href="{{ route('doubt-sessions.create') }}"
                    class="payment-back-link"
                >
                    Return to Booking Form
                </a>

                <p class="payment-security-note">
                    <i class="bi bi-lock-fill"></i>
                    Payment details are securely processed by Razorpay.
                </p>

                <form
                    id="paymentVerificationForm"
                    action="{{ route(
                    'doubt-sessions.payment.verify',
                    $booking
                ) }}"
                    method="POST"
                    class="d-none"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="razorpay_payment_id"
                        id="razorpayPaymentId"
                    >

                    <input
                        type="hidden"
                        name="razorpay_order_id"
                        id="razorpayOrderId"
                    >

                    <input
                        type="hidden"
                        name="razorpay_signature"
                        id="razorpaySignature"
                    >
                </form>
            </div>
        </div>
    </main>

    @push('styles')
        <style>
            .session-payment-page {
                min-height: calc(100vh - 80px);
                display: grid;
                place-items: center;
                padding: 70px 16px;
                background:
                    radial-gradient(
                        circle at top,
                        rgba(0, 74, 173, 0.12),
                        transparent 38%
                    ),
                    #F7F9FC;
            }

            .session-payment-container {
                width: min(680px, 100%);
            }

            .session-payment-card {
                padding: 46px;
                border: 1px solid rgba(0, 74, 173, 0.12);
                border-radius: 30px;
                background: #FFFFFF;
                box-shadow: 0 30px 70px rgba(15, 23, 42, 0.10);
                text-align: center;
            }

            .payment-icon {
                width: 72px;
                height: 72px;
                display: grid;
                place-items: center;
                margin: 0 auto 18px;
                border-radius: 22px;
                background: #004AAD;
                color: #FFFFFF;
                font-size: 1.7rem;
            }

            .payment-eyebrow {
                display: block;
                margin-bottom: 9px;
                color: #004AAD;
                font-size: 0.75rem;
                font-weight: 800;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .session-payment-card h1 {
                margin: 0 0 14px;
                color: #0F172A;
                font-size: clamp(1.8rem, 4vw, 2.55rem);
                font-weight: 850;
                line-height: 1.2;
            }

            .payment-intro {
                max-width: 560px;
                margin: 0 auto 25px;
                color: #64748B;
                font-size: 0.95rem;
                line-height: 1.7;
            }

            .payment-alert {
                display: flex;
                align-items: flex-start;
                gap: 9px;
                margin-bottom: 20px;
                padding: 14px 16px;
                border-radius: 13px;
                background: rgba(245, 158, 11, 0.12);
                color: #92400E;
                font-size: 0.86rem;
                text-align: left;
            }

            .payment-alert.error {
                background: rgba(220, 38, 38, 0.09);
                color: #B91C1C;
            }

            .payment-alert.success {
                background: rgba(22, 163, 74, 0.10);
                color: #15803D;
            }

            .payment-reference {
                margin: 0 auto 24px;
                padding: 15px 18px;
                border: 1px dashed rgba(0, 74, 173, 0.30);
                border-radius: 14px;
                background: rgba(0, 74, 173, 0.05);
            }

            .payment-reference span {
                display: block;
                margin-bottom: 4px;
                color: #64748B;
                font-size: 0.72rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .payment-reference strong {
                color: #004AAD;
                overflow-wrap: anywhere;
            }

            .payment-details {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                margin-bottom: 25px;
                border: 1px solid #D9D9D9;
                border-radius: 17px;
                overflow: hidden;
                text-align: left;
            }

            .payment-detail {
                padding: 17px 19px;
                border-bottom: 1px solid #D9D9D9;
            }

            .payment-detail:nth-child(odd) {
                border-right: 1px solid #D9D9D9;
            }

            .payment-detail:nth-last-child(-n + 2) {
                border-bottom: 0;
            }

            .payment-detail span {
                display: block;
                margin-bottom: 4px;
                color: #94A3B8;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .payment-detail strong {
                color: #0F172A;
                font-size: 0.88rem;
                overflow-wrap: anywhere;
            }

            .payment-detail .payment-amount {
                color: #004AAD;
                font-size: 1.05rem;
            }

            .pay-now-button {
                width: 100%;
                min-height: 52px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 9px;
                border: 0;
                border-radius: 13px;
                background: #004AAD;
                color: #FFFFFF;
                font: inherit;
                font-weight: 800;
                cursor: pointer;
                transition:
                    transform 0.2s ease,
                    background 0.2s ease;
            }

            .pay-now-button:hover {
                background: #003B8A;
                transform: translateY(-2px);
            }

            .pay-now-button:disabled {
                cursor: wait;
                opacity: 0.7;
                transform: none;
            }

            .payment-back-link {
                display: inline-block;
                margin-top: 17px;
                color: #004AAD;
                font-size: 0.86rem;
                font-weight: 750;
                text-decoration: none;
            }

            .payment-security-note {
                margin: 20px 0 0;
                color: #94A3B8;
                font-size: 0.75rem;
            }

            @media (max-width: 600px) {
                .session-payment-page {
                    padding: 40px 12px;
                }

                .session-payment-card {
                    padding: 32px 18px;
                    border-radius: 24px;
                }

                .payment-details {
                    grid-template-columns: 1fr;
                }

                .payment-detail,
                .payment-detail:nth-child(odd),
                .payment-detail:nth-last-child(-n + 2) {
                    border-right: 0;
                    border-bottom: 1px solid #D9D9D9;
                }

                .payment-detail:last-child {
                    border-bottom: 0;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const payButton = document.getElementById(
                    'payNowButton'
                );

                const messageBox = document.getElementById(
                    'checkoutMessage'
                );

                const verificationForm = document.getElementById(
                    'paymentVerificationForm'
                );

                function showMessage(message, type = 'error') {
                    messageBox.textContent = message;
                    messageBox.className =
                        'payment-alert ' + type;
                }

                function resetButton() {
                    payButton.disabled = false;
                    payButton.innerHTML =
                        '<i class="bi bi-credit-card-fill"></i>' +
                        ' Pay ₹{{ number_format($booking->amount, 2) }}';
                }

                function openCheckout() {
                    if (typeof Razorpay === 'undefined') {
                        showMessage(
                            'Razorpay Checkout could not be loaded. Please refresh the page.'
                        );

                        return;
                    }

                    payButton.disabled = true;
                    payButton.textContent = 'Opening Secure Checkout...';

                    const options = {
                        key: @json($razorpayKeyId),
                        amount: @json($amountSubunits),
                        currency: @json($booking->currency),
                        name: @json($siteName),
                        description: @json($paymentDescription),
                        order_id: @json(
                    $booking->razorpay_order_id
                ),

                        prefill: {
                            name: @json($booking->student_name),
                            email: @json($booking->student_email),
                            contact: @json($booking->phone)
                        },

                        readonly: {
                            name: true,
                            email: true
                        },

                        notes: {
                            booking_reference: @json(
                        $booking->booking_reference
                    )
                        },

                        theme: {
                            color: '#004AAD'
                        },

                        retry: {
                            enabled: true
                        },

                        modal: {
                            confirm_close: true,

                            ondismiss: function () {
                                resetButton();

                                showMessage(
                                    'Payment was not completed. You may try again.',
                                    'error'
                                );
                            }
                        },

                        handler: function (response) {
                            document.getElementById(
                                'razorpayPaymentId'
                            ).value =
                                response.razorpay_payment_id || '';

                            document.getElementById(
                                'razorpayOrderId'
                            ).value =
                                response.razorpay_order_id || '';

                            document.getElementById(
                                'razorpaySignature'
                            ).value =
                                response.razorpay_signature || '';

                            payButton.disabled = true;
                            payButton.textContent =
                                'Verifying Payment...';

                            verificationForm.submit();
                        }
                    };

                    const checkout = new Razorpay(options);

                    checkout.on(
                        'payment.failed',
                        function (response) {
                            resetButton();

                            const description =
                                response.error
                                && response.error.description
                                    ? response.error.description
                                    : 'The payment failed. Please try again.';

                            showMessage(description, 'error');
                        }
                    );

                    checkout.open();
                }

                payButton.addEventListener(
                    'click',
                    openCheckout
                );

                /*
                 * Automatically open Checkout after the page loads.
                 * The Pay button remains available for retry.
                 */
                window.setTimeout(openCheckout, 500);
            });
        </script>
    @endpush
@endsection
