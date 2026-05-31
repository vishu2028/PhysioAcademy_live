@extends('layouts.admin')

@section('title', 'Create Page')

@push('styles')
<style>
    .ck-editor__editable {
        min-height: 500px;
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.pages.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <h2 class="fw-bold text-dark mt-2">Create New Dynamic Page</h2>
</div>

<form action="{{ route('admin.pages.store') }}" method="POST">
    @csrf

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Page Title</label>
                        <input type="text" name="title" id="pageTitle" class="form-control form-control-lg rounded-3" value="{{ old('title') }}" placeholder="Enter page title..." required>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-bold">Page Content</label>
                        <textarea name="content" id="editor" class="form-control" rows="20">{{ old('content') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Publishing Settings</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug / URL</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">/page/</span>
                            <input type="text" name="slug" id="pageSlug" class="form-control border-start-0" value="{{ old('slug') }}" placeholder="page-url-slug" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Visibility Status</label>
                        <select name="status" class="form-select rounded-3">
                            <option value="1" {{ old('status', 1) == 1 ? 'selected' : '' }}>Published (Public)</option>
                            <option value="0" {{ old('status', 1) == 0 ? 'selected' : '' }}>Draft (Private)</option>
                        </select>
                    </div>

                    <div class="mb-0">
                        <div class="form-check form-switch pt-2">
                            <input class="form-check-input" type="checkbox" name="show_in_menu" id="showMenuSwitch" value="1" {{ old('show_in_menu') ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold" for="showMenuSwitch">Show in Main Menu</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button type="submit" class="btn btn-primary rounded-3 py-2 fw-bold">
                        <i class="bi bi-save me-2"></i> Save and Publish
                    </button>
                    <a href="{{ route('admin.pages.index') }}" class="btn btn-link btn-sm text-secondary mt-2">Cancel and discard</a>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">SEO Metadata (Optional)</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Meta Description</label>
                        <textarea name="meta_description" class="form-control rounded-3" rows="3">{{ old('meta_description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            ckfinder: {
                uploadUrl: '{{ route("admin.pages.upload_image") }}?_token={{ csrf_token() }}'
            },
            toolbar: [
                'heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|',
                'imageUpload', 'insertTable', 'mediaEmbed', '|', 'undo', 'redo'
            ]
        })
        .catch(error => {
            console.error(error);
        });

    const titleInput = document.getElementById('pageTitle');
    const slugInput = document.getElementById('pageSlug');

    titleInput.addEventListener('input', function() {
        const slug = titleInput.value
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        slugInput.value = slug;
    });
</script>
@endpush
@endsection
