<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->filled('search'), fn ($query) => $query->where('code', 'like', '%'.mb_strtoupper($request->string('search')).'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->status === 'active'))
            ->when($request->filled('service'), fn ($query) => $query->whereIn('applies_to', [$request->service, Coupon::SERVICE_BOTH]))
            ->withCount(['redemptions as redeemed_count' => fn ($query) => $query->where('status', 'redeemed')])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.coupons.index', compact('coupons'));
    }

    public function create()
    {
        return view('admin.coupons.form', ['coupon' => new Coupon()]);
    }

    public function store(Request $request)
    {
        Coupon::create($this->validated($request));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon)
    {
        return view('admin.coupons.form', compact('coupon'));
    }

    public function update(Request $request, Coupon $coupon)
    {
        $coupon->update($this->validated($request, $coupon));

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated.');
    }

    public function toggle(Coupon $coupon)
    {
        $coupon->update(['is_active' => ! $coupon->is_active]);

        return back()->with('success', 'Coupon status updated.');
    }

    public function destroy(Coupon $coupon)
    {
        if ($coupon->redemptions()->exists()) {
            return back()->withErrors(['coupon' => 'Coupons with redemption history cannot be deleted. Deactivate the coupon instead.']);
        }

        $coupon->delete();

        return back()->with('success', 'Coupon deleted.');
    }

    private function validated(Request $request, ?Coupon $coupon = null): array
    {
        $data = $request->validate([
            'code' => [
                'required', 'string', 'max:64',
            ],
            'discount_type' => ['required', Rule::in([Coupon::TYPE_PERCENTAGE, Coupon::TYPE_FIXED])],
            'discount_value' => ['required', 'numeric', 'gt:0'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date', 'after:starts_at'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_user_limit' => ['nullable', 'integer', 'min:1'],
            'minimum_amount' => ['nullable', 'numeric', 'min:0'],
            'maximum_discount' => ['nullable', 'numeric', 'gt:0'],
            'applies_to' => ['required', Rule::in([Coupon::SERVICE_DOUBT_SESSION, Coupon::SERVICE_WEBINAR, Coupon::SERVICE_BOTH])],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $duplicateCode = Coupon::query()
            ->whereRaw('LOWER(code) = ?', [mb_strtolower(trim($data['code']))])
            ->when($coupon, fn ($query) => $query->where('id', '<>', $coupon->id))
            ->exists();
        if ($duplicateCode) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'code' => 'This coupon code has already been used.',
            ]);
        }

        if ($data['discount_type'] === Coupon::TYPE_PERCENTAGE && (float) $data['discount_value'] > 100) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'discount_value' => 'Percentage discounts cannot exceed 100%.',
            ]);
        }

        if ($data['starts_at'] && $data['expires_at'] && $data['expires_at'] <= $data['starts_at']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'expires_at' => 'Expiry must be after the start date.',
            ]);
        }

        foreach (['starts_at', 'expires_at'] as $dateField) {
            $data[$dateField] = filled($data[$dateField])
                ? CarbonImmutable::parse($data[$dateField], 'Asia/Kolkata')->utc()
                : null;
        }

        $data['code'] = mb_strtoupper(trim($data['code']));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
