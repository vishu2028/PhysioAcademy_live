@extends('layouts.admin')

@section('title', 'Add Team Member')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.team-members.index') }}" class="text-decoration-none small text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Team Members
        </a>
        <h2 class="fw-bold text-dark mt-2">Add Team Member</h2>
    </div>

    <form action="{{ route('admin.team-members.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.team-members._form')
    </form>
@endsection
