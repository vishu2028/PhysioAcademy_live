@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <a href="{{ route('admin.coupons.index') }}" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Coupons</a>
    <h1 class="h3 mt-3 mb-4">{{ $coupon->exists ? 'Edit Coupon' : 'Create Coupon' }}</h1>
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <form method="post" action="{{ $coupon->exists ? route('admin.coupons.update', $coupon) : route('admin.coupons.store') }}" class="card border-0 shadow-sm p-4">
        @csrf @if($coupon->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="code">Coupon code</label><input id="code" name="code" class="form-control text-uppercase" value="{{ old('code', $coupon->code) }}" required maxlength="64"></div>
            <div class="col-md-6"><label class="form-label" for="applies_to">Applicable service</label><select id="applies_to" name="applies_to" class="form-select" required>@foreach(['doubt_session'=>'Paid Doubt Sessions','webinar'=>'Paid Webinars','both'=>'Both'] as $value=>$label)<option value="{{ $value }}" @selected(old('applies_to',$coupon->applies_to)===$value)>{{ $label }}</option>@endforeach</select></div>
            <div class="col-md-6"><label class="form-label" for="discount_type">Discount type</label><select id="discount_type" name="discount_type" class="form-select" required><option value="percentage" @selected(old('discount_type',$coupon->discount_type)==='percentage')>Percentage (%)</option><option value="fixed" @selected(old('discount_type',$coupon->discount_type)==='fixed')>Fixed amount (₹)</option></select></div>
            <div class="col-md-6"><label class="form-label" for="discount_value">Discount value</label><input id="discount_value" name="discount_value" class="form-control" type="number" min="0.01" step="0.01" value="{{ old('discount_value',$coupon->discount_value) }}" required></div>
            <div class="col-md-6"><label class="form-label" for="starts_at">Starts at (IST)</label><input id="starts_at" name="starts_at" class="form-control" type="datetime-local" value="{{ old('starts_at',$coupon->starts_at?->timezone('Asia/Kolkata')->format('Y-m-d\TH:i')) }}"></div>
            <div class="col-md-6"><label class="form-label" for="expires_at">Expires at (IST)</label><input id="expires_at" name="expires_at" class="form-control" type="datetime-local" value="{{ old('expires_at',$coupon->expires_at?->timezone('Asia/Kolkata')->format('Y-m-d\TH:i')) }}"></div>
            <div class="col-12"><small class="text-muted">Enter start and expiry times in India Standard Time (IST). Leave blank for no date limit.</small></div>
            <div class="col-md-6"><label class="form-label" for="usage_limit">Total usage limit</label><input id="usage_limit" name="usage_limit" class="form-control" type="number" min="1" value="{{ old('usage_limit',$coupon->usage_limit) }}"></div>
            <div class="col-md-6"><label class="form-label" for="per_user_limit">Per-user usage limit</label><input id="per_user_limit" name="per_user_limit" class="form-control" type="number" min="1" value="{{ old('per_user_limit',$coupon->per_user_limit) }}"></div>
            <div class="col-md-6"><label class="form-label" for="minimum_amount">Minimum booking amount (₹)</label><input id="minimum_amount" name="minimum_amount" class="form-control" type="number" min="0" step="0.01" value="{{ old('minimum_amount',$coupon->minimum_amount) }}"></div>
            <div class="col-md-6"><label class="form-label" for="maximum_discount">Maximum discount (₹)</label><input id="maximum_discount" name="maximum_discount" class="form-control" type="number" min="0.01" step="0.01" value="{{ old('maximum_discount',$coupon->maximum_discount) }}"></div>
            <div class="col-12"><label class="form-label" for="description">Admin description</label><textarea id="description" name="description" class="form-control" maxlength="500">{{ old('description',$coupon->description) }}</textarea></div>
            <div class="col-12 form-check ms-2"><input class="form-check-input" id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active',$coupon->exists ? $coupon->is_active : true))><label class="form-check-label" for="is_active">Active</label></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary">{{ $coupon->exists ? 'Save changes' : 'Create coupon' }}</button></div>
    </form>
</div>
@endsection
