@extends('layouts.admin')

@section('title', 'Edit Section Item')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.page-sections.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sections
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit Item for "{{ $section->name }}"</h2>
</div>

<form action="{{ route('admin.page-section-items.update', $item) }}" method="POST">
    @csrf @method('PUT')

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <div class="mb-3">
                <label class="form-label fw-bold">Title</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $item->title) }}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Body (HTML allowed)</label>
                <textarea name="body" class="form-control" rows="6">{{ old('body', $item->body) }}</textarea>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Meta (JSON)</label>
                <textarea name="meta" class="form-control font-monospace" rows="4">{{ old('meta', json_encode($item->meta, JSON_PRETTY_PRINT)) }}</textarea>
            </div>
        </div>
        <div class="card-footer bg-light p-3 d-grid">
            <button class="btn btn-primary">Save Item</button>
        </div>
    </div>
</form>

@endsection
