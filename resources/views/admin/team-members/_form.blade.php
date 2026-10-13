@php($member = $member ?? null)

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $member?->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Designation</label>
                    <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror"
                           placeholder="e.g. Founder, Academic Lead" value="{{ old('designation', $member?->designation) }}">
                    @error('designation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold">Short Description</label>
                    <textarea name="bio" rows="4" maxlength="500" class="form-control @error('bio') is-invalid @enderror"
                              placeholder="Expertise or responsibilities (max 500 characters)">{{ old('bio', $member?->bio) }}</textarea>
                    @error('bio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Photo</label>

                    @if($member?->photo_url)
                        <div class="mb-2">
                            <img src="{{ $member->photo_url }}" alt="{{ $member->name }}" class="rounded-circle object-fit-cover" width="90" height="90">
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="remove_photo" id="remove_photo" value="1">
                            <label class="form-check-label small" for="remove_photo">Remove current photo</label>
                        </div>
                    @endif

                    <input type="file" name="photo" accept="image/*" class="form-control @error('photo') is-invalid @enderror">
                    <div class="form-text">Square image works best. Max 2 MB.</div>
                    @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Order</label>
                    <input type="number" name="sort_order" min="0" class="form-control"
                           value="{{ old('sort_order', $member?->sort_order ?? 0) }}">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           @checked(old('is_active', $member?->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Show on About page</label>
                </div>
            </div>

            <div class="card-footer bg-light p-3 d-grid">
                <button class="btn btn-primary">{{ $member ? 'Update Member' : 'Add Member' }}</button>
            </div>
        </div>
    </div>
</div>
