@extends('layouts.admin')

@section('title', 'User Inquiries')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark">User Inquiries</h2>
        <p class="text-secondary">Manage contact form submissions and student messages.</p>
    </div>
</div>

<x-admin.data-table 
    title="Incoming Messages" 
    :headers="['Sender', 'Subject', 'Received', 'Status', 'Actions']"
>
    @foreach($messages as $message)
    <tr class="{{ !$message->is_read ? 'bg-light-primary fw-bold' : '' }}">
        <td class="ps-4">
            <div>
                <div class="text-dark">{{ $message->name }}</div>
                <div class="small text-muted">{{ $message->email }}</div>
            </div>
        </td>
        <td>{{ $message->subject ?? 'No Subject' }}</td>
        <td>{{ $message->created_at->diffForHumans() }}</td>
        <td>
            @if(!$message->is_read)
                <span class="badge bg-primary rounded-pill px-3">New</span>
            @else
                <span class="badge bg-light text-secondary rounded-pill px-3">Read</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.messages.show', $message) }}" class="btn btn-light btn-sm rounded-3"><i class="bi bi-eye"></i></a>
                <form action="{{ route('admin.messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this message?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                </form>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>

@if($messages->hasPages())
<div class="mt-4">
    {{ $messages->links() }}
</div>
@endif
@endsection
