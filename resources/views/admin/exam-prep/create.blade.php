@extends('layouts.admin')

@section('title', 'Add Card')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.exam-prep.index') }}" class="text-decoration-none small text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Exam Prep Section
        </a>
        <h2 class="fw-bold text-dark mt-2">Add Exam Prep Card</h2>
    </div>

    <form action="{{ route('admin.exam-prep-cards.store') }}" method="POST">
        @csrf
        @include('admin.exam-prep._form')
    </form>
@endsection
