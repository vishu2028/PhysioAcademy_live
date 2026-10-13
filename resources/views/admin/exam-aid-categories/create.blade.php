@extends('layouts.admin')

@section('title', 'Create Category')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.exam-aid-categories.index') }}" class="text-decoration-none small text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Categories
        </a>
        <h2 class="fw-bold text-dark mt-2">Create Material Category</h2>
    </div>

    <form action="{{ route('admin.exam-aid-categories.store') }}" method="POST">
        @csrf
        @include('admin.exam-aid-categories._form')
    </form>
@endsection
