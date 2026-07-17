<?php

namespace App\Services;

use App\Models\DoubtSessionBooking;
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
        return filled(config('services.razorpay.key_id'))
            && filled(config('services.razorpay.key_secret'));
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

        return (string) config('services.razorpay.key_id');
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
            (string) config('services.razorpay.key_id'),
            (string) config('services.razorpay.key_secret')
        );
    }
}
