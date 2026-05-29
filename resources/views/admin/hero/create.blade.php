@extends('layouts.admin')

@section('title', 'Create Hero')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.hero.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Hero Section</h2>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.hero.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title') }}" placeholder="e.g. Your Academic Guide for Physiotherapy" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Subtitle</label>
                        <textarea name="subtitle" class="form-control rounded-3" rows="3" placeholder="e.g. Navigate your syllabus, understand important topics...">{{ old('subtitle') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Primary Button Text</label>
                            <input type="text" name="button_text" class="form-control rounded-3" value="{{ old('button_text', 'Explore Topics') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Primary Button URL</label>
                            <input type="text" name="button_url" class="form-control rounded-3" value="{{ old('button_url', '#') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Badge Text</label>
                            <input type="text" name="badge" class="form-control rounded-3" value="{{ old('badge', 'New Curriculum 2024') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="statusSwitch">Active Status</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Hero Image</label>
                        <input type="file" name="image" class="form-control rounded-3">
                        <small class="text-muted">Recommended size: 1200x800px. Max 5MB.</small>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3 px-5 py-2 fw-bold">Save Hero Section</button>
                <a href="{{ route('admin.hero.index') }}" class="btn btn-light rounded-3 px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
