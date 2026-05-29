@extends('layouts.admin')

@section('title', 'FAQs')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">Frequently Asked Questions</h2>
        <p class="text-secondary">Manage the help and support questions for students.</p>
    </div>
</div>

<x-admin.data-table 
    title="FAQ List" 
    :headers="['Question', 'Answer', 'Category', 'Order', 'Status', 'Actions']" 
    :createRoute="route('admin.faqs.create')"
>
    @foreach($faqs as $faq)
    <tr>
        <td class="ps-4 fw-bold">{{ Str::limit($faq->question, 50) }}</td>
        <td class="small">{{ Str::limit($faq->answer, 60) }}</td>
        <td><span class="badge bg-light text-primary border">{{ $faq->category ?? 'General' }}</span></td>
        <td>{{ $faq->order }}</td>
        <td>
            @if($faq->status)
                <span class="badge bg-success-subtle text-success px-3">Active</span>
            @else
                <span class="badge bg-danger-subtle text-danger px-3">Inactive</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.faqs.edit', $faq) }}" class="btn btn-primary btn-sm rounded-3"><i class="bi bi-pencil"></i></a>
                <form action="{{ route('admin.faqs.destroy', $faq) }}" method="POST" onsubmit="return confirm('Delete this FAQ?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
