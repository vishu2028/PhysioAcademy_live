@extends('layouts.admin')

@section('title', 'Edit '.$page->title)

@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 420px;
        max-height: 70vh;
        overflow-y: auto;
    }
</style>
@endpush

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.support-legal-pages.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Support &amp; Legal Pages
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit {{ $page->title }}</h2>
</div>

<form action="{{ route('admin.support-legal-pages.update', $page->slug) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="mb-4">
                        <label for="pageTitle" class="form-label fw-bold">Page Title</label>
                        <input id="pageTitle" type="text" name="title" class="form-control form-control-lg rounded-3" value="{{ old('title', $page->title) }}" required>
                    </div>
                    <div>
                        <label for="editor" class="form-label fw-bold">Description / Content</label>
                        <textarea id="editor" name="content" class="form-control">{{ old('content', $page->content) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold mb-0">Publishing</h6>
                </div>
                <div class="card-body">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="status" value="1" id="status" {{ old('status', $page->status) ? 'checked' : '' }}>
                        <label class="form-check-label fw-semibold" for="status">Published</label>
                    </div>
                    <p class="text-secondary small mt-3 mb-0">
                        Unpublished pages remain editable in the admin panel and are not shown to normal users.
                    </p>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button type="submit" class="btn btn-primary rounded-3 fw-bold">
                        <i class="bi bi-check-circle me-1"></i> Save and Update
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
<script>
(() => {
    const Editor = window.CKEDITOR?.ClassicEditor;
    const textarea = document.querySelector('#editor');

    if (!Editor || !textarea) {
        console.error('Support page editor initialization failed.');
        return;
    }

    Editor.create(textarea, {
        removePlugins: [
            'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
            'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments',
            'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Users',
            'CommentsRepository', 'WProofreader', 'Pagination', 'DocumentOutline',
            'AIAssistant', 'AIAssistantUI', 'AIAssistantEditing', 'AIAdapter',
            'TableOfContents', 'FormatPainter', 'Template', 'SlashCommand',
            'PasteFromOfficeEnhanced', 'CaseChange', 'MathType'
        ],
        ckfinder: {
            uploadUrl: '{{ route("admin.pages.upload_image") }}?_token={{ csrf_token() }}'
        },
        toolbar: {
            items: [
                'heading', '|', 'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
                'bold', 'italic', 'underline', 'strikethrough', 'removeFormat', '|',
                'link', 'bulletedList', 'numberedList', 'outdent', 'indent', '|',
                'alignment', 'blockQuote', 'imageUpload', 'insertTable', 'mediaEmbed', '|',
                'undo', 'redo'
            ]
        }
    }).catch(error => console.error('Support page editor failed:', error));
})();
</script>
@endpush
@endsection
