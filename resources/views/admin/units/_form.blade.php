<div class="mb-3">
    <label class="form-label">Subject</label>

    <select name="subject_id" class="form-select @error('subject_id') is-invalid @enderror" required>
        <option value="">Select Subject</option>

        @foreach($subjects as $subject)
            <option value="{{ $subject->id }}"
                @selected(old('subject_id', $unit->subject_id ?? '') == $subject->id)>
                {{ $subject->name }}
            </option>
        @endforeach
    </select>

    @error('subject_id')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Unit Name</label>

    <input
        type="text"
        name="name"
        class="form-control @error('name') is-invalid @enderror"
        value="{{ old('name', $unit->name ?? '') }}"
        required
    >

    @error('name')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Description</label>

    <textarea
        name="description"
        class="form-control @error('description') is-invalid @enderror"
        rows="4"
    >{{ old('description', $unit->description ?? '') }}</textarea>

    @error('description')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Sort Order</label>

    <input
        type="number"
        name="sort_order"
        class="form-control @error('sort_order') is-invalid @enderror"
        value="{{ old('sort_order', $unit->sort_order ?? 0) }}"
        min="0"
    >

    @error('sort_order')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Status</label>

    <select name="is_active" class="form-select @error('is_active') is-invalid @enderror">
        <option value="1" @selected(old('is_active', $unit->is_active ?? 1) == 1)>Active</option>
        <option value="0" @selected(old('is_active', $unit->is_active ?? 1) == 0)>Inactive</option>
    </select>

    @error('is_active')
    <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
