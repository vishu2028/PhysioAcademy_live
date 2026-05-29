@extends('layouts.admin')

@section('title', 'Topics')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Topic Management</h2>
        <p class="text-secondary">Create and manage curriculum topics and learning materials.</p>
    </div>
</div>

<x-admin.data-table 
    title="Topic List" 
    :headers="['Title', 'Subject', 'Year/Semester', 'Module', 'Materials', 'Status', 'Actions']" 
    :createRoute="route('admin.topics.create')"
>
    @foreach($topics as $topic)
    <tr>
        <td class="ps-4">
            <div class="fw-bold">{{ $topic->title }}</div>
            <div class="small text-muted">{{ Str::limit($topic->description, 40) }}</div>
        </td>
        <td><span class="badge bg-primary-subtle text-primary">{{ $topic->subject->name ?? 'N/A' }}</span></td>
        <td>
            <div class="small fw-bold">{{ $topic->academicYear->name ?? 'N/A' }}</div>
            <div class="small text-muted">{{ $topic->semester->name ?? '' }}</div>
        </td>
        <td>{{ $topic->module_number ?? '-' }}</td>
        <td><span class="badge bg-info-subtle text-info">{{ $topic->materials()->count() }}</span></td>
        <td>
            @if($topic->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.topics.edit', $topic) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.topics.destroy', $topic) }}" method="POST" onsubmit="return confirm('Delete this Topic?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
