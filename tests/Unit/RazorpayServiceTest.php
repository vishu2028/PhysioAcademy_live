<?php

namespace Tests\Unit;

use App\Models\DoubtSessionBooking;
use App\Services\RazorpayService;
use PHPUnit\Framework\TestCase;
use UnexpectedValueException;

class RazorpayServiceTest extends TestCase
{
    public function test_amount_is_converted_to_paise(): void
    {
        $service = new RazorpayService();

        $this->assertSame(
            10000,
            $service->amountToSubunits(100)
        );

        $this->assertSame(
            90000,
            $service->amountToSubunits(900)
        );
    }

    public function test_payment_must_match_booking_order_amount_and_currency(): void
    {
        $service = new RazorpayService();
        $booking = new DoubtSessionBooking([
            'amount' => 100,
            'currency' => 'INR',
            'razorpay_order_id' => 'order_test_123',
        ]);

        $service->assertPaymentMatchesBooking($booking, [
            'order_id' => 'order_test_123',
            'amount' => 10000,
            'currency' => 'INR',
        ]);

        $this->expectException(UnexpectedValueException::class);

        $service->assertPaymentMatchesBooking($booking, [
            'order_id' => 'order_test_123',
            'amount' => 9900,
            'currency' => 'INR',
        ]);
    }

    public function test_payment_with_wrong_currency_is_rejected(): void
    {
        $service = new RazorpayService();
        $booking = new DoubtSessionBooking([
            'amount' => 100,
            'currency' => 'INR',
            'razorpay_order_id' => 'order_test_123',
        ]);

        $this->expectException(UnexpectedValueException::class);

        $service->assertPaymentMatchesBooking($booking, [
            'order_id' => 'order_test_123',
            'amount' => 10000,
            'currency' => 'USD',
        ]);
    }
}
