@extends('layouts.admin')

@section('title', 'Hero Sections')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Hero Sections</h2>
        <p class="text-secondary">Manage the hero section displayed at the top of the homepage.</p>
    </div>
</div>

<div class="alert alert-info border-0 rounded-4 shadow-sm mb-4">
    <i class="bi bi-info-circle me-2"></i> Only the most recently created <strong>Active</strong> hero section will be displayed on the frontend.
</div>

<x-admin.data-table 
    title="Hero Versioning" 
    :headers="['Image', 'Title', 'Button', 'Status', 'Actions']" 
    :createRoute="route('admin.hero.create')"
>
    @foreach($heroes as $hero)
    <tr>
        <td class="ps-4">
            @if($hero->image_path)
                <img src="{{ asset('storage/' . $hero->image_path) }}" class="rounded-3" width="80" height="45" style="object-fit:cover;">
            @else
                <div class="bg-light rounded-3 d-flex align-items-center justify-content-center" width="80" height="45">
                    <i class="bi bi-image text-secondary"></i>
                </div>
            @endif
        </td>
        <td class="fw-bold">{{ Str::limit($hero->title, 40) }}</td>
        <td><span class="badge bg-light text-dark border">{{ $hero->button_text }}</span></td>
        <td>
            @if($hero->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.hero.edit', $hero) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.hero.destroy', $hero) }}" method="POST" onsubmit="return confirm('Delete this hero section?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
