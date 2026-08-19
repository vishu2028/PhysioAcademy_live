@extends('layouts.admin')

@section('title', 'Edit About Timeline')

@section('content')

<div class="mb-4">

    <a href="{{ route('admin.about_content.index') }}"
       class="btn btn-light border btn-sm mb-3">

        <i class="bi bi-arrow-left me-1"></i>
        Back to About

    </a>

    <h2 class="fw-bold text-dark mb-1">
        Edit About Timeline
    </h2>

    <p class="text-secondary mb-0">
        Update the selected timeline entry.
    </p>

</div>


<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-4">

        <form action="{{ route('admin.about_timelines.update', $timeline->id) }}"
              method="POST">

            @csrf
            @method('PUT')


            <div class="row">

                {{-- Year --}}
                <div class="col-md-4 mb-3">

                    <label class="form-label fw-bold small text-uppercase text-secondary">
                        Year
                    </label>

                    <input type="text"
                           name="year"
                           class="form-control rounded-3 @error('year') is-invalid @enderror"
                           value="{{ old('year', $timeline->year) }}"
                           required>

                    @error('year')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Title --}}
                <div class="col-md-8 mb-3">

                    <label class="form-label fw-bold small text-uppercase text-secondary">
                        Timeline Title
                    </label>

                    <input type="text"
                           name="title"
                           class="form-control rounded-3 @error('title') is-invalid @enderror"
                           value="{{ old('title', $timeline->title) }}"
                           required>

                    @error('title')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                {{-- Description --}}
                <div class="col-md-12 mb-3">

                    <label class="form-label fw-bold small text-uppercase text-secondary">
                        Description
                    </label>

                    <textarea name="description"
                              rows="5"
                              class="form-control rounded-3 @error('description') is-invalid @enderror"
                              required>{{ old('description', $timeline->description) }}</textarea>

                    @error('description')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

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
                    Update Timeline

                </button>

            </div>

        </form>

    </div>

</div>

@endsection