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
                        <label class="form-label fw-bold">Type</label>
                        <input type="text" name="type" class="form-control" value="{{ old('type') }}" placeholder="e.g. mission, features, explore, vision, closing">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content (JSON or HTML)</label>
                        <textarea name="content" class="form-control" rows="8">{{ old('content') }}</textarea>
                    </div>
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
