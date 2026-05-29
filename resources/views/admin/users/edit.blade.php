@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Users
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit User: {{ $user->name }}</h2>
</div>

<form action="{{ route('admin.users.update', $user) }}" method="POST">
    @csrf
    @method('PATCH')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    </div>
                    
                    <div class="alert alert-info border-0 rounded-4 mt-4">
                        <i class="bi bi-info-circle me-2"></i> Leave password fields empty to keep the current password.
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">New Password (Optional)</label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">User Role</label>
                        <select name="role" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $user->hasRole($role->name) ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Update User</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection
