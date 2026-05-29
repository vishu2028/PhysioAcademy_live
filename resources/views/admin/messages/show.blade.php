@extends('layouts.admin')

@section('title', 'View Message')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.messages.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Inbox
    </a>
    <h2 class="fw-bold text-dark mt-2">Message Details</h2>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">{{ $message->subject ?? 'No Subject' }}</h5>
                <span class="small text-muted">{{ $message->created_at->format('M d, Y h:i A') }}</span>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <label class="small text-uppercase fw-bold text-muted letter-spacing-1 mb-2 d-block">Message Content</label>
                    <div class="p-4 bg-light rounded-4 line-height-lg" style="white-space: pre-wrap;">
                        {{ $message->message }}
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="btn btn-primary rounded-3 px-4">
                        <i class="bi bi-reply-fill me-2"></i> Reply by Email
                    </a>
                    <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this message permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger rounded-3 px-4">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Sender Information</h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($message->name) }}&background=4f46e5&color=fff" class="rounded-circle" width="50">
                    <div>
                        <h6 class="fw-bold mb-0">{{ $message->name }}</h6>
                        <small class="text-muted">Inquiry Sender</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="small text-muted d-block">Email Address</label>
                    <a href="mailto:{{ $message->email }}" class="text-decoration-none fw-bold">{{ $message->email }}</a>
                </div>
                
                <div class="mb-0">
                    <label class="small text-muted d-block">Status</label>
                    @if($message->is_read)
                        <span class="text-success small fw-bold"><i class="bi bi-check-all me-1"></i> Marked as Read</span>
                    @else
                        <span class="text-primary small fw-bold"><i class="bi bi-envelope-fill me-1"></i> New Message</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
