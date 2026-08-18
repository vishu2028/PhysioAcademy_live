@extends('layouts.admin')

@section('title', 'Edit Announcement')

@section('content')

<div class="mb-4">

    <a href="{{ route('admin.community_and_announcements.index') }}"
       class="btn btn-light border btn-sm mb-3">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Community
    </a>

    <h2 class="fw-bold text-dark mb-1">
        Edit Announcement
    </h2>

    <p class="text-secondary mb-0">
        Update the selected announcement.
    </p>

</div>


<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-4">

        <form action="{{ route('admin.announcements.update', $announcement->id) }}"
              method="POST">

            @csrf
            @method('PUT')

            <div class="row">

                <div class="col-md-8">

                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Announcement Title
                        </label>

                        <input type="text"
                               name="title"
                               class="form-control rounded-3 py-2 @error('title') is-invalid @enderror"
                               value="{{ old('title', $announcement->title) }}"
                               required>

                        @error('title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card bg-light border-0 rounded-4 p-3 h-100">

                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Icon
                            </label>

                            <input type="text"
                                   name="icon"
                                   class="form-control rounded-3 @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', $announcement->icon) }}"
                                   required>

                            @error('icon')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Announcement Date
                            </label>

                            <input type="date"
                                   name="date"
                                   class="form-control rounded-3 @error('date') is-invalid @enderror"
                                   value="{{ old('date', $announcement->date) }}"
                                   required>

                            @error('date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </div>

            </div>


            <hr class="my-4">


            <div class="d-flex justify-content-end gap-2">

                <a href="{{ route('admin.community_and_announcements.index') }}"
                   class="btn btn-light border px-4 py-2 rounded-3">
                    Cancel
                </a>

                <button type="submit"
                        class="btn btn-primary px-4 py-2 rounded-3 fw-bold">

                    <i class="bi bi-check-lg me-1"></i>
                    Update Announcement

                </button>

            </div>

        </form>

    </div>

</div>

@endsection