@extends('layouts.admin')

@section('title', 'Platform Features')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Platform Features</h2>
        <p class="text-secondary">Manage the "Academic Support" section on your homepage.</p>
    </div>
</div>

<x-admin.data-table 
    title="Support Feature List" 
    :headers="['Icon', 'Title', 'Description', 'Order', 'Status', 'Actions']" 
    :createRoute="route('admin.features.create')"
>
    @foreach($features as $feature)
    <tr>
        <td class="ps-4">
            <div class="p-2 rounded-3 bg-light d-inline-block text-primary" style="--sc-color: {{ $feature->color }}">
                @if(str_contains($feature->icon, '<svg'))
                    {!! $feature->icon !!}
                @else
                    <i class="{{ $feature->icon }} fs-4"></i>
                @endif
            </div>
        </td>
        <td class="fw-bold">{{ $feature->title }}</td>
        <td class="small">{{ Str::limit($feature->description, 60) }}</td>
        <td><span class="badge bg-light text-dark border">{{ $feature->order }}</span></td>
        <td>
            @if($feature->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.features.edit', $feature) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.features.destroy', $feature) }}" method="POST" onsubmit="return confirm('Delete this feature?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
