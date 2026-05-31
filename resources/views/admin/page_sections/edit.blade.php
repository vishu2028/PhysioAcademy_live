@extends('layouts.admin')

@section('title', 'Edit Section')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.page-sections.index', ['page_id' => $section->page_id]) }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sections
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit Section: {{ $section->name }}</h2>
</div>

<form action="{{ route('admin.page-sections.update', $section) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            {{-- DYNAMIC CONFIG FORM --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0">
                    <h5 class="fw-bold mb-0">Structured Content Management</h5>
                    <p class="text-muted small mb-0">Managing dynamic fields for section type: <code>{{ $section->type }}</code></p>
                </div>
                <div class="card-body p-4 pt-0">
                    <hr class="mt-0 mb-4 opacity-10">

                    @php
                        $content = is_array($section->content) ? $section->content : (json_decode($section->content, true) ?: []);
                    @endphp

                    @if($section->type === 'mission')
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Kicker (Eyebrow Text)</label>
                            <input type="text" name="config[kicker]" class="form-control" value="{{ $content['kicker'] ?? 'Our Mission' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Title</label>
                            <input type="text" name="config[title]" class="form-control" value="{{ $content['title'] ?? 'Our Mission' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Body Content</label>
                            <textarea name="config[body]" class="form-control ckeditor" rows="5">{{ $content['body'] ?? '' }}</textarea>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold font-sm">Mission Pills (Floating Tags)</label>
                            <div id="pills-container">
                                @forelse($content['pills'] ?? [] as $pill)
                                    <div class="input-group mb-2 pill-row">
                                        <input type="text" name="config[pills][]" class="form-control" value="{{ $pill }}">
                                        <button type="button" class="btn btn-outline-danger remove-pill"><i class="bi bi-x"></i></button>
                                    </div>
                                @empty
                                    <div class="input-group mb-2 pill-row">
                                        <input type="text" name="config[pills][]" class="form-control" value="Structured Learning">
                                        <button type="button" class="btn btn-outline-danger remove-pill"><i class="bi bi-x"></i></button>
                                    </div>
                                @endforelse
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-pill"><i class="bi bi-plus"></i> Add Pill</button>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold font-sm">Main Image</label>
                                <input type="file" name="config_images[main_image]" class="form-control mb-2" onchange="previewImg(this, 'main_preview')">
                                <img id="main_preview" src="{{ $content['main_image'] ?? 'https://placehold.co/800x600' }}" class="img-fluid rounded-3 border" style="max-height: 150px;">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold font-sm">Thumbnail 1</label>
                                <input type="file" name="config_images[thumb_1]" class="form-control mb-2" onchange="previewImg(this, 'thumb1_preview')">
                                <img id="thumb1_preview" src="{{ $content['thumb_1'] ?? 'https://placehold.co/400x400' }}" class="img-fluid rounded-3 border" style="max-height: 100px;">
                            </div>
                        </div>

                    @elseif($section->type === 'vision')
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Section Heading</label>
                            <input type="text" name="config[heading]" class="form-control" value="{{ $content['heading'] ?? 'Our Vision' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Vision Body</label>
                            <textarea name="config[body]" class="form-control ckeditor" rows="5">{{ $content['body'] ?? '' }}</textarea>
                        </div>

                    @elseif(in_array($section->type, ['closing', 'closing-banner']))
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Banner Title</label>
                            <input type="text" name="config[heading]" class="form-control" value="{{ $content['heading'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Banner Kicker</label>
                            <input type="text" name="config[kicker]" class="form-control" value="{{ $content['kicker'] ?? '' }}">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold font-sm">Button Text</label>
                                <input type="text" name="config[cta_text]" class="form-control" value="{{ $content['cta_text'] ?? 'Explore Topics' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold font-sm">Button URL</label>
                                <input type="text" name="config[cta_url]" class="form-control" value="{{ $content['cta_url'] ?? '#' }}">
                            </div>
                        </div>

                    @elseif(in_array($section->type, ['hero', 'exam_hero']))
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Hero Heading</label>
                            <input type="text" name="config[title]" class="form-control" value="{{ $content['title'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Highlight Text (Colored)</label>
                            <input type="text" name="config[highlight]" class="form-control" value="{{ $content['highlight'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Hero Subtitle</label>
                            <textarea name="config[subtitle]" class="form-control" rows="3">{{ $content['subtitle'] ?? '' }}</textarea>
                        </div>

                    @elseif($section->type === 'empty-state' || $section->type === 'guest-state')
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Heading</label>
                            <input type="text" name="config[heading]" class="form-control" value="{{ $content['heading'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Subtext</label>
                            <textarea name="config[subtext]" class="form-control" rows="3">{{ $content['subtext'] ?? '' }}</textarea>
                        </div>
                        @if($section->type === 'empty-state')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold font-sm">Button Text</label>
                                <input type="text" name="config[button_text]" class="form-control" value="{{ $content['button_text'] ?? '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold font-sm">Button URL</label>
                                <input type="text" name="config[button_url]" class="form-control" value="{{ $content['button_url'] ?? '' }}">
                            </div>
                        </div>
                        @endif

                    @elseif($section->type === 'contact-info')
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Contact Email</label>
                            <input type="email" name="config[email]" class="form-control" value="{{ $content['email'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Phone Number</label>
                            <input type="text" name="config[phone]" class="form-control" value="{{ $content['phone'] ?? '' }}">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Physical Address</label>
                            <textarea name="config[address]" class="form-control" rows="2">{{ $content['address'] ?? '' }}</textarea>
                        </div>
                    
                    @elseif($section->type === 'social-links')
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Socials Header</label>
                            <input type="text" name="config[heading]" class="form-control" value="{{ $content['heading'] ?? 'Follow our journey' }}">
                        </div>

                    @elseif(Str::contains($section->type, 'grid') || $section->type === 'split-section')
                        <div class="mb-3">
                            <label class="form-label fw-bold font-sm">Section Subtext</label>
                            <textarea name="config[subtext]" class="form-control" rows="3">{{ $content['subtext'] ?? '' }}</textarea>
                        </div>
                        @if($section->type === 'split-section')
                            <div class="mb-3">
                                <label class="form-label fw-bold font-sm">Heading</label>
                                <input type="text" name="config[heading]" class="form-control" value="{{ $content['heading'] ?? '' }}">
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold font-sm">Body Content</label>
                                <textarea name="config[body]" class="form-control" rows="4">{{ $content['body'] ?? '' }}</textarea>
                            </div>
                        @endif

                    @else
                        {{-- FALLBACK RAW JSON IF UNKNOWN TYPE --}}
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle me-2"></i> Custom Type: <strong>{{ $section->type }}</strong>. Managing via Raw JSON.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Raw Content (JSON)</label>
                            <textarea name="content" class="form-control font-monospace" rows="12">{{ json_encode($content, JSON_PRETTY_PRINT) }}</textarea>
                        </div>
                    @endif
                </div>
            </div>

            {{-- SECTION ITEMS TABLE (IF APPLICABLE) --}}
            @php
                $itemTypes = ['features', 'explore', 'feature-grid', 'explore-grid', 'split-section', 'social-links'];
            @endphp
            @if(in_array($section->type, $itemTypes))
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Managed Items</h5>
                    <a href="{{ route('admin.page-section-items.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-primary">Add New Item</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Title / Label</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($section->items as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $item->title ?: 'Untitled' }}</div>
                                        <div class="small text-muted">{{ Str::limit($item->body, 50) }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark">{{ $item->order }}</span></td>
                                    <td>
                                        @if($item->enabled)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.page-section-items.edit', $item) }}" class="btn btn-light btn-sm rounded-2"><i class="bi bi-pencil"></i></a>
                                            <form action="{{ route('admin.page-section-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete item?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm rounded-2"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-secondary">No items found for this section.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            {{-- GENERAL SETTINGS --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-white border-0 p-4">
                    <h5 class="fw-bold mb-0">General Settings</h5>
                </div>
                <div class="card-body p-4 pt-0">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Section Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $section->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug (ID)</label>
                        <input type="text" name="slug" class="form-control bg-light" value="{{ old('slug', $section->slug) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type Hook</label>
                        <input type="text" name="type" class="form-control bg-light" value="{{ old('type', $section->type) }}" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" name="order" class="form-control" value="{{ $section->order }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Visibility Status</label>
                        <div class="form-check form-switch p-0 ms-4">
                            <input class="form-check-input" type="checkbox" name="enabled" id="enabled" style="width: 2.5em;" {{ $section->enabled ? 'checked' : '' }}>
                            <label class="form-check-label ms-2" for="enabled">Published (Visible on site)</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light p-4 d-grid">
                    <button class="btn btn-primary py-2 fw-bold">Save All Changes</button>
                    <p class="text-center small text-muted mt-3 mb-0">Last updated: {{ $section->updated_at->diffForHumans() }}</p>
                </div>
            </div>

            {{-- DEVELOPER PANEL (HIDDEN BY DEFAULT) --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-secondary">Developer Tools</h6>
                    <button type="button" class="btn btn-sm btn-link text-decoration-none" data-bs-toggle="collapse" data-bs-target="#devPanel">Toggle</button>
                </div>
                <div id="devPanel" class="collapse">
                    <div class="card-body p-4 pt-0">
                        <div class="alert alert-warning small py-2 mb-3">
                            Use this only if the form above doesn't support a specific key.
                        </div>
                        <textarea class="form-control font-monospace mb-2" rows="6" readonly>{{ json_encode($content, JSON_PRETTY_PRINT) }}</textarea>
                        <small class="text-muted">ID: #{{ $section->id }} | Page ID: {{ $section->page_id }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
<script>
    $(document).ready(function() {
        // Init CKEditor
        $('.ckeditor').each(function() {
            CKEDITOR.replace($(this).attr('name'), {
                height: 300,
                removeButtons: 'PasteFromWord'
            });
        });

        // Add Pill Row
        $('#add-pill').click(function() {
            const row = `
                <div class="input-group mb-2 pill-row">
                    <input type="text" name="config[pills][]" class="form-control" placeholder="New Pill Tag">
                    <button type="button" class="btn btn-outline-danger remove-pill"><i class="bi bi-x"></i></button>
                </div>
            `;
            $('#pills-container').append(row);
        });

        // Remove Pill Row
        $(document).on('click', '.remove-pill', function() {
            $(this).closest('.pill-row').remove();
        });
    });

    function previewImg(input, targetId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#' + targetId).attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
