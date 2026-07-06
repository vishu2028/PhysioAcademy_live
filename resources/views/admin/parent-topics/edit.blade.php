@extends('layouts.admin')

@section('title', 'Edit Parent Topic')

@section('content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <h1 class="h3 fw-bold mb-1">Edit Parent Topic</h1>
            <p class="text-muted mb-0">Update parent topic details.</p>
        </div>

        @include('admin.parent-topics._form', [
            'action' => route('admin.parent-topics.update', $parentTopic),
            'method' => 'PUT',
            'buttonText' => 'Update Parent Topic',
        ])
    </div>
@endsection
