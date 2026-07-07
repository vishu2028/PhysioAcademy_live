@extends('layouts.admin')

@section('title', 'Create Section')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.page-sections.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sections
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Section</h2>
</div>

<form action="{{ route('admin.page-sections.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Section Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug') }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Section Type</label>
                        <select name="type" class="form-select select2" required>
                            <option value="">-- Select Type --</option>
                            <option value="hero">Hero Section</option>
                            <option value="mission">Mission Section</option>
                            <option value="vision">Vision Section</option>
                            <option value="closing">Closing Banner</option>
                            <option value="feature-grid">Feature Grid</option>
                            <option value="explore-grid">Explore Grid</option>
                            <option value="split-section">Split Section (Text + Media)</option>
                            <option value="contact-info">Contact Info</option>
                            <option value="social-links">Social Links</option>
                            <option value="empty-state">Empty State</option>
                            <option value="guest-state">Guest State</option>
                            <option value="exam_hero" {{ old('type', $section->type ?? '') == 'exam_hero' ? 'selected' : '' }}>
                                Exam Hero
                            </option>

                            <option value="exam_filters" {{ old('type', $section->type ?? '') == 'exam_filters' ? 'selected' : '' }}>
                                Exam Filters
                            </option>

                            <option value="exam_resources" {{ old('type', $section->type ?? '') == 'exam_resources' ? 'selected' : '' }}>
                                Exam Resources
                            </option>
                        </select>
                        <div class="small text-muted mt-1">Selecting a type determines the available management fields after creation.</div>
                    </div>

                    <div class="alert alert-light border border-dashed rounded-4 p-4 text-center">
                        <i class="bi bi-info-circle fs-2 text-primary mb-2 d-block"></i>
                        <p class="mb-0 fw-bold">Step 1: Define Section Basics</p>
                        <p class="text-muted small">After saving this section, you will be able to manage its specific content (titles, text, images) using a user-friendly form.</p>
                    </div>
                    <textarea name="content" class="d-none"></textarea>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Attach to Page</label>
                        <select name="page_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($pages as $p)
                                <option value="{{ $p->id }}">{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Order</label>
                        <input type="number" name="order" class="form-control" value="0">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="enabled" checked>
                        <label class="form-check-label" for="enabled">Enabled</label>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Save Section</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
