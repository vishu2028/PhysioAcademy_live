@extends('layouts.admin')

@section('title', 'Help · Support Ticket')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div>
                    <a href="{{ route('admin.help.support-ticket') }}" class="text-decoration-none">&larr; Support Tickets</a>
                    <p class="text-uppercase small fw-bold text-primary mt-4 mb-1">PhysioSource Admin</p>
                    <h1 class="h3 mb-2">{{ $ticket->title }}</h1>
                    <span class="text-secondary">{{ $ticket->rootflash_ticket_id ?: $ticket->source_reference }}</span>
                </div>
                @if($rootFlashTicketsUrl)
                    <a class="btn btn-outline-primary" href="{{ $rootFlashTicketsUrl }}" target="_blank" rel="noopener noreferrer">
                        Open RootFlash Admin <i class="bi bi-box-arrow-up-right ms-1" aria-hidden="true"></i>
                    </a>
                @endif
            </div>

            @if(session('success'))
                <div class="alert alert-success mt-4">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-warning mt-4">{{ session('error') }}</div>
            @endif
            @if($ticket->sync_status === 'synced')
                <div class="alert alert-success mt-4">Synchronization with the RootFlash support desk is confirmed.</div>
            @elseif($ticket->sync_status !== 'synced')
                <div class="alert alert-warning mt-4">
                    This ticket has not synchronized with RootFlash. Its current sync status is {{ $ticket->sync_status }}.
                    <form class="mt-2" method="POST" action="{{ route('admin.help.support-ticket.sync', $ticket) }}">
                        @csrf
                        <button class="btn btn-sm btn-warning" type="submit">Retry ticket synchronization</button>
                    </form>
                </div>
            @endif
            @if($refreshFailed)
                <div class="alert alert-warning">The latest RootFlash status and public replies could not be loaded. Showing the last saved information.</div>
            @endif

            <dl class="row mt-4">
                <dt class="col-sm-3">Category</dt>
                <dd class="col-sm-9">{{ $ticket->category }}</dd>
                <dt class="col-sm-3">Priority</dt>
                <dd class="col-sm-9">{{ ucfirst($ticket->priority) }}</dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ ucwords(str_replace('_', ' ', $ticket->status)) }}</dd>
                <dt class="col-sm-3">Created</dt>
                <dd class="col-sm-9">{{ $ticket->created_at?->format('M d, Y g:i A') }}</dd>
                <dt class="col-sm-3">Last updated</dt>
                <dd class="col-sm-9">{{ $ticket->updated_at?->format('M d, Y g:i A') }}</dd>
                <dt class="col-sm-3">Submitted by</dt>
                <dd class="col-sm-9">{{ $ticket->requester_name }} · {{ $ticket->requester_email }}</dd>
            </dl>

            <section class="border-top pt-4 mt-4">
                <h2 class="h5 fw-bold mb-3">Issue description</h2>
                <div class="text-break" style="white-space: pre-wrap;">{{ $ticket->description }}</div>
                @if($ticket->attachment_paths)
                    <h3 class="h6 mt-4">Attachments</h3>
                    <ul>
                        @foreach($ticket->attachment_paths as $index => $attachment)
                            <li>
                                <a href="{{ route('admin.help.support-ticket.attachments.download', ['ticket' => $ticket, 'attachment' => $index]) }}">
                                    {{ $attachment['name'] }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if($rootFlashTicketAttachment === 'ticket')
                    <a class="d-inline-flex align-items-center gap-2 mt-2" href="{{ route('admin.help.support-ticket.rootflash-attachments.download', ['ticket' => $ticket, 'attachment' => 'ticket']) }}">
                        <i class="bi bi-paperclip" aria-hidden="true"></i> Download RootFlash attachment
                    </a>
                @endif
            </section>

            <section class="border-top pt-4 mt-4">
                <h2 class="h5 fw-bold mb-3">Public conversation</h2>
                @forelse($replies as $reply)
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap small text-secondary mb-2">
                            <strong class="text-dark">{{ $reply->author_name }}</strong>
                            <span>{{ $reply->created_at?->format('M d, Y g:i A') }}</span>
                        </div>
                        <p class="mb-0" style="white-space: pre-wrap;">{{ $reply->body }}</p>
                        @if($reply->attachment_metadata['reference'] ?? false)
                            <a class="small d-inline-flex align-items-center gap-1 mt-2" href="{{ route('admin.help.support-ticket.rootflash-attachments.download', ['ticket' => $ticket, 'attachment' => $reply->attachment_metadata['reference']]) }}">
                                <i class="bi bi-paperclip" aria-hidden="true"></i> Download reply attachment
                            </a>
                        @endif
                    </div>
                @empty
                    <p class="text-secondary">No public replies yet.</p>
                @endforelse
                <p class="small text-secondary mb-0">When synchronized, public replies and status refresh from RootFlash when this page is opened. Replies and status are managed in RootFlash Admin.</p>
            </section>
        </div>
    </div>
</div>
@endsection
