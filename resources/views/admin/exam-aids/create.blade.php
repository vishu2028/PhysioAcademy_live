@extends('layouts.admin')

@section('content')
    <div class="mb-4">
        <a href="{{ route('admin.exam-aids.index') }}" class="text-decoration-none small text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Exam Aid
        </a>

        <h2 class="fw-bold text-dark mt-2">Create Exam Aid</h2>
    </div>

    <form action="{{ route('admin.exam-aids.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        @include('admin.exam-aids._form', [
            'examAid' => new \App\Models\ExamAid(),
            'buttonText' => 'Create Exam Aid'
        ])
    </form>
@endsection
