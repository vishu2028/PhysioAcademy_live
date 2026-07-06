<form action="{{ $action }}" method="POST">
    @csrf

    @if($method !== 'POST')
        @method($method)
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row">
                {{-- Subject --}}
                <div class="col-md-6 mb-3">
                    <label for="subject_id" class="form-label fw-bold">
                        Subject <span class="text-danger">*</span>
                    </label>

                    <select
                        name="subject_id"
                        id="subject_id"
                        class="form-select @error('subject_id') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Select Subject --</option>

                        @foreach($subjects as $subject)
                            <option
                                value="{{ $subject->id }}"
                                {{ (string) old('subject_id', $parentTopic->subject_id ?? '') === (string) $subject->id ? 'selected' : '' }}
                            >
                                {{ $subject->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('subject_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Unit --}}
                <div class="col-md-6 mb-3">
                    <label for="unit_id" class="form-label fw-bold">
                        Unit <span class="text-danger">*</span>
                    </label>

                    <select
                        name="unit_id"
                        id="unit_id"
                        class="form-select @error('unit_id') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Select Unit --</option>

                        @foreach($units as $unit)
                            <option
                                value="{{ $unit->id }}"
                                {{ (string) old('unit_id', $parentTopic->unit_id ?? '') === (string) $unit->id ? 'selected' : '' }}
                            >
                                {{ $unit->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('unit_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Topic --}}
                <div class="col-md-6 mb-3">
                    <label for="unit_topic_id" class="form-label fw-bold">
                        Topic <span class="text-danger">*</span>
                    </label>

                    <select
                        name="unit_topic_id"
                        id="unit_topic_id"
                        class="form-select @error('unit_topic_id') is-invalid @enderror"
                        required
                    >
                        <option value="">-- Select Topic --</option>

                        @foreach($unitTopics as $unitTopic)
                            <option
                                value="{{ $unitTopic->id }}"
                                {{ (string) old('unit_topic_id', $parentTopic->unit_topic_id ?? '') === (string) $unitTopic->id ? 'selected' : '' }}
                            >
                                {{ $unitTopic->title }}
                            </option>
                        @endforeach
                    </select>

                    @error('unit_topic_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Parent Topic Name --}}
                <div class="col-md-6 mb-3">
                    <label for="title" class="form-label fw-bold">
                        Parent Topic Name <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        id="title"
                        class="form-control @error('title') is-invalid @enderror"
                        value="{{ old('title', $parentTopic->title ?? '') }}"
                        placeholder="e.g. Muscles, Movements, Clinical Tests"
                        required
                    >

                    @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Sort Order --}}
                <div class="col-md-6 mb-3">
                    <label for="sort_order" class="form-label fw-bold">
                        Sort Order
                    </label>

                    <input
                        type="number"
                        name="sort_order"
                        id="sort_order"
                        class="form-control @error('sort_order') is-invalid @enderror"
                        value="{{ old('sort_order', $parentTopic->sort_order ?? 0) }}"
                        min="0"
                    >

                    @error('sort_order')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold d-block">
                        Status
                    </label>

                    <div class="form-check form-switch mt-2">
                        <input
                            type="checkbox"
                            name="status"
                            id="status"
                            value="1"
                            class="form-check-input"
                            {{ old('status', isset($parentTopic) ? $parentTopic->status : true) ? 'checked' : '' }}
                        >

                        <label class="form-check-label" for="status">
                            Active
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer bg-white border-0 p-4 d-flex justify-content-end gap-2">
            <a href="{{ route('admin.parent-topics.index') }}" class="btn btn-light">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                {{ $buttonText }}
            </button>
        </div>
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subjectSelect = document.getElementById('subject_id');
        const unitSelect = document.getElementById('unit_id');
        const topicSelect = document.getElementById('unit_topic_id');

        if (!subjectSelect || !unitSelect || !topicSelect) {
            return;
        }

        subjectSelect.addEventListener('change', function () {
            const subjectId = this.value;

            unitSelect.innerHTML = '<option value="">-- Select Unit --</option>';
            topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';

            if (!subjectId) {
                return;
            }

            fetch("{{ url('/admin/units/by-subject') }}/" + subjectId)
                .then(function (response) {
                    return response.json();
                })
                .then(function (units) {
                    units.forEach(function (unit) {
                        const option = document.createElement('option');

                        option.value = unit.id;
                        option.textContent = unit.name;

                        unitSelect.appendChild(option);
                    });
                })
                .catch(function (error) {
                    console.error('Error loading units:', error);
                });
        });

        unitSelect.addEventListener('change', function () {
            const unitId = this.value;

            topicSelect.innerHTML = '<option value="">-- Select Topic --</option>';

            if (!unitId) {
                return;
            }

            fetch("{{ url('/admin/unit-topics/by-unit') }}/" + unitId)
                .then(function (response) {
                    return response.json();
                })
                .then(function (topics) {
                    topics.forEach(function (topic) {
                        const option = document.createElement('option');

                        option.value = topic.id;
                        option.textContent = topic.title;

                        topicSelect.appendChild(option);
                    });
                })
                .catch(function (error) {
                    console.error('Error loading topics:', error);
                });
        });
    });
</script>
