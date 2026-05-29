@extends('layouts.admin')

@section('title', 'Activity Banners')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Activity Banners</h2>
        <p class="text-secondary">Sticky or floating announcement banners for the frontend.</p>
    </div>
</div>

<x-admin.data-table 
    title="Banner List" 
    :headers="['Title', 'Message', 'Type', 'Status', 'Actions']" 
    :createRoute="route('admin.banners.create')"
>
    @foreach($banners as $banner)
    <tr>
        <td class="ps-4 fw-bold">{{ $banner->title }}</td>
        <td><small>{{ Str::limit($banner->content, 50) }}</small></td>
        <td><span class="badge bg-light text-dark border">{{ strtoupper($banner->type ?? 'Announcement') }}</span></td>
        <td>
            @if($banner->status)
                <span class="badge bg-success-subtle text-success px-3">Published</span>
            @else
                <span class="badge bg-light text-secondary px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.banners.edit', $banner) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.banners.destroy', $banner) }}" method="POST" onsubmit="return confirm('Delete this banner?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
