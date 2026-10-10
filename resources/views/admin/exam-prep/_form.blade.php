@php($card = $card ?? null)

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           placeholder="e.g. Viva Mastery" value="{{ old('title', $card?->title) }}" required>
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror"
                              placeholder="Short text shown on the card">{{ old('description', $card?->description) }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Button Text</label>
                    <input type="text" name="button_text" class="form-control @error('button_text') is-invalid @enderror"
                           value="{{ old('button_text', $card?->button_text ?? 'Open') }}" required>
                    @error('button_text') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <hr class="my-4">

                <h6 class="fw-bold mb-3">Where should the button go?</h6>

                <div class="mb-3">
                    <label class="form-label fw-bold">Material Category</label>
                    <select name="exam_aid_category_id" class="form-select @error('exam_aid_category_id') is-invalid @enderror">
                        <option value="">None (opens the Exam Aid page)</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}"
                                @selected((string) old('exam_aid_category_id', $card?->exam_aid_category_id) === (string) $category->id)>
                                {{ $category->name }}{{ $category->is_active ? '' : ' (inactive)' }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        Categories are added from the Exam Aid create page.
                    </div>
                    @error('exam_aid_category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-0">
                    <label class="form-label fw-bold">Custom Link (optional)</label>
                    <input type="text" name="custom_url" class="form-control @error('custom_url') is-invalid @enderror"
                           placeholder="https://… or /page — overrides the category"
                           value="{{ old('custom_url', $card?->custom_url) }}">
                    @error('custom_url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Icon</label>
                    <select name="icon" class="form-select @error('icon') is-invalid @enderror" required>
                        @foreach($icons as $icon)
                            <option value="{{ $icon }}" @selected(old('icon', $card?->icon ?? 'edit') === $icon)>
                                {{ ucwords(str_replace('-', ' ', $icon)) }}
                            </option>
                        @endforeach
                    </select>
                    @error('icon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Order</label>
                    <input type="number" name="sort_order" min="0" class="form-control"
                           value="{{ old('sort_order', $card?->sort_order ?? 0) }}">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_large" id="is_large" value="1"
                           @checked(old('is_large', $card?->is_large ?? false))>
                    <label class="form-check-label" for="is_large">
                        Large card <span class="text-muted small">(wider, shows topic and subject counts)</span>
                    </label>
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           @checked(old('is_active', $card?->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Show on homepage</label>
                </div>
            </div>

            <div class="card-footer bg-light p-3 d-grid">
                <button class="btn btn-primary">{{ $card ? 'Update Card' : 'Create Card' }}</button>
            </div>
        </div>
    </div>
</div>
