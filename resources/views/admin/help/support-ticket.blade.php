@extends('layouts.admin')

@section('title', 'Help · Support Ticket')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-ticket-perforated fs-2 text-primary"></i>
                    <div>
                        <p class="text-uppercase small fw-bold text-primary mb-1">Help</p>
                        <h1 class="h3 mb-0">Support Tickets</h1>
                    </div>
                </div>
                @if($ticketSchemaReady)
                    <a class="btn btn-primary" href="{{ route('admin.help.support-ticket.create') }}">
                        <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> Raise Support Ticket
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-warning">{{ session('error') }}</div>
            @endif

            @unless($ticketSchemaReady)
                <div class="alert alert-warning mb-0" role="alert">
                    <strong>Support ticket storage is not installed.</strong>
                    The PhysioSource database is missing the support-ticket tables, so ticket history and submission are unavailable.
                    Ask your deployment administrator to apply the existing support-ticket migrations. No ticket data was changed.
                </div>
            @else
            @if($rootFlashTicketsUrl)
                <a class="btn btn-outline-primary btn-sm mb-4" href="{{ $rootFlashTicketsUrl }}" target="_blank" rel="noopener noreferrer">
                    Open RootFlash Support Tickets <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
                </a>
            @else
                <div class="alert alert-info mb-4">
                    RootFlash Admin link is not configured. Set <code>ROOTFLASH_ADMIN_URL</code> on the PhysioSource server.
                    Ticket submission and status viewing remain available here.
                </div>
            @endif

            <form method="GET" action="{{ route('admin.help.support-ticket') }}" class="row g-2 align-items-end mb-4">
                <div class="col-sm-8 col-md-5">
                    <label class="form-label" for="status">Filter by status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All statuses</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>
                                {{ ucwords(str_replace('_', ' ', $status)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto">
                    <button class="btn btn-outline-primary" type="submit">Apply filter</button>
                </div>
                @if($selectedStatus)
                    <div class="col-auto">
                        <a class="btn btn-link" href="{{ route('admin.help.support-ticket') }}">Clear</a>
                    </div>
                @endif
            </form>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Ticket reference</th>
                            <th>Subject</th>
                            <th>Category</th>
                            <th>Created</th>
                            <th>Last updated</th>
                            <th>Status</th>
                            <th>Latest public reply</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $ticket)
                            <tr>
                                <td class="text-nowrap">
                                    <a href="{{ route('admin.help.support-ticket.show', $ticket) }}">
                                        {{ $ticket->rootflash_ticket_id ?: $ticket->source_reference }}
                                    </a>
                                </td>
                                <td>{{ $ticket->title }}</td>
                                <td>{{ $ticket->category }}</td>
                                <td class="text-nowrap">{{ $ticket->created_at?->format('M d, Y') }}</td>
                                <td class="text-nowrap">{{ $ticket->updated_at?->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge rounded-pill text-bg-secondary">
                                        {{ ucwords(str_replace('_', ' ', $ticket->status)) }}
                                    </span>
                                    @if($ticket->sync_status !== 'synced')
                                        <span class="badge rounded-pill text-bg-warning">
                                            {{ $ticket->sync_status === 'failed' ? 'Sync failed' : 'Sync pending' }}
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($ticket->latestPublicReply)
                                        {{ $ticket->latestPublicReply->source === 'rootflash' ? 'Support replied' : 'Website replied' }}
                                        <span class="d-block small text-secondary">{{ $ticket->latestPublicReply->created_at?->diffForHumans() }}</span>
                                    @else
                                        <span class="text-secondary">No public replies</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-secondary py-4">
                                    {{ $selectedStatus ? 'No tickets match this status.' : 'No PhysioSource support tickets have been submitted yet.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center">{{ $tickets->links() }}</div>
            @endunless
        </div>
    </div>
</div>
@endsection
