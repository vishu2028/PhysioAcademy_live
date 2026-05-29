@extends('layouts.admin')

@section('title', 'Edit Menu')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.menus.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Menus
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit Menu: {{ $menu->name }}</h2>
</div>

<div class="row">
    <div class="col-lg-5">
        <!-- Menu Metadata Settings -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0">Menu Container Settings</h6>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Menu Name</label>
                        <input type="text" name="name" class="form-control rounded-3 @error('name') is-invalid @enderror" value="{{ old('name', $menu->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Location Slug</label>
                        <input type="text" name="location" class="form-control rounded-3 bg-light @error('location') is-invalid @enderror" value="{{ old('location', $menu->location) }}" readonly>
                        <small class="text-muted">Location slugs are locked after creation to maintain layout stability.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small">Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="1" {{ old('status', $menu->status) == 1 ? 'selected' : '' }}>Active (Visible)</option>
                            <option value="0" {{ old('status', $menu->status) == 0 ? 'selected' : '' }}>Inactive (Hidden)</option>
                        </select>
                    </div>

                    <div class="d-grid pt-3">
                        <button type="submit" class="btn btn-primary rounded-3 fw-bold">
                            Update Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <!-- Menu Items Management (Placeholder for now, or just links to items) -->
        <div class="card border-0 shadow-sm rounded-4 text-center p-5">
            <div class="py-4">
                <i class="bi bi-list-ul display-1 text-primary-subtle"></i>
                <h4 class="fw-bold mt-4">Manage Menu Items</h4>
                <p class="text-secondary mx-auto" style="max-width: 400px;">
                    This container has <strong>{{ $menu->items->count() }}</strong> direct links. 
                    Manage the actual navigation tree, labels, and hierarchy in the Menu Items manager.
                </p>
                <a href="#" class="btn btn-outline-primary rounded-pill px-4 fw-bold mt-3 disabled">
                    <i class="bi bi-arrow-right-circle me-2"></i> Manage Links (Coming Soon)
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
