<?php

namespace App\Services;

use App\Models\DoubtSessionBooking;
use App\Models\WebinarRegistration;
use Razorpay\Api\Api;
use RuntimeException;
use UnexpectedValueException;

class RazorpayService
{
    /**
     * Confirm whether both Razorpay credentials are configured.
     */
    public function isConfigured(): bool
    {
        return filled($this->configuredValue('key_id'))
            && filled($this->configuredValue('key_secret'));
    }

    /**
     * Return the public Razorpay Key ID.
     *
     * Only the Key ID may be passed to frontend Checkout.
     */
    public function keyId(): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Razorpay credentials are not configured.'
            );
        }

        return $this->configuredValue('key_id');
    }

    /**
     * Convert rupees to paise.
     *
     * Example:
     * ₹250.00 becomes 25000.
     */
    public function amountToSubunits($amount): int
    {
        $subunits = (int) round(((float) $amount) * 100);

        if ($subunits < 1) {
            throw new RuntimeException(
                'The Razorpay amount must be greater than zero.'
            );
        }

        return $subunits;
    }

    /**
     * Create a Razorpay order for a paid booking.
     */
    public function createOrder(
        DoubtSessionBooking $booking
    ): array {
        $amount = $this->amountToSubunits($booking->amount);
        $currency = strtoupper($booking->currency ?: 'INR');

        $order = $this->api()->order->create([
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => substr(
                $booking->booking_reference,
                0,
                40
            ),
            'notes' => [
                'booking_id' => (string) $booking->id,
                'booking_reference' =>
                    $booking->booking_reference,
                'user_id' => (string) $booking->user_id,
            ],
        ]);

        if (empty($order['id'])) {
            throw new RuntimeException(
                'Razorpay did not return an order ID.'
            );
        }

        if ((int) $order['amount'] !== $amount) {
            throw new UnexpectedValueException(
                'Razorpay order amount does not match the booking.'
            );
        }

        if (
            strtoupper((string) ($order['currency'] ?? ''))
            !== $currency
        ) {
            throw new UnexpectedValueException(
                'Razorpay order currency does not match the booking.'
            );
        }

        return [
            'id' => (string) $order['id'],
            'amount' => (int) $order['amount'],
            'currency' => strtoupper(
                (string) $order['currency']
            ),
            'status' => (string) $order['status'],
        ];
    }

    /**
     * Verify the payment signature using the order ID
     * stored on our server.
     */
    public function verifySignature(
        DoubtSessionBooking $booking,
        string $paymentId,
        string $signature
    ): void {
        if (blank($booking->razorpay_order_id)) {
            throw new RuntimeException(
                'The booking does not have a Razorpay order ID.'
            );
        }

        $this->api()->utility->verifyPaymentSignature([
            'razorpay_order_id' =>
                $booking->razorpay_order_id,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);
    }

    public function createWebinarOrder(WebinarRegistration $registration): array
    {
        $amount = $this->amountToSubunits($registration->amount);
        $currency = strtoupper($registration->currency ?: 'INR');
        $order = $this->api()->order->create([
            'amount'=>$amount, 'currency'=>$currency,
            'receipt'=>substr($registration->registration_reference, 0, 40),
            'notes'=>['registration_id'=>(string)$registration->id, 'webinar_id'=>(string)$registration->webinar_id],
        ]);
        if (empty($order['id']) || (int)$order['amount'] !== $amount || strtoupper((string)$order['currency']) !== $currency) {
            throw new UnexpectedValueException('Razorpay webinar order does not match the registration.');
        }
        return ['id'=>(string)$order['id'], 'amount'=>(int)$order['amount'], 'currency'=>$currency, 'status'=>(string)$order['status']];
    }

    public function verifyWebinarSignature(WebinarRegistration $registration, string $paymentId, string $signature): void
    {
        if (blank($registration->razorpay_order_id)) throw new RuntimeException('The registration does not have a Razorpay order ID.');
        $this->api()->utility->verifyPaymentSignature(['razorpay_order_id'=>$registration->razorpay_order_id,'razorpay_payment_id'=>$paymentId,'razorpay_signature'=>$signature]);
    }

    public function assertPaymentMatchesWebinar(WebinarRegistration $registration, array $payment): void
    {
        if (!hash_equals((string)$registration->razorpay_order_id, (string)($payment['order_id'] ?? ''))
            || (int)($payment['amount'] ?? 0) !== $this->amountToSubunits($registration->amount)
            || strtoupper((string)($payment['currency'] ?? '')) !== strtoupper($registration->currency ?: 'INR')) {
            throw new UnexpectedValueException('The Razorpay payment does not match the registration.');
        }
    }

    /**
     * Fetch payment details directly from Razorpay.
     */
    public function fetchPayment(string $paymentId): array
    {
        $payment = $this->api()
            ->payment
            ->fetch($paymentId);

        return [
            'id' => (string) $payment['id'],
            'order_id' => (string) (
                $payment['order_id'] ?? ''
            ),
            'amount' => (int) $payment['amount'],
            'currency' => strtoupper(
                (string) $payment['currency']
            ),
            'status' => strtolower(
                (string) $payment['status']
            ),
        ];
    }

    /**
     * Ensure the fetched Razorpay payment belongs
     * to this exact booking.
     */
    public function assertPaymentMatchesBooking(
        DoubtSessionBooking $booking,
        array $payment
    ): void {
        $expectedOrderId =
            (string) $booking->razorpay_order_id;

        $receivedOrderId =
            (string) ($payment['order_id'] ?? '');

        if (
            blank($expectedOrderId)
            || blank($receivedOrderId)
            || ! hash_equals(
                $expectedOrderId,
                $receivedOrderId
            )
        ) {
            throw new UnexpectedValueException(
                'The Razorpay payment order does not match the booking.'
            );
        }

        $expectedAmount = $this->amountToSubunits(
            $booking->amount
        );

        if (
            (int) ($payment['amount'] ?? 0)
            !== $expectedAmount
        ) {
            throw new UnexpectedValueException(
                'The Razorpay payment amount does not match the booking.'
            );
        }

        $expectedCurrency = strtoupper(
            $booking->currency ?: 'INR'
        );

        $receivedCurrency = strtoupper(
            (string) ($payment['currency'] ?? '')
        );

        if ($receivedCurrency !== $expectedCurrency) {
            throw new UnexpectedValueException(
                'The Razorpay payment currency does not match the booking.'
            );
        }
    }

    /**
     * Build an authenticated Razorpay API client.
     */
    private function api(): Api
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException(
                'Razorpay credentials are not configured.'
            );
        }

        return new Api(
            $this->configuredValue('key_id'),
            $this->configuredValue('key_secret')
        );
    }

    /**
     * Normalize environment values before using them.
     *
     * This also prevents accidental whitespace in .env values from being
     * sent to Razorpay or exposed as the public Checkout key.
     */
    private function configuredValue(string $key): string
    {
        return trim((string) config("services.razorpay.{$key}"));
    }
}
