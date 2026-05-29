@extends('layouts.admin')

@section('title', 'Edit Section')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.page-sections.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Sections
    </a>
    <h2 class="fw-bold text-dark mt-2">Edit Section</h2>
</div>

<form action="{{ route('admin.page-sections.update', $section) }}" method="POST">
    @csrf @method('PUT')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Section Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $section->name) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Slug</label>
                        <input type="text" name="slug" class="form-control" value="{{ old('slug', $section->slug) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Type</label>
                        <input type="text" name="type" class="form-control" value="{{ old('type', $section->type) }}">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Content (JSON/Configs)</label>
                        <div class="small text-muted mb-2">Available types/keys: 
                            <code>mission</code> (kicker, title, body, pills, images), 
                            <code>vision</code> (title, body), 
                            <code>closing</code> (title, cta_text, cta_url, etc.)
                        </div>
                        <textarea name="content" class="form-control font-monospace" rows="8">{{ old('content', json_encode($section->content, JSON_PRETTY_PRINT)) }}</textarea>
                    </div>
                </div>
            </div>

            @if(in_array($section->type, ['features', 'explore', 'team']))
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Section Items</h5>
                    <a href="{{ route('admin.page-section-items.create', ['section_id' => $section->id]) }}" class="btn btn-sm btn-primary">Add New Item</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Title</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($section->items as $item)
                                <tr>
                                    <td class="ps-4 fw-medium">{{ $item->title ?: 'No Title' }}</td>
                                    <td>{{ $item->order }}</td>
                                    <td>
                                        @if($item->enabled)
                                            <span class="badge bg-success-subtle text-success">Active</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">Disabled</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="{{ route('admin.page-section-items.edit', $item) }}" class="btn btn-light btn-sm"><i class="bi bi-pencil"></i></a>
                                            <form action="{{ route('admin.page-section-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Delete item?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-4 text-secondary">No items found for this section.</td>
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
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Attach to Page</label>
                        <select name="page_id" class="form-select">
                            <option value="">-- None --</option>
                            @foreach($pages as $p)
                                <option value="{{ $p->id }}" {{ $section->page_id == $p->id ? 'selected' : '' }}>{{ $p->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Order</label>
                        <input type="number" name="order" class="form-control" value="{{ $section->order }}">
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="enabled" id="enabled" {{ $section->enabled ? 'checked' : '' }}>
                        <label class="form-check-label" for="enabled">Enabled</label>
                    </div>
                </div>
                <div class="card-footer bg-light p-3 d-grid">
                    <button class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</form>

@endsection
