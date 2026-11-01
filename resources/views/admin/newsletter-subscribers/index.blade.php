@extends('layouts.admin')

@section('title', 'Newsletter Subscribers')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold text-dark">Newsletter Subscribers</h2>
    <p class="text-secondary mb-0">Manage PhysioSource newsletter subscriptions.</p>
</div>

@if(session('success'))
    <div class="alert alert-success rounded-3">{{ session('success') }}</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.newsletter-subscribers.index') }}" class="row g-3 align-items-end mb-4">
            <div class="col-12 col-md-6">
                <label for="subscriberSearch" class="form-label fw-semibold">Search email address</label>
                <input
                    id="subscriberSearch"
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    class="form-control"
                    placeholder="Search by email"
                >
            </div>
            <div class="col-12 col-md-3">
                <label for="subscriberStatus" class="form-label fw-semibold">Status</label>
                <select id="subscriberStatus" name="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="subscribed" @selected(request('status') === 'subscribed')>Subscribed</option>
                    <option value="unsubscribed" @selected(request('status') === 'unsubscribed')>Unsubscribed</option>
                </select>
            </div>
            <div class="col-12 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('admin.newsletter-subscribers.index') }}" class="btn btn-outline-secondary">Clear</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col">S. No.</th>
                        <th scope="col">Email Address</th>
                        <th scope="col">Subscription Date</th>
                        <th scope="col">Subscription Time</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $subscriber)
                        <tr>
                            <td>{{ $subscribers->firstItem() + $loop->index }}</td>
                            <td class="text-break">{{ $subscriber->email }}</td>
                            <td>{{ $subscriber->subscribed_at->format('M d, Y') }}</td>
                            <td>{{ $subscriber->subscribed_at->format('h:i:s A') }}</td>
                            <td>
                                @if($subscriber->isSubscribed())
                                    <span class="badge rounded-pill bg-success-subtle text-success">Subscribed</span>
                                @else
                                    <span class="badge rounded-pill bg-secondary-subtle text-secondary">Unsubscribed</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a
                                        href="{{ route('admin.newsletter-subscribers.show', array_merge(['newsletter_subscriber' => $subscriber], request()->query())) }}"
                                        class="btn btn-light btn-sm rounded-3"
                                        aria-label="View subscriber"
                                    ><i class="bi bi-eye"></i></a>
                                    <form
                                        action="{{ route('admin.newsletter-subscribers.destroy', array_merge(['newsletter_subscriber' => $subscriber], request()->query())) }}"
                                        method="POST"
                                        onsubmit="return confirm('Delete this newsletter subscriber?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" aria-label="Delete subscriber">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">No newsletter subscribers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($subscribers->hasPages())
            <div class="mt-3">{{ $subscribers->links() }}</div>
        @endif
    </div>
</div>
@endsection
