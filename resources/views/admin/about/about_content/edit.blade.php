@extends('layouts.admin')

@section('title', 'Edit About Content')

@section('content')

<div class="mb-4">

    <a href="{{ route('admin.about_content.index') }}"
       class="btn btn-light border btn-sm mb-3">

        <i class="bi bi-arrow-left me-1"></i>
        Back to About

    </a>

    <h2 class="fw-bold text-dark mb-1">
        Edit About Content
    </h2>

    <p class="text-secondary mb-0">
        Update the main About section content and counters.
    </p>

</div>


<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-4">

        <form action="{{ route('admin.about_content.update', $aboutContent->id) }}"
              method="POST">

            @csrf
            @method('PUT')


            <div class="row">

                {{-- Main Title --}}
                <div class="col-md-12 mb-3">

                    <label class="form-label fw-bold small text-uppercase text-secondary">
                        Main Title
                    </label>

                    <input type="text"
                           name="main_title"
                           class="form-control rounded-3 py-2 @error('main_title') is-invalid @enderror"
                           value="{{ old('main_title', $aboutContent->main_title) }}"
                           required>

                    @error('main_title')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Main Description --}}
                <div class="col-md-12 mb-4">

                    <label class="form-label fw-bold small text-uppercase text-secondary">
                        Main Description
                    </label>

                    <textarea name="main_description"
                              rows="5"
                              class="form-control rounded-3 @error('main_description') is-invalid @enderror"
                              required>{{ old('main_description', $aboutContent->main_description) }}</textarea>

                    @error('main_description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Topic Count --}}
                <div class="col-md-4">

                    <div class="card bg-light border-0 rounded-4 p-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Topic Count
                        </label>

                        <input type="number"
                               name="topic_count"
                               min="0"
                               class="form-control rounded-3"
                               value="{{ old('topic_count', $aboutContent->topic_count) }}"
                               required>

                    </div>

                </div>


                {{-- Question Count --}}
                <div class="col-md-4">

                    <div class="card bg-light border-0 rounded-4 p-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Question Count
                        </label>

                        <input type="number"
                               name="question_count"
                               min="0"
                               class="form-control rounded-3"
                               value="{{ old('question_count', $aboutContent->question_count) }}"
                               required>

                    </div>

                </div>


                {{-- Student Count --}}
                <div class="col-md-4">

                    <div class="card bg-light border-0 rounded-4 p-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Student Count
                        </label>

                        <input type="number"
                               name="student_count"
                               min="0"
                               class="form-control rounded-3"
                               value="{{ old('student_count', $aboutContent->student_count) }}"
                               required>

                    </div>

                </div>

            </div>


            <hr class="my-4">


            <div class="d-flex justify-content-end gap-2">

                <a href="{{ route('admin.about_content.index') }}"
                   class="btn btn-light border px-4 py-2 rounded-3">

                    Cancel

                </a>


                <button type="submit"
                        class="btn btn-primary px-4 py-2 rounded-3 fw-bold">

                    <i class="bi bi-check-lg me-1"></i>
                    Update About Content

                </button>

            </div>

        </form>

    </div>

</div>

@endsection