<?php

namespace App\Http\Controllers;

use App\Models\Webinar;
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
            'webinar_id' => ['required_if:service,webinar', 'nullable', 'integer', 'exists:webinars,id'],
        ]);

        if (
            $data['service'] === 'doubt_session'
            && (string) get_setting('doubt_session_is_free', '0') === '1'
        ) {
            return response()->json([
                'valid' => false,
                'message' => 'Coupons are available only for paid sessions.',
            ], 422);
        }

        $amount = $data['service'] === 'doubt_session'
            ? max((float) get_setting('doubt_session_price', 0), 0)
            : (float) Webinar::query()->findOrFail($data['webinar_id'])->price;

        try {
            $quote = $coupons->quote(
                $data['code'],
                (int) $request->user()->id,
                $data['service'],
                $amount
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
            'discount_description' => $quote['discount_description'],
            'discount_amount' => $quote['discount_amount'],
            'final_amount' => $quote['final_amount'],
        ]);
    }
}
