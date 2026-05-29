@extends('layouts.admin')

@section('title', 'Academic Years')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Academic Year Management</h2>
        <p class="text-secondary">Define study years and their semesters.</p>
    </div>
</div>

<x-admin.data-table 
    title="Year List" 
    :headers="['Name', 'Semesters', 'Units', 'Topics', 'Order', 'Status', 'Actions']" 
    :createRoute="route('admin.academic-years.create')"
>
    @foreach($years as $year)
    <tr>
        <td class="ps-4 fw-bold">{{ $year->name }}</td>
        <td>
            @foreach($year->semesters as $sem)
                <span class="badge bg-light text-dark border">{{ $sem->name }}</span>
            @endforeach
        </td>
        <td>{{ $year->units_count }}</td>
        <td>{{ $year->topics_count }}</td>
        <td>{{ $year->order }}</td>
        <td>
            @if($year->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.academic-years.edit', $year) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.academic-years.destroy', $year) }}" method="POST" onsubmit="return confirm('Delete this Year? This will also delete all topics under it.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
