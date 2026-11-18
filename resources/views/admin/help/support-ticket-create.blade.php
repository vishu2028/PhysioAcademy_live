@extends('layouts.admin')

@section('title', 'Help · Raise Support Ticket')

@section('content')
<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 p-lg-5">
            <a href="{{ route('admin.help.support-ticket') }}" class="text-decoration-none">&larr; Support Tickets</a>
            <div class="d-flex align-items-center gap-3 my-4">
                <i class="bi bi-ticket-perforated fs-2 text-primary"></i>
                <div>
                    <p class="text-uppercase small fw-bold text-primary mb-1">PhysioSource Admin</p>
                    <h1 class="h3 mb-0">Raise Support Ticket</h1>
                </div>
            </div>
            <p class="text-secondary">The ticket is submitted to the existing RootFlash Support Tickets module using the configured PhysioSource website and client mapping.</p>

            @if(session('error'))
                <div class="alert alert-warning" role="alert">{{ session('error') }}</div>
            @endif

            @unless($ticketSchemaReady)
                <div class="alert alert-warning" role="alert">
                    <strong>Ticket submission is unavailable.</strong>
                    The PhysioSource database is missing the support-ticket tables. Ask your deployment administrator to apply the existing support-ticket migrations before submitting a ticket.
                </div>
            @else
            <form method="POST" action="{{ route('admin.help.support-ticket.store') }}" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="source_reference" value="{{ old('source_reference', $sourceReference) }}">
                <div class="mb-3">
                    <label class="form-label" for="title">Subject</label>
                    <input class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" maxlength="255" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6" maxlength="10000" required>{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="category">Category</label>
                        <select class="form-select @error('category') is-invalid @enderror" id="category" name="category" required>
                            <option value="">Choose a category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('category') === $category)>{{ $category }}</option>
                            @endforeach
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="priority">Priority</label>
                        <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            @foreach($priorities as $priority)
                                <option value="{{ $priority }}" @selected(old('priority', 'medium') === $priority)>{{ ucfirst($priority) }}</option>
                            @endforeach
                        </select>
                        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="my-3">
                    <label class="form-label" for="attachments">Attachments (optional, up to 5)</label>
                    <input class="form-control @error('attachments') is-invalid @enderror @error('attachments.*') is-invalid @enderror" id="attachments" name="attachments[]" type="file" accept=".jpg,.jpeg,.png,.webp,.pdf" multiple>
                    <div class="form-text">JPG, PNG, WEBP, or PDF. Maximum 10 MB per file.</div>
                    @error('attachments')<div class="text-danger small">{{ $message }}</div>@enderror
                    @error('attachments.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-primary" type="submit">Submit Ticket</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.help.support-ticket') }}">Cancel</a>
                    @if($rootFlashTicketsUrl)
                        <a class="btn btn-link" href="{{ $rootFlashTicketsUrl }}" target="_blank" rel="noopener noreferrer">Open RootFlash Admin</a>
                    @endif
                </div>
            </form>
            @endunless
        </div>
    </div>
</div>
@endsection
