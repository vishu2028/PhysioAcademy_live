@extends('layouts.admin')

@section('title', 'Create Feature')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.features.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Feature</h2>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.features.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title</label>
                        <input type="text" name="title" class="form-control rounded-3" value="{{ old('title') }}" placeholder="e.g. Topic Navigation" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea name="description" class="form-control rounded-3" rows="4" placeholder="e.g. Browse the complete physiotherapy syllabus..." required>{{ old('description') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Icon (Class or SVG)</label>
                            <input type="text" name="icon" class="form-control rounded-3" value="{{ old('icon', 'bi bi-grid') }}" placeholder="bi bi-book or <svg...">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Brand Color (HEX)</label>
                            <input type="color" name="color" class="form-control form-control-color w-100 rounded-3" value="{{ old('color', '#2563eb') }}">
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" name="order" class="form-control rounded-3" value="{{ old('order', 0) }}">
                    </div>
                    
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" {{ old('status', true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="statusSwitch">Active Status</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3 px-5 py-2 fw-bold">Save Feature</button>
                <a href="{{ route('admin.features.index') }}" class="btn btn-light rounded-3 px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
