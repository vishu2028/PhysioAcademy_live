@php
    $selectedSubjectId = old('subject_id', $unitTopic->subject_id ?? '');
    $selectedUnitId = old('unit_id', $unitTopic->unit_id ?? '');
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Subject</label>

        <select
            name="subject_id"
            id="subjectSelect"
            class="form-select @error('subject_id') is-invalid @enderror"
            required
        >
            <option value="">-- Select Subject --</option>

            @foreach($subjects as $subject)
                <option value="{{ $subject->id }}" {{ $selectedSubjectId == $subject->id ? 'selected' : '' }}>
                    {{ $subject->name }}
                </option>
            @endforeach
        </select>

        @error('subject_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Unit</label>

        <select
            name="unit_id"
            id="unitSelect"
            class="form-select @error('unit_id') is-invalid @enderror"
            required
        >
            <option value="">-- Select Unit --</option>

            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ $selectedUnitId == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }}
                </option>
            @endforeach
        </select>

        @error('unit_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-12 mb-3">
        <label class="form-label fw-bold">Topic Name</label>

        <input
            type="text"
            name="title"
            class="form-control @error('title') is-invalid @enderror"
            value="{{ old('title', $unitTopic->title ?? '') }}"
            placeholder="Enter topic name"
            required
        >

        @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Sort Order</label>

        <input
            type="number"
            name="sort_order"
            class="form-control @error('sort_order') is-invalid @enderror"
            value="{{ old('sort_order', $unitTopic->sort_order ?? 0) }}"
            min="0"
        >

        @error('sort_order')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Status</label>

        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
            <option value="1" {{ old('status', $unitTopic->status ?? 1) == 1 ? 'selected' : '' }}>
                Active
            </option>

            <option value="0" {{ old('status', $unitTopic->status ?? 1) == 0 ? 'selected' : '' }}>
                Inactive
            </option>
        </select>

        @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const subjectSelect = document.getElementById('subjectSelect');
            const unitSelect = document.getElementById('unitSelect');

            if (!subjectSelect || !unitSelect) {
                return;
            }

            subjectSelect.addEventListener('change', function () {
                const subjectId = this.value;

                unitSelect.innerHTML = '<option value="">Loading...</option>';

                if (!subjectId) {
                    unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
                    return;
                }

                fetch("{{ url('/admin/units/by-subject') }}/" + subjectId)
                    .then(response => response.json())
                    .then(units => {
                        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';

                        units.forEach(unit => {
                            unitSelect.innerHTML += `<option value="${unit.id}">${unit.name}</option>`;
                        });
                    })
                    .catch(() => {
                        unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
                    });
            });
        });
    </script>
@endpush
