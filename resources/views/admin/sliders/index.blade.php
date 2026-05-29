@extends('layouts.admin')

@section('title', 'Sliders')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Homepage Sliders</h2>
        <p class="text-secondary">Manage large images and calls to action for the hero slider.</p>
    </div>
</div>

<x-admin.data-table 
    title="Slider List" 
    :headers="['Image', 'Title', 'Subtitle', 'Status', 'Actions']" 
    :createRoute="route('admin.sliders.create')"
>
    @foreach($sliders as $slider)
    <tr>
        <td class="ps-4">
            @if($slider->image_path)
                <img src="{{ asset('storage/' . $slider->image_path) }}" class="rounded-3" width="100" height="40" style="object-fit:cover;">
            @else
                <i class="bi bi-image text-muted fs-4"></i>
            @endif
        </td>
        <td class="fw-bold">{{ $slider->title }}</td>
        <td class="small">{{ Str::limit($slider->subtitle, 40) }}</td>
        <td>
            @if($slider->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.sliders.edit', $slider) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.sliders.destroy', $slider) }}" method="POST" onsubmit="return confirm('Delete this slider?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
