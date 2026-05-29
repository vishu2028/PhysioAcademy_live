@extends('layouts.admin')

@section('title', 'CMS Pages')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">CMS Pages</h2>
        <p class="text-secondary">Create and manage dynamic content pages for your website.</p>
    </div>
</div>

<x-admin.data-table 
    title="Static & Dynamic Pages" 
    :headers="['Title', 'Slug', 'Visibility', 'Last Updated', 'Actions']" 
    :createRoute="route('admin.pages.create')"
>
    @foreach($pages as $page)
    <tr>
        <td class="ps-4 fw-bold">{{ $page->title }}</td>
        <td><code>/page/{{ $page->slug }}</code></td>
        <td>
            @if($page->status)
                <span class="badge bg-success-subtle text-success px-3">Public</span>
            @else
                <span class="badge bg-warning-subtle text-warning px-3">Draft</span>
            @endif
        </td>
        <td>{{ $page->updated_at->diffForHumans() }}</td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('page.show', $page->slug) }}" target="_blank" class="btn btn-light btn-sm rounded-3" title="View Page"><i class="bi bi-eye"></i></a>
                <a href="{{ route('admin.page-sections.index', ['page_id' => $page->id]) }}" class="btn btn-info btn-sm rounded-3 text-white" title="Manage Sections"><i class="bi bi-layers"></i></a>
                <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-primary btn-sm rounded-3" title="Edit Page"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.pages.destroy', $page) }}" method="POST" onsubmit="return confirm('Delete this page?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3" title="Delete Page"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
