@extends('layouts.admin')

@section('title', 'Page Sections')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.dashboard') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
    <h2 class="fw-bold text-dark mt-2">Manage Page Sections</h2>
</div>

<div class="mb-3">
    <a href="{{ route('admin.page-sections.create') }}" class="btn btn-primary">Create Section</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Page</th>
                    <th>Type</th>
                    <th>Order</th>
                    <th>Enabled</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $s)
                <tr>
                    <td>{{ $s->name }}</td>
                    <td>{{ $s->slug }}</td>
                    <td>{{ $s->page ? $s->page->title : '-' }}</td>
                    <td>{{ $s->type }}</td>
                    <td>{{ $s->order }}</td>
                    <td>{{ $s->enabled ? 'Yes' : 'No' }}</td>
                    <td class="text-end">
                        <a href="{{ route('admin.page-sections.edit', $s) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <a href="{{ route('admin.page-section-items.create') }}?section_id={{ $s->id }}" class="btn btn-sm btn-outline-primary">Items</a>
                        <form action="{{ route('admin.page-sections.destroy', $s) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Delete section?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection
