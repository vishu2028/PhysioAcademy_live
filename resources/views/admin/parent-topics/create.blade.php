@extends('layouts.admin')

@section('title', 'Create Parent Topic')

@section('content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <h1 class="h3 fw-bold mb-1">Create Parent Topic</h1>
            <p class="text-muted mb-0">Add a parent topic under a selected topic.</p>
        </div>

        @include('admin.parent-topics._form', [
            'action' => route('admin.parent-topics.store'),
            'method' => 'POST',
            'buttonText' => 'Create Parent Topic',
        ])
    </div>
@endsection
