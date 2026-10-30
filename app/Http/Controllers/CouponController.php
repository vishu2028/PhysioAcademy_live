<?php

namespace App\Http\Controllers;

use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CouponController extends Controller
{
    public function validateCoupon(Request $request, CouponService $coupons): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:64'],
            'service' => ['required', 'in:doubt_session,webinar'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        try {
            $quote = $coupons->quote(
                $data['code'],
                (int) $request->user()->id,
                $data['service'],
                (float) $data['amount']
            );
        } catch (ValidationException $exception) {
            return response()->json([
                'valid' => false,
                'message' => $exception->errors()['coupon_code'][0] ?? 'Invalid coupon code.',
            ], 422);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Coupon applied successfully.',
            'code' => $quote['coupon']->code,
            'original_amount' => $quote['original_amount'],
            'discount_amount' => $quote['discount_amount'],
            'final_amount' => $quote['final_amount'],
        ]);
    }
}
