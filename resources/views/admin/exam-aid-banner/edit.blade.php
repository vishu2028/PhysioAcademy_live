@extends('layouts.admin')

@section('title', 'Edit Exam Aid Banner')

@section('content')

<div class="mb-4">

    <a href="{{ route('admin.exam-aid-banner.index') }}"
       class="btn btn-light border btn-sm mb-3">

        <i class="bi bi-arrow-left me-1"></i>
        Back to List

    </a>

    <h2 class="fw-bold text-dark">
        Edit Exam Aid Banner
    </h2>

    <p class="text-secondary">
        Update the Exam Aid banner content.
    </p>

</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-4">

        <form action="{{ route('admin.exam-aid-banner.update', $banner->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-8">

                    {{-- Main Title --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Main Title
                        </label>

                        <input
                            type="text"
                            name="main_title"
                            value="{{ old('main_title', $banner->main_title) }}"
                            class="form-control rounded-3 py-2 @error('main_title') is-invalid @enderror"
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
                        >{{ old('description', $banner->description) }}</textarea>

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
                            value="{{ old('heading_1', $banner->heading_1) }}"
                            class="form-control rounded-3 @error('heading_1') is-invalid @enderror"
                        >

                        @error('heading_1')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

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
                                value="{{ old('title_1', $banner->title_1) }}"
                                class="form-control rounded-3"
                            >

                        </div>

                        {{-- Title 2 --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Title 2
                            </label>

                            <input
                                type="text"
                                name="title_2"
                                value="{{ old('title_2', $banner->title_2) }}"
                                class="form-control rounded-3"
                            >

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
                                    value="{{ old('percentage_value', $banner->percentage_value) }}"
                                    min="0"
                                    max="100"
                                    class="form-control"
                                    required
                                >

                                <span class="input-group-text">
                                    %
                                </span>

                            </div>

                        </div>

                        <hr class="my-4">

                        <div class="mt-auto">

                            <button
                                type="submit"
                                class="btn btn-primary w-100 py-3 rounded-3 fw-bold"
                            >
                                <i class="bi bi-check-lg d-block fs-4 mb-1"></i>
                                Update Banner
                            </button>

                        </div>

                    </div>

                </div>

            </div>

        </form>

    </div>

</div>

@endsection