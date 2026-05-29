@extends('layouts.admin')

@section('title', 'Edit Academic Year')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.academic-years.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Years
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit Year: {{ $academicYear->name }}</h2>
</div>

<form action="{{ route('admin.academic-years.update', $academicYear) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Year Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $academicYear->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ old('description', $academicYear->description) }}">
                    </div>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <label class="form-label fw-bold mb-0">Semesters</label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addSemester">
                            <i class="bi bi-plus-lg"></i> Add Semester
                        </button>
                    </div>
                    
                    <div id="semesterContainer">
                        @foreach($academicYear->semesters as $index => $semester)
                        <div class="input-group mb-2 semester-item">
                            <input type="hidden" name="semesters[{{ $index }}][id]" value="{{ $semester->id }}">
                            <input type="text" name="semesters[{{ $index }}][name]" class="form-control" value="{{ $semester->name }}">
                            <button class="btn btn-outline-danger remove-semester" type="button"><i class="bi bi-trash"></i></button>
                        </div>
                        @endforeach
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
                             <input type="number" name="units_count" class="form-control form-control-sm" value="{{ $academicYear->units_count }}">
                        </div>
                        <div class="mb-2">
                             <label class="small text-muted">Topics Count</label>
                             <input type="number" name="topics_count" class="form-control form-control-sm" value="{{ $academicYear->topics_count }}">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Order</label>
                        <input type="number" name="order" class="form-control" value="{{ $academicYear->order }}">
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="status" {{ $academicYear->status ? 'checked' : '' }}>
                        <label class="form-check-label" for="status">Enabled</label>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Update Year</button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
    let semCount = {{ $academicYear->semesters->count() }};
    document.getElementById('addSemester').addEventListener('click', function() {
        const container = document.getElementById('semesterContainer');
        const count = container.querySelectorAll('.semester-item').length + 1;
        const div = document.createElement('div');
        div.className = 'input-group mb-2 semester-item';
        div.innerHTML = `
            <input type="text" name="semesters[new_${semCount}][name]" class="form-control" placeholder="e.g. Semester ${count}" value="Semester ${count}">
            <button class="btn btn-outline-danger remove-semester" type="button"><i class="bi bi-trash"></i></button>
        `;
        container.appendChild(div);
        semCount++;
    });

    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-semester')) {
            e.target.closest('.semester-item').remove();
        }
    });
</script>
@endpush
@endsection
