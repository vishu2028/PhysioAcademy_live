@extends('layouts.admin')

@section('title', 'Subjects')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Subject Management</h2>
        <p class="text-secondary">Create and manage academic subjects for topics categorization.</p>
    </div>
</div>

<x-admin.data-table 
    title="Subject List" 
    :headers="['Icon', 'Name', 'Topics', 'Order', 'Status', 'Actions']" 
    :createRoute="route('admin.subjects.create')"
>
    @foreach($subjects as $subject)
    <tr>
        <td class="ps-4">
            @if($subject->icon)
                <span class="fs-4">{{ $subject->icon }}</span>
            @elseif($subject->image)
                <img src="{{ asset('storage/' . $subject->image) }}" class="rounded" width="40" height="40" style="object-fit: cover;">
            @else
                <span class="text-muted">No Icon</span>
            @endif
        </td>
        <td class="fw-bold">{{ $subject->name }}</td>
        <td>{{ $subject->topics_count ?? $subject->topics()->count() }}</td>
        <td>{{ $subject->order }}</td>
        <td>
            @if($subject->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.subjects.edit', $subject) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.subjects.destroy', $subject) }}" method="POST" onsubmit="return confirm('Delete this Subject? This will also delete all topics under it.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
