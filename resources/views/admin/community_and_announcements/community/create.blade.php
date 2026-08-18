@extends('layouts.admin')

@section('title', 'Add Recent Activity')

@section('content')

<div class="mb-4">

    <a href="{{ route('admin.community_and_announcements.index') }}"
       class="btn btn-light border btn-sm mb-3">
        <i class="bi bi-arrow-left me-1"></i>
        Back to Community
    </a>

    <h2 class="fw-bold text-dark mb-1">
        Add Recent Activity
    </h2>

    <p class="text-secondary mb-0">
        Create a new activity for the live activity feed.
    </p>

</div>


<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-4">

        <form action="{{ route('admin.community.store') }}" method="POST">
            @csrf

            <div class="row">

                <div class="col-md-8">

                    {{-- Title --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Activity Title
                        </label>

                        <input type="text"
                               name="title"
                               class="form-control rounded-3 py-2 @error('title') is-invalid @enderror"
                               value="{{ old('title') }}"
                               placeholder="New Notes Added: Electrotherapy Unit 3"
                               required>

                        @error('title')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Subject --}}
                    <div class="mb-3">

                        <label class="form-label fw-bold small text-uppercase text-secondary">
                            Subject
                        </label>

                        <input type="text"
                               name="subject"
                               class="form-control rounded-3 py-2 @error('subject') is-invalid @enderror"
                               value="{{ old('subject') }}"
                               placeholder="Anatomy"
                               required>

                        @error('subject')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>


                <div class="col-md-4">

                    <div class="card bg-light border-0 rounded-4 p-3 h-100">

                        {{-- Time --}}
                        <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Time
                            </label>

                            <input type="text"
                                   name="time"
                                   class="form-control rounded-3 @error('time') is-invalid @enderror"
                                   value="{{ old('time') }}"
                                   placeholder="2 hours ago"
                                   required>

                            <div class="form-text">
                                Example: Today, Yesterday, 2 hours ago
                            </div>

                            @error('time')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>


                        {{-- Icon --}}
                        {{-- <div class="mb-3">

                            <label class="form-label fw-bold small text-uppercase text-secondary">
                                Icon
                            </label>

                            <input type="text"
                                   name="icon"
                                   class="form-control rounded-3 @error('icon') is-invalid @enderror"
                                   value="{{ old('icon', 'folder') }}"
                                   placeholder="folder"
                                   required>

                            <div class="form-text">
                                Example: folder, flame, help, refresh, megaphone
                            </div>

                            @error('icon')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div> --}}

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

                    <i class="bi bi-cloud-upload me-1"></i>
                    Save Activity

                </button>

            </div>

        </form>

    </div>

</div>

@endsection