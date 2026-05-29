@extends('layouts.admin')

@section('title', 'Create Menu')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.menus.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Menus
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Navigation Menu</h2>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.menus.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold">Menu Name</label>
                        <input type="text" name="name" class="form-control form-control-lg rounded-3 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g., Main Header Menu" required>
                        <small class="text-muted">A descriptive name for internal management.</small>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Location Slug</label>
                        <input type="text" name="location" class="form-control rounded-3 @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="e.g., header_menu" required>
                        <div class="mt-2 text-muted x-small">
                            Available slots: <code>header_menu</code>, <code>footer_quick_links</code>, <code>footer_support</code>
                        </div>
                        @error('location') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Active (Visible)</option>
                            <option value="0" {{ old('status') === '0' ? 'selected' : '' }}>Inactive (Hidden)</option>
                        </select>
                    </div>

                    <div class="d-grid mt-5">
                        <button type="submit" class="btn btn-primary rounded-3 py-2 fw-bold">
                            <i class="bi bi-save me-2"></i> Create Menu Container
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="card border-0 bg-primary shadow-sm rounded-4 text-white p-4">
            <h5 class="fw-bold"><i class="bi bi-info-circle-fill me-2"></i> How Menus Work</h5>
            <p class="small opacity-75 mt-3">
                First, create a <strong>Menu Container</strong> (e.g., "Header Menu"). 
                Once created, you will be able to add individual links, dropdowns, and sub-items to it.
            </p>
            <p class="small opacity-75">
                The <strong>Location Slug</strong> tells the website exactly where this menu should appear in the layout.
            </p>
        </div>
    </div>
</div>
@endsection
