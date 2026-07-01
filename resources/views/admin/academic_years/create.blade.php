@extends('layouts.admin')

@section('title', 'Create Academic Year')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.academic-years.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Years
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Academic Year</h2>
</div>

<form action="{{ route('admin.academic-years.store') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Year Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. First Year" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <input type="text" name="description" class="form-control" placeholder="e.g. Anatomy, Physiology & Foundations" value="{{ old('description') }}">
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold mb-0">Subjects</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addSemester">
                            <i class="bi bi-plus-lg"></i> Add Subject
                        </button>
                    </div>

                    <div id="semesterContainer">
                        <div class="input-group mb-2 semester-item">
                            <input type="text" name="semesters[]" class="form-control" placeholder="e.g. Subject 1" value="Subject 1">
                            <button class="btn btn-outline-danger remove-semester" type="button"><i class="bi bi-trash"></i></button>
                        </div>
                        <div class="input-group mb-2 semester-item">
                            <input type="text" name="semesters[]" class="form-control" placeholder="e.g. Semester 2" value="Subject 2">
                            <button class="btn btn-outline-danger remove-semester" type="button"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold font-monospace small">STATS FOR UI (Optional)</label>
                        <div class="mb-2">
                             <label class="small text-muted">Units Count</label>
                             <input type="number" name="units_count" class="form-control form-control-sm" value="0">
                        </div>
                        <div class="mb-2">
                             <label class="small text-muted">Topics Count</label>
                             <input type="number" name="topics_count" class="form-control form-control-sm" value="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Order</label>
                        <input type="number" name="order" class="form-control" value="0">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="status" checked>
                        <label class="form-check-label" for="status">Enabled</label>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Create Year</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.getElementById('addSemester').addEventListener('click', function() {
        const container = document.getElementById('semesterContainer');
        const count = container.querySelectorAll('.semester-item').length + 1;

        const div = document.createElement('div');
        div.className = 'input-group mb-2 semester-item';

        div.innerHTML = `
        <input type="text" name="semesters[]" class="form-control" placeholder="e.g. Subject ${count}" value="Subject ${count}">
        <button class="btn btn-outline-danger remove-semester" type="button"><i class="bi bi-trash"></i></button>
    `;

        container.appendChild(div);
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-semester')) {
            e.target.closest('.semester-item').remove();
        }
    });
</script>
@endpush
@endsection
