@php($category = $category ?? null)

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Category Name</label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           placeholder="e.g. Past Papers" value="{{ old('name', $category?->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Slug</label>
                    <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                           placeholder="Leave empty to generate from the name" value="{{ old('slug', $category?->slug) }}">
                    <div class="form-text">
                        The category page opens at <code>{{ url('/exam-aid/category') }}/<strong>slug</strong></code>.
                        Changing the slug changes that page's link.
                    </div>
                    @error('slug') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Description</label>
                    <textarea name="description" rows="3" class="form-control"
                              placeholder="Shown at the top of the category page">{{ old('description', $category?->description) }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body">
                <div class="alert alert-info small">
                    Categories are shared: they can be assigned to Exam Aid materials and to topic learning materials.
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Order</label>
                    <input type="number" name="sort_order" min="0" class="form-control"
                           value="{{ old('sort_order', $category?->sort_order ?? 0) }}">
                </div>

                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
                           @checked(old('is_active', $category?->is_active ?? true))>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>

            <div class="card-footer bg-light p-3 d-grid">
                <button class="btn btn-primary">{{ $category ? 'Update Category' : 'Create Category' }}</button>
            </div>
        </div>
    </div>
</div>
