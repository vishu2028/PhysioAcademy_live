@extends('layouts.admin')

@section('title', 'Create Subject')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.subjects.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Subjects
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Subject</h2>
</div>

<form action="{{ route('admin.subjects.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Anatomy" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Brief overview of this subject...">{{ old('description') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Icon (Emoji or SVG string)</label>
                            <input type="text" name="icon" class="form-control" placeholder="e.g. 📚" value="{{ old('icon') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Display Image (Optional)</label>
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
                        <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                        <label class="form-check-label" for="status">Enabled</label>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Create Subject</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
