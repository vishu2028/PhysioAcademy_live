@extends('layouts.admin')

@section('title', 'Add Exam Aid Banner')

@section('content')

<div class="mb-4">

    <a href="{{ route('admin.exam-aid-banner.index') }}"
       class="btn btn-light border btn-sm mb-3">

        <i class="bi bi-arrow-left me-1"></i>
        Back to List

    </a>

    <h2 class="fw-bold text-dark">
        Add Exam Aid Banner
    </h2>

    <p class="text-secondary">
        Create content for the Exam Aid banner.
    </p>

</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-4">

        <form action="{{ route('admin.exam-aid-banner.store') }}"
              method="POST">

            @csrf

            <div class="row">

                {{-- Left Side --}}
                <div class="col-md-8">

                    {{-- Main Title --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Main Title
                        </label>

                        <input
                            type="text"
                            name="main_title"
                            value="{{ old('main_title') }}"
                            class="form-control rounded-3 py-2 @error('main_title') is-invalid @enderror"
                            placeholder="Enter main title"
                            required
                        >

                        @error('main_title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Description --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Description
                        </label>

                        <textarea
                            name="description"
                            rows="7"
                            class="form-control rounded-3 @error('description') is-invalid @enderror"
                            placeholder="Enter description..."
                        >{{ old('description') }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    {{-- Heading 1 --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Heading 1
                        </label>

                        <input
                            type="text"
                            name="heading_1"
                            value="{{ old('heading_1') }}"
                            class="form-control rounded-3 @error('heading_1') is-invalid @enderror"
                            placeholder="Enter heading 1"
                        >

                        @error('heading_1')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                {{-- Right Side --}}
                <div class="col-md-4">

                    <div class="card bg-light border-0 rounded-4 p-3 h-100">

                        {{-- Title 1 --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Title 1
                            </label>

                            <input
                                type="text"
                                name="title_1"
                                value="{{ old('title_1') }}"
                                class="form-control rounded-3 @error('title_1') is-invalid @enderror"
                                placeholder="Enter title 1"
                            >

                            @error('title_1')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Title 2 --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Title 2
                            </label>

                            <input
                                type="text"
                                name="title_2"
                                value="{{ old('title_2') }}"
                                class="form-control rounded-3 @error('title_2') is-invalid @enderror"
                                placeholder="Enter title 2"
                            >

                            @error('title_2')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Percentage --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Percentage Value
                            </label>

                            <div class="input-group">

                                <input
                                    type="number"
                                    name="percentage_value"
                                    value="{{ old('percentage_value', 0) }}"
                                    min="0"
                                    max="100"
                                    class="form-control @error('percentage_value') is-invalid @enderror"
                                    placeholder="0"
                                    required
                                >

                                <span class="input-group-text">
                                    %
                                </span>

                            </div>

                            @error('percentage_value')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Enter a value between 0 and 100.
                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="mt-auto">

                            <button
                                type="submit"
                                class="btn btn-primary w-100 py-3 rounded-3 fw-bold"
                            >
                                <i class="bi bi-cloud-upload d-block fs-4 mb-1"></i>
                                Save Exam Aid Banner
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection