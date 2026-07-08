@php
    $formMaterials = old('materials');

    if ($formMaterials === null && $examAid->exists) {
        $formMaterials = [];

        foreach ($examAid->materials as $material) {
            $formMaterials[] = [
                'title' => $material->title,
                'type' => $material->type,
                'content' => $material->content,
                'url' => $material->url,
                'existing_file_path' => $material->file_path,
            ];
        }
    }

    $formMaterials = $formMaterials ?? [];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white p-4 border-0">
                <h5 class="fw-bold mb-0">Exam Aid Details</h5>
            </div>

            <div class="card-body p-4 pt-0">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title</label>

                    <input
                        type="text"
                        name="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $examAid->title) }}"
                        placeholder="e.g. Final Exam Preparation Aid"
                        required
                    >

                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description / Overview</label>

                    <textarea
                        name="description"
                        class="form-control @error('description') is-invalid @enderror"
                        rows="4"
                        placeholder="Brief introduction..."
                    >{{ old('description', $examAid->description) }}</textarea>

                    @error('description')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Viva Question</label>

                    <textarea
                        name="viva_question"
                        class="form-control @error('viva_question') is-invalid @enderror"
                        rows="6"
                        placeholder="Write viva questions here..."
                    >{{ old('viva_question', $examAid->viva_question) }}</textarea>

                    @error('viva_question')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Exam Question</label>

                    <textarea
                        name="exam_question"
                        class="form-control @error('exam_question') is-invalid @enderror"
                        rows="6"
                        placeholder="Write exam questions here..."
                    >{{ old('exam_question', $examAid->exam_question) }}</textarea>

                    @error('exam_question')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Learning Materials</h5>

                <button type="button" class="btn btn-sm btn-primary" id="addMaterial">
                    <i class="bi bi-plus-lg"></i> Add Material
                </button>
            </div>

            <div class="card-body p-4 pt-0">
                <div id="materialContainer">
                    <div class="text-center py-4 text-muted {{ count($formMaterials) ? 'd-none' : '' }}" id="noMaterialsMsg">
                        <i class="bi bi-box fs-1 d-block mb-2"></i>
                        No materials added yet. Click "Add Material" to start.
                    </div>

                    @foreach($formMaterials as $index => $material)
                        @php
                            $materialType = $material['type'] ?? 'pdf';
                        @endphp

                        <div class="material-item border rounded-3 p-3 mb-3 bg-light position-relative">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-material"></button>

                            <input
                                type="hidden"
                                name="materials[{{ $index }}][existing_file_path]"
                                value="{{ $material['existing_file_path'] ?? '' }}"
                            >

                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Material Title</label>

                                    <input
                                        type="text"
                                        name="materials[{{ $index }}][title]"
                                        class="form-control form-control-sm"
                                        placeholder="e.g. Lecture Note PDF"
                                        value="{{ $material['title'] ?? '' }}"
                                    >
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Type</label>

                                    <select name="materials[{{ $index }}][type]" class="form-select form-select-sm material-type-select">
                                        <option value="pdf" {{ $materialType === 'pdf' ? 'selected' : '' }}>PDF File</option>
                                        <option value="video" {{ $materialType === 'video' ? 'selected' : '' }}>Video Embed</option>
                                        <option value="link" {{ $materialType === 'link' ? 'selected' : '' }}>External Link</option>
                                        <option value="note" {{ $materialType === 'note' ? 'selected' : '' }}>Text Note</option>
                                    </select>
                                </div>

                                <div class="col-12 mt-2 type-fields {{ $materialType === 'pdf' ? '' : 'd-none' }}" data-type="pdf">
                                    <label class="form-label small fw-bold">Upload PDF</label>

                                    <input
                                        type="file"
                                        name="materials[{{ $index }}][file]"
                                        class="form-control form-control-sm"
                                        accept=".pdf"
                                    >

                                    @if(!empty($material['existing_file_path']))
                                        <div class="small mt-2">
                                            Current PDF:
                                            <a href="{{ asset('storage/' . $material['existing_file_path']) }}" target="_blank">
                                                View File
                                            </a>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-12 mt-2 type-fields {{ $materialType === 'video' ? '' : 'd-none' }}" data-type="video">
                                    <label class="form-label small fw-bold">Video URL / Embed Code</label>

                                    <textarea
                                        name="materials[{{ $index }}][content]"
                                        class="form-control form-control-sm"
                                        rows="2"
                                        placeholder="YouTube link or iframe..."
                                    >{{ $material['content'] ?? '' }}</textarea>
                                </div>

                                <div class="col-12 mt-2 type-fields {{ $materialType === 'link' ? '' : 'd-none' }}" data-type="link">
                                    <label class="form-label small fw-bold">External URL</label>

                                    <input
                                        type="url"
                                        name="materials[{{ $index }}][url]"
                                        class="form-control form-control-sm"
                                        placeholder="https://..."
                                        value="{{ $material['url'] ?? '' }}"
                                    >
                                </div>

                                <div class="col-12 mt-2 type-fields {{ $materialType === 'note' ? '' : 'd-none' }}" data-type="note">
                                    <label class="form-label small fw-bold">Note Content</label>

                                    <textarea
                                        name="materials[{{ $index }}][content]"
                                        class="form-control form-control-sm"
                                        rows="3"
                                        placeholder="Write your notes here..."
                                    >{{ $material['content'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                @error('materials')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white p-4 border-0">
                <h5 class="fw-bold mb-0">Categorization</h5>
            </div>

            <div class="card-body p-4 pt-0">
                <div class="mb-3">
                    <label class="form-label fw-bold">Subject</label>

                    <select
                        name="subject_id"
                        class="form-select @error('subject_id') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Select Subject --</option>

                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" {{ old('subject_id', $examAid->subject_id) == $subject->id ? 'selected' : '' }}>
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Unit Optional</label>

                    <select
                        name="unit_id"
                        class="form-select @error('unit_id') is-invalid @enderror"
                    >
                        <option value="">-- Select Unit --</option>

                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id', $examAid->unit_id) == $unit->id ? 'selected' : '' }}>
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('unit_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Academic Year Optional</label>

                    <select
                        name="academic_year_id"
                        class="form-select @error('academic_year_id') is-invalid @enderror"
                    >
                        <option value="">-- Select Year --</option>

                        @foreach($years as $year)
                            <option value="{{ $year->id }}" {{ old('academic_year_id', $examAid->academic_year_id) == $year->id ? 'selected' : '' }}>
                                {{ $year->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('academic_year_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Semester Optional</label>

                    <select
                        name="semester_id"
                        class="form-select @error('semester_id') is-invalid @enderror"
                    >
                        <option value="">-- Select Semester --</option>

                        @foreach($semesters as $semester)
                            <option value="{{ $semester->id }}" {{ old('semester_id', $examAid->semester_id) == $semester->id ? 'selected' : '' }}>
                                {{ $semester->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('semester_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="form-check form-switch mb-3">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="status"
                        id="status"
                        value="1"
                        {{ old('status', $examAid->exists ? $examAid->status : 1) ? 'checked' : '' }}
                    >

                    <label class="form-check-label fw-bold" for="status">
                        Active / Visible
                    </label>
                </div>
            </div>

            <div class="card-footer bg-light p-3 d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    {{ $buttonText }}
                </button>
            </div>
        </div>
    </div>
</div>

<template id="materialTemplate">
    <div class="material-item border rounded-3 p-3 mb-3 bg-light position-relative">
        <button type="button" class="btn-close position-absolute top-0 end-0 m-2 remove-material"></button>

        <input type="hidden" name="materials[INDEX][existing_file_path]" value="">

        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label small fw-bold">Material Title</label>

                <input
                    type="text"
                    name="materials[INDEX][title]"
                    class="form-control form-control-sm"
                    placeholder="e.g. Lecture Note PDF"
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

                <input
                    type="file"
                    name="materials[INDEX][file]"
                    class="form-control form-control-sm"
                    accept=".pdf"
                >
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        let materialIndex = {{ count($formMaterials) }};

        const addMaterialButton = document.getElementById('addMaterial');
        const materialContainer = document.getElementById('materialContainer');
        const materialTemplate = document.getElementById('materialTemplate');
        const noMaterialsMsg = document.getElementById('noMaterialsMsg');

        function toggleNoMaterialsMessage() {
            const items = materialContainer.querySelectorAll('.material-item');

            if (items.length > 0) {
                noMaterialsMsg.classList.add('d-none');
            } else {
                noMaterialsMsg.classList.remove('d-none');
            }
        }

        function updateTypeFields(materialItem, selectedType) {
            materialItem.querySelectorAll('.type-fields').forEach(function (field) {
                if (field.dataset.type === selectedType) {
                    field.classList.remove('d-none');
                } else {
                    field.classList.add('d-none');
                }
            });
        }

        addMaterialButton.addEventListener('click', function () {
            const html = materialTemplate.innerHTML.replaceAll('INDEX', materialIndex);

            materialContainer.insertAdjacentHTML('beforeend', html);

            materialIndex++;

            toggleNoMaterialsMessage();
        });

        materialContainer.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-material')) {
                event.target.closest('.material-item').remove();

                toggleNoMaterialsMessage();
            }
        });

        materialContainer.addEventListener('change', function (event) {
            if (event.target.classList.contains('material-type-select')) {
                const materialItem = event.target.closest('.material-item');

                updateTypeFields(materialItem, event.target.value);
            }
        });

        toggleNoMaterialsMessage();
    });
</script>
