@extends('layouts.admin')

@section('title', 'Navigation Menus')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Navigation Menus</h2>
        <p class="text-secondary">Manage site navigation and footer link groups.</p>
    </div>
</div>

<x-admin.data-table 
    title="Main Menus" 
    :headers="['Name', 'Location Slug', 'Items Count', 'Status', 'Actions']" 
    :createRoute="route('admin.menus.create')"
>
    @foreach($menus as $menu)
    <tr>
        <td class="ps-4 fw-bold">{{ $menu->name }}</td>
        <td><code>{{ $menu->location }}</code></td>
        <td><span class="badge bg-light text-dark border">{{ $menu->items->count() }} Items</span></td>
        <td>
            @if($menu->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" onsubmit="return confirm('Delete this menu?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>

<div class="mt-4">
    <div class="alert alert-warning border-0 rounded-4 shadow-sm">
        <h6 class="fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> Reserved Location Slugs:</h6>
        <ul class="mb-0 small">
            <li><code>header_menu</code>: Used for the main top navigation.</li>
            <li><code>footer_quick_links</code>: Used for the first column in footer.</li>
            <li><code>footer_support</code>: Used for the second column in footer.</li>
        </ul>
    </div>
</div>
@endsection
