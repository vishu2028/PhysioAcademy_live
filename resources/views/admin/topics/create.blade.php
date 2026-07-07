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
                            <label class="form-label fw-bold">Description / Overview</label>

                            <textarea
                                name="description"
                                class="form-control @error('description') is-invalid @enderror"
                                rows="4"
                                placeholder="Brief introduction to the topic..."
                            >{{ old('description') }}</textarea>

                            @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Module / Unit Number</label>

                                <input
                                    type="text"
                                    name="module_number"
                                    class="form-control @error('module_number') is-invalid @enderror"
                                    placeholder="e.g. Unit 1 or Module A"
                                    value="{{ old('module_number') }}"
                                >

                                @error('module_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Display Order</label>

                                <input
                                    type="number"
                                    name="order"
                                    class="form-control @error('order') is-invalid @enderror"
                                    value="{{ old('order', 0) }}"
                                    min="0"
                                >

                                @error('order')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Learning Materials -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0">Learning Materials</h5>

                        <button type="button" class="btn btn-sm btn-primary" id="addMaterial">
                            <i class="bi bi-plus-lg"></i> Add Material
                        </button>
                    </div>

                    <div class="card-body p-4 pt-0">
                        <div id="materialContainer">
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
                        <!-- Subject -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Subject</label>

                            <select
                                name="subject_id"
                                id="subjectSelect"
                                class="form-select @error('subject_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Subject --</option>

                                @foreach($subjects as $subject)
                                    <option value="{{ $subject->id }}" {{ old('subject_id', $topic->subject_id ?? '') == $subject->id ? 'selected' : '' }}>
                                        {{ $subject->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('subject_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Unit -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Unit</label>

                            <select
                                name="unit_id"
                                id="unitSelect"
                                class="form-select @error('unit_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Unit --</option>

                                @foreach($units as $unit)
                                    <option
                                        value="{{ $unit->id }}"
                                        {{ old('unit_id', $selectedUnitId ?? '') == $unit->id ? 'selected' : '' }}
                                    >
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('unit_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Topic from unit_topics table -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Topic</label>

                            <select
                                name="unit_topic_id"
                                id="unitTopicSelect"
                                class="form-select @error('unit_topic_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Topic --</option>

                                @foreach($unitTopics as $unitTopic)
                                    <option
                                        value="{{ $unitTopic->id }}"
                                        {{ old('unit_topic_id', $selectedUnitTopicId ?? '') == $unitTopic->id ? 'selected' : '' }}
                                    >
                                        {{ $unitTopic->title }}
                                    </option>
                                @endforeach
                            </select>

                            @error('unit_topic_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Parent Topic -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Parent Topic (Optional)</label>

                            <select
                                name="parent_topic_id"
                                id="parentTopicSelect"
                                class="form-select @error('parent_topic_id') is-invalid @enderror"
                            >
                                <option value="">-- None (Core Topic) --</option>

                                @foreach($parentTopics as $parentTopic)
                                    <option value="{{ $parentTopic->id }}" {{ old('parent_topic_id') == $parentTopic->id ? 'selected' : '' }}>
                                        {{ $parentTopic->title }}
                                    </option>
                                @endforeach
                            </select>

                            @error('parent_topic_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Academic Year -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Academic Year</label>

                            <select
                                name="academic_year_id"
                                id="yearSelect"
                                class="form-select @error('academic_year_id') is-invalid @enderror"
                                required
                            >
                                <option value="">-- Select Year --</option>

                                @foreach($years as $year)
                                    <option value="{{ $year->id }}" {{ old('academic_year_id') == $year->id ? 'selected' : '' }}>
                                        {{ $year->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('academic_year_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Semester -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Semester (Optional)</label>

                            <select
                                name="semester_id"
                                id="semesterSelect"
                                class="form-select @error('semester_id') is-invalid @enderror"
                            >
                                <option value="">-- Select Semester --</option>
                            </select>

                            @error('semester_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Settings -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="status"
                                id="status"
                                value="1"
                                {{ old('status', 1) ? 'checked' : '' }}
                            >

                            <label class="form-check-label fw-bold" for="status">
                                Active / Visible
                            </label>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_protected"
                                id="is_protected"
                                value="1"
                                {{ old('is_protected') ? 'checked' : '' }}
                            >

                            <label class="form-check-label fw-bold" for="is_protected">
                                Protected Content
                            </label>

                            <div class="small text-muted">
                                Restrict copying/downloading.
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-light p-3 d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            Create Topic
                        </button>
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
                    <input
                        type="text"
                        name="materials[INDEX][title]"
                        class="form-control form-control-sm"
                        placeholder="e.g. Lecture Note PDF"
                        required
                    >
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
                    <textarea
                        name="materials[INDEX][content]"
                        class="form-control form-control-sm"
                        rows="2"
                        placeholder="YouTube link or iframe..."
                    ></textarea>
                </div>

                <div class="col-12 mt-2 type-fields d-none" data-type="link">
                    <label class="form-label small fw-bold">External URL</label>
                    <input
                        type="url"
                        name="materials[INDEX][url]"
                        class="form-control form-control-sm"
                        placeholder="https://..."
                    >
                </div>

                <div class="col-12 mt-2 type-fields d-none" data-type="note">
                    <label class="form-label small fw-bold">Note Content</label>
                    <textarea
                        name="materials[INDEX][content]"
                        class="form-control form-control-sm"
                        rows="3"
                        placeholder="Write your notes here..."
                    ></textarea>
                </div>
            </div>
        </div>
    </template>
@endsection
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

    <script>
        $(document).ready(function () {
            // --- Elements ---
            const subjectSelect = document.getElementById('subjectSelect');
            const unitSelect = document.getElementById('unitSelect');
            const unitTopicSelect = document.getElementById('unitTopicSelect');
            const parentTopicSelect = document.getElementById('parentTopicSelect');
            const yearSelect = document.getElementById('yearSelect');
            const semesterSelect = document.getElementById('semesterSelect');
            const addMaterialButton = document.getElementById('addMaterial');
            const materialContainer = document.getElementById('materialContainer');
            const materialTemplate = document.getElementById('materialTemplate');
            const noMaterialsMsg = document.getElementById('noMaterialsMsg');

            // --- Old values after validation error ---
            const oldSubjectId = "{{ old('subject_id') }}";
            const oldUnitId = "{{ old('unit_id') }}";
            const oldUnitTopicId = "{{ old('unit_topic_id') }}";
            const oldParentTopicId = "{{ old('parent_topic_id') }}";
            const oldAcademicYearId = "{{ old('academic_year_id') }}";
            const oldSemesterId = "{{ old('semester_id') }}";

            // --- Data ---
            const yearsData = @json($years);
            let materialIndex = 0;

            // --- Select2 Init ---
            $('#subjectSelect').select2({
                placeholder: '-- Select Subject --',
                allowClear: true,
                width: '100%'
            });

            $('#unitSelect').select2({
                placeholder: '-- Select Unit --',
                allowClear: true,
                width: '100%'
            });

            $('#unitTopicSelect').select2({
                placeholder: '-- Select Topic --',
                allowClear: true,
                width: '100%'
            });

            if ($('#parentTopicSelect').length) {
                $('#parentTopicSelect').select2({
                    placeholder: '-- None (Core Topic) --',
                    allowClear: true,
                    width: '100%'
                });
            }

            // --- Helpers ---
            function refreshSelect2(selectElement) {
                if (selectElement) {
                    $(selectElement).trigger('change.select2');
                }
            }

            function resetUnits() {
                if (unitSelect) {
                    unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
                    refreshSelect2(unitSelect);
                }

                resetUnitTopics();
            }

            function resetUnitTopics() {
                if (unitTopicSelect) {
                    unitTopicSelect.innerHTML = '<option value="">-- Select Topic --</option>';
                    refreshSelect2(unitTopicSelect);
                }

                resetParentTopics();
            }

            function resetParentTopics() {
                if (parentTopicSelect) {
                    parentTopicSelect.innerHTML = '<option value="">-- None (Core Topic) --</option>';
                    refreshSelect2(parentTopicSelect);
                }
            }

            function loadUnits(subjectId, selectedUnitId = null, selectedTopicId = null, selectedParentTopicId = null) {
                resetUnits();

                if (!subjectId || !unitSelect) {
                    return;
                }

                unitSelect.innerHTML = '<option value="">Loading...</option>';
                refreshSelect2(unitSelect);

                fetch("{{ url('/admin/units/by-subject') }}/" + subjectId)
                    .then(response => response.json())
                    .then(units => {
                        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

                        units.forEach(unit => {
                            const option = document.createElement('option');
                            option.value = unit.id;
                            option.textContent = unit.name;

                            if (selectedUnitId && String(selectedUnitId) === String(unit.id)) {
                                option.selected = true;
                            }

                            unitSelect.appendChild(option);
                        });

                        refreshSelect2(unitSelect);

                        if (selectedUnitId) {
                            loadUnitTopics(selectedUnitId, selectedTopicId, selectedParentTopicId);
                        }
                    })
                    .catch(() => {
                        resetUnits();
                    });
            }

            function loadUnitTopics(unitId, selectedTopicId = null, selectedParentTopicId = null) {
                resetUnitTopics();

                if (!unitId || !unitTopicSelect) {
                    return;
                }

                unitTopicSelect.innerHTML = '<option value="">Loading...</option>';
                refreshSelect2(unitTopicSelect);

                fetch("{{ url('/admin/unit-topics/by-unit') }}/" + unitId)
                    .then(response => response.json())
                    .then(topics => {
                        unitTopicSelect.innerHTML = '<option value="">-- Select Topic --</option>';

                        topics.forEach(topic => {
                            const option = document.createElement('option');
                            option.value = topic.id;
                            option.textContent = topic.title;

                            if (selectedTopicId && String(selectedTopicId) === String(topic.id)) {
                                option.selected = true;
                            }

                            unitTopicSelect.appendChild(option);
                        });

                        refreshSelect2(unitTopicSelect);

                        if (selectedTopicId) {
                            loadParentTopics(selectedTopicId, selectedParentTopicId);
                        }
                    })
                    .catch(() => {
                        resetUnitTopics();
                    });
            }

            function loadParentTopics(unitTopicId, selectedParentTopicId = null) {
                resetParentTopics();

                if (!unitTopicId || !parentTopicSelect) {
                    return;
                }

                parentTopicSelect.innerHTML = '<option value="">Loading...</option>';
                refreshSelect2(parentTopicSelect);

                fetch("{{ url('/admin/parent-topics/by-topic') }}/" + unitTopicId)
                    .then(response => response.json())
                    .then(parentTopics => {
                        parentTopicSelect.innerHTML = '<option value="">-- None (Core Topic) --</option>';

                        parentTopics.forEach(parentTopic => {
                            const option = document.createElement('option');
                            option.value = parentTopic.id;
                            option.textContent = parentTopic.title;

                            if (selectedParentTopicId && String(selectedParentTopicId) === String(parentTopic.id)) {
                                option.selected = true;
                            }

                            parentTopicSelect.appendChild(option);
                        });

                        refreshSelect2(parentTopicSelect);
                    })
                    .catch(() => {
                        resetParentTopics();
                    });
            }

            function loadSemesters(yearId, selectedSemesterId = null) {
                if (!semesterSelect) {
                    return;
                }

                semesterSelect.innerHTML = '<option value="">-- Select Semester --</option>';

                if (!yearId) {
                    return;
                }

                const year = yearsData.find(item => String(item.id) === String(yearId));

                if (year && year.semesters) {
                    year.semesters.forEach(semester => {
                        const option = document.createElement('option');
                        option.value = semester.id;
                        option.textContent = semester.name;

                        if (selectedSemesterId && String(selectedSemesterId) === String(semester.id)) {
                            option.selected = true;
                        }

                        semesterSelect.appendChild(option);
                    });
                }
            }

            // --- Subject -> Units ---
            $('#subjectSelect').on('change', function () {
                loadUnits($(this).val());
            });

            // --- Unit -> Topics ---
            $('#unitSelect').on('change', function () {
                loadUnitTopics($(this).val());
            });

            // --- Topic -> Parent Topics ---
            $('#unitTopicSelect').on('change', function () {
                loadParentTopics($(this).val());
            });

            // --- Year -> Semesters ---
            if (yearSelect && semesterSelect) {
                yearSelect.addEventListener('change', function () {
                    loadSemesters(this.value);
                });
            }

            // --- Restore old values after validation error ---
            if (oldSubjectId) {
                loadUnits(oldSubjectId, oldUnitId, oldUnitTopicId, oldParentTopicId);
            }

            if (oldAcademicYearId) {
                loadSemesters(oldAcademicYearId, oldSemesterId);
            }

            // --- Add Material ---
            if (addMaterialButton && materialContainer && materialTemplate) {
                addMaterialButton.addEventListener('click', function () {
                    if (noMaterialsMsg) {
                        noMaterialsMsg.classList.add('d-none');
                    }

                    const html = materialTemplate.innerHTML.replace(/INDEX/g, materialIndex);
                    const div = document.createElement('div');

                    div.innerHTML = html;
                    materialContainer.appendChild(div.firstElementChild);

                    materialIndex++;
                });
            }

            // --- Remove Material ---
            document.addEventListener('click', function (event) {
                if (event.target.classList.contains('remove-material')) {
                    const item = event.target.closest('.material-item');

                    if (item) {
                        item.remove();
                    }

                    if (document.querySelectorAll('.material-item').length === 0 && noMaterialsMsg) {
                        noMaterialsMsg.classList.remove('d-none');
                    }
                }
            });

            // --- Material Type Change ---
            document.addEventListener('change', function (event) {
                if (event.target.classList.contains('material-type-select')) {
                    const type = event.target.value;
                    const parent = event.target.closest('.material-item');

                    if (!parent) {
                        return;
                    }

                    parent.querySelectorAll('.type-fields').forEach(field => {
                        field.classList.toggle('d-none', field.dataset.type !== type);
                    });
                }
            });

            // --- CKEditor ---
            try {
                if (typeof ClassicEditor !== 'undefined') {
                    ClassicEditor
                        .create(document.querySelector('textarea[name="description"]'), {
                            ckfinder: {
                                uploadUrl: '{{ route("admin.topics.upload_image") }}?_token={{ csrf_token() }}'
                            },
                            toolbar: [
                                'heading',
                                '|',
                                'bold',
                                'italic',
                                'link',
                                'bulletedList',
                                'numberedList',
                                'blockQuote',
                                '|',
                                'imageUpload',
                                'insertTable',
                                'mediaEmbed',
                                'undo',
                                'redo'
                            ]
                        })
                        .catch(error => console.warn('CKEditor init error:', error));
                }
            } catch (error) {
                console.warn('CKEditor not available:', error);
            }
        });
    </script>
@endpush
