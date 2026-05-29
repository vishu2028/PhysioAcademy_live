@extends('layouts.admin')

@section('title', 'Media Library')

@push('styles')
<style>
    .media-card {
        transition: all 0.2s ease-in-out;
        border: 2px solid transparent;
        cursor: pointer;
    }
    .media-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        border-color: #2563eb;
    }
    .media-preview {
        height: 160px;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
    .media-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .media-preview i {
        font-size: 3rem;
        color: #cbd5e1;
    }
</style>
@endpush

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Media Library</h2>
        <p class="text-secondary">Upload and manage images and documents used across the site.</p>
    </div>
    <button class="btn btn-primary rounded-3 px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-upload me-2"></i> Upload Files
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body">
        <form action="{{ route('admin.media.index') }}" method="GET" class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 rounded-start-3"><i class="bi bi-search"></i></span>
                    <input type="text" name="search" class="form-control border-start-0 rounded-end-3" placeholder="Search filenames..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select rounded-3">
                    <option value="">All Media Types</option>
                    <option value="image" {{ request('type') == 'image' ? 'selected' : '' }}>Images</option>
                    <option value="video" {{ request('type') == 'video' ? 'selected' : '' }}>Videos</option>
                    <option value="document" {{ request('type') == 'document' ? 'selected' : '' }}>Documents</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-dark w-100 rounded-3">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($media as $item)
    <div class="col-6 col-md-4 col-lg-2">
        <div class="card media-card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative">
            <div class="media-preview">
                @if($item->is_image)
                    <img src="{{ $item->url }}" alt="{{ $item->file_name }}">
                @else
                    @php
                        $icon = 'bi-file-earmark';
                        if(str_contains($item->file_type, 'pdf')) $icon = 'bi-file-earmark-pdf text-danger';
                        elseif(str_contains($item->file_type, 'video')) $icon = 'bi-play-circle text-primary';
                    @endphp
                    <i class="bi {{ $icon }}"></i>
                @endif
            </div>
            <div class="card-body p-2 border-top">
                <p class="small fw-bold mb-0 text-truncate" title="{{ $item->file_name }}">{{ $item->file_name }}</p>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted">{{ $item->human_size }}</small>
                    <div class="dropdown">
                        <button class="btn btn-link btn-sm p-0 text-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                            <li><a class="dropdown-item" href="{{ $item->url }}" target="_blank"><i class="bi bi-eye me-2"></i> View Full</a></li>
                            <li><button class="dropdown-item" onclick="copyToClipboard('{{ $item->url }}')"><i class="bi bi-link-45deg me-2"></i> Copy URL</button></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('admin.media.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete this file permanently?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="p-5 bg-white rounded-5 shadow-sm d-inline-block">
            <i class="bi bi-images fs-1 text-light mb-3 d-block"></i>
            <h4 class="fw-bold">No media found</h4>
            <p class="text-secondary">Upload your first files to start building your library.</p>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-5">
    {{ $media->appends(request()->query())->links() }}
</div>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Upload Files</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.media.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Files</label>
                        <input type="file" name="files[]" class="form-control rounded-3" multiple required>
                        <div class="form-text mt-2">You can select multiple files at once. Max file size: 10MB each.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-3 px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4">Start Upload</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('URL copied to clipboard!');
        });
    }
</script>
@endpush
@endsection
