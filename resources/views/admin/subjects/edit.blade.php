@extends('layouts.admin')

@section('title', 'Edit Subject')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Subjects
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit Subject: {{ $subject->name }}</h2>
</div>

<form action="{{ route('admin.subjects.update', $subject) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $subject->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Code</label>
                        <input
                            type="text"
                            name="code"
                            class="form-control @error('code') is-invalid @enderror"
                            placeholder="e.g. BPT-401"
                            value="{{ old('code', $subject->code) }}"
                        >

                        @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="5">{{ old('description', $subject->description) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Icon (Emoji or SVG string)</label>
                            <input type="text" name="icon" class="form-control" value="{{ old('icon', $subject->icon) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Display Image (Optional)</label>
                            @if($subject->image)
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $subject->image) }}" class="rounded" width="100">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Order</label>
                        <input type="number" name="order" class="form-control" value="{{ old('order', $subject->order) }}">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="status" {{ $subject->status ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Enabled</label>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Update Subject</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
