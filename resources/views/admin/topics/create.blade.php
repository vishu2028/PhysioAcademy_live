@extends('layouts.admin')

@section('title', 'Create Topic')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.topics.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Topics
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Topic</h2>
</div>

<form action="{{ route('admin.topics.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="row g-4">
        <div class="col-lg-8">
            <!-- Basic Info -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0">Topic Details</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Topic Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Brachial Plexus" value="{{ old('title') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description / Overview</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Brief introduction to the topic...">{{ old('description') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Module / Unit Number</label>
                            <input type="text" name="module_number" class="form-control" placeholder="e.g. Unit 1 or Module A" value="{{ old('module_number') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Display Order</label>
                            <input type="number" name="order" class="form-control" value="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Learning Materials (LMS Style) -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Learning Materials</h5>
                    <button type="button" class="btn btn-sm btn-primary" id="addMaterial">
                        <i class="bi bi-plus-lg"></i> Add Material
                    </button>
                </div>
                <div class="card-body p-4 pt-0">
                    <div id="materialContainer">
                        <!-- Dynamic materials will be added here -->
                        <div class="text-center py-4 text-muted" id="noMaterialsMsg">
                            <i class="bi bi-box fs-1 d-block mb-2"></i>
                            No materials added yet. Click "Add Material" to start.
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- Categorization -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0">Categorization</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Subject</label>
                        <select name="subject_id" class="form-select" required>
                            <option value="">-- Select Subject --</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject->id }}" {{ old('subject_id') == $subject->id ? 'selected' : '' }}>{{ $subject->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Academic Year</label>
                        <select name="academic_year_id" id="yearSelect" class="form-select" required>
                            <option value="">-- Select Year --</option>
                            @foreach($years as $year)
                                <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>{{ $year->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Semester (Optional)</label>
                        <select name="semester_id" id="semesterSelect" class="form-select">
                            <option value="">-- Select Semester --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Parent Topic (Optional)</label>
                        <select name="parent_id" class="form-select">
                            <option value="">-- None (Core Topic) --</option>
                            @foreach($topics as $t)
                                <option value="{{ $t->id }}">{{ $t->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Settings -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                 <div class="card-body p-4">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="status" id="status" value="1" checked>
                        <label class="form-check-label fw-bold" for="status">Active / Visible</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_protected" id="is_protected" value="1">
                        <label class="form-check-label fw-bold" for="is_protected">Protected Content</label>
                        <div class="small text-muted">Restrict copying/downloading.</div>
                    </div>
                 </div>
                 <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary btn-lg">Create Topic</button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Material Template -->
<template id="materialTemplate">
    <div class="material-item border rounded-3 p-3 mb-3 bg-light position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-material"></button>
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label small fw-bold">Material Title</label>
                <input type="text" name="materials[INDEX][title]" class="form-control form-control-sm" placeholder="e.g. Lecture Note PDF" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Type</label>
                <select name="materials[INDEX][type]" class="form-select form-select-sm material-type-select">
                    <option value="pdf">PDF File</option>
                    <option value="video">Video Embed</option>
                    <option value="link">External Link</option>
                    <option value="note">Text Note</option>
                </select>
            </div>
            <div class="col-12 mt-2 type-fields" data-type="pdf">
                <label class="form-label small fw-bold">Upload PDF</label>
                <input type="file" name="materials[INDEX][file]" class="form-control form-control-sm" accept=".pdf">
            </div>
            <div class="col-12 mt-2 type-fields d-none" data-type="video">
                <label class="form-label small fw-bold">Video URL / Embed Code</label>
                <textarea name="materials[INDEX][content]" class="form-control form-control-sm" rows="2" placeholder="YouTube link or iframe..."></textarea>
            </div>
            <div class="col-12 mt-2 type-fields d-none" data-type="link">
                <label class="form-label small fw-bold">External URL</label>
                <input type="url" name="materials[INDEX][url]" class="form-control form-control-sm" placeholder="https://...">
            </div>
            <div class="col-12 mt-2 type-fields d-none" data-type="note">
                <label class="form-label small fw-bold">Note Content</label>
                <textarea name="materials[INDEX][content]" class="form-control form-control-sm" rows="3" placeholder="Write your notes here..."></textarea>
            </div>
        </div>
    </div>
</template>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    // --- Material JS (registered first, independent of CKEditor) ---
    let materialIndex = 0;
    const yearsData = @json($years);

    // Year-Semester Dynamic Dropdown
    document.getElementById('yearSelect').addEventListener('change', function() {
        const yearId = this.value;
        const semesterSelect = document.getElementById('semesterSelect');
        semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';
        if (yearId) {
            const year = yearsData.find(y => y.id == yearId);
            if (year && year.semesters) {
                year.semesters.forEach(sem => {
                    semesterSelect.innerHTML += `<option value="${sem.id}">${sem.name}</option>`;
                });
            }
        }
    });

    // Add Material
    document.getElementById('addMaterial').addEventListener('click', function() {
        document.getElementById('noMaterialsMsg').classList.add('d-none');
        const container = document.getElementById('materialContainer');
        const template = document.getElementById('materialTemplate').innerHTML;
        const html = template.replace(/INDEX/g, materialIndex);
        const div = document.createElement('div');
        div.innerHTML = html;
        container.appendChild(div.firstElementChild);
        materialIndex++;
    });

    // Remove Material
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-material')) {
            e.target.closest('.material-item').remove();
            if (document.querySelectorAll('.material-item').length === 0) {
                document.getElementById('noMaterialsMsg').classList.remove('d-none');
            }
        }
    });

    // Type Change Logic
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('material-type-select')) {
            const type = e.target.value;
            const parent = e.target.closest('.material-item');
            parent.querySelectorAll('.type-fields').forEach(field => {
                field.classList.toggle('d-none', field.dataset.type !== type);
            });
        }
    });

    // --- CKEditor Init (wrapped in try-catch so failures don't block above JS) ---
    try {
        if (typeof ClassicEditor !== 'undefined') {
            ClassicEditor
                .create(document.querySelector('textarea[name="description"]'), {
                    ckfinder: {
                        uploadUrl: '{{ route("admin.topics.upload_image") }}?_token={{ csrf_token() }}'
                    },
                    toolbar: [
                        'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                        'imageUpload', 'insertTable', 'mediaEmbed', 'undo', 'redo'
                    ]
                })
                .catch(error => console.warn('CKEditor init error:', error));
        }
    } catch (e) {
        console.warn('CKEditor not available:', e);
    }
</script>
@endpush
@endsection
