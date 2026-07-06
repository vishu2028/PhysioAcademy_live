@extends('layouts.admin')

@section('title', 'Create Topic')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.unit-topics.index') }}" class="text-decoration-none small text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Topics
        </a>

        <h2 class="fw-bold text-dark mt-2">Create New Topic</h2>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('admin.unit-topics.store') }}" method="POST">
                @csrf

                @include('admin.unit-topics._form')

                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary rounded-3">
                        Save Topic
                    </button>

                    <a href="{{ route('admin.unit-topics.index') }}" class="btn btn-secondary rounded-3">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
