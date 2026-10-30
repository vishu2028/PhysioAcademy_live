@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Coupon Management</h1><p class="text-muted mb-0">Manage discounts for paid sessions and webinars.</p></div>
        <a class="btn btn-primary" href="{{ route('admin.coupons.create') }}"><i class="bi bi-plus-lg me-1"></i>Create Coupon</a>
    </div>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->has('coupon'))<div class="alert alert-warning">{{ $errors->first('coupon') }}</div>@endif
    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-5"><label class="form-label" for="coupon-search">Search</label><input id="coupon-search" class="form-control" name="search" value="{{ request('search') }}" placeholder="Coupon code"></div>
        <div class="col-md-3"><label class="form-label" for="coupon-status">Status</label><select class="form-select" id="coupon-status" name="status"><option value="">All</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option></select></div>
        <div class="col-md-3"><label class="form-label" for="coupon-service">Service</label><select class="form-select" id="coupon-service" name="service"><option value="">All services</option><option value="doubt_session" @selected(request('service')==='doubt_session')>Doubt Sessions</option><option value="webinar" @selected(request('service')==='webinar')>Webinars</option></select></div>
        <div class="col-md-1 d-flex gap-2"><button class="btn btn-primary" type="submit">Filter</button><a class="btn btn-outline-secondary" href="{{ route('admin.coupons.index') }}">Clear</a></div>
    </form>
    <div class="table-responsive bg-white rounded-3 shadow-sm">
        <table class="table align-middle mb-0">
            <thead><tr><th>Code</th><th>Discount</th><th>Applies to</th><th>Validity</th><th>Redemptions</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($coupons as $coupon)
                <tr>
                    <td><strong>{{ $coupon->code }}</strong><div class="small text-muted">{{ $coupon->description }}</div></td>
                    <td>{{ $coupon->discount_type === 'percentage' ? number_format($coupon->discount_value, 2).'%' : '₹'.number_format($coupon->discount_value, 2) }}</td>
                    <td>{{ ucwords(str_replace('_', ' ', $coupon->applies_to)) }}</td>
                    <td>{{ $coupon->starts_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? 'Any time' }} – {{ $coupon->expires_at?->timezone('Asia/Kolkata')->format('d M Y, h:i A') ?? 'No expiry' }} IST</td>
                    <td>{{ $coupon->redeemed_count }}{{ $coupon->usage_limit ? ' / '.$coupon->usage_limit : '' }}</td>
                    <td><span class="badge {{ $coupon->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $coupon->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td><div class="d-flex gap-2"><a class="btn btn-sm btn-outline-primary" href="{{ route('admin.coupons.edit', $coupon) }}">Edit</a>
                        <form method="post" action="{{ route('admin.coupons.toggle', $coupon) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-secondary">{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}</button></form>
                        @if($coupon->redeemed_count===0)<form method="post" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Delete this coupon?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form>@endif
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5"><i class="bi bi-ticket-perforated fs-2 text-muted d-block mb-2"></i><strong>No coupons found</strong><p class="text-muted mb-0">Create a coupon to offer a discount on paid sessions or webinars.</p></td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $coupons->links() }}</div>
</div>
@endsection
