@extends('layouts.admin')

@section('title', 'About Section')

@section('content')

<div class="mb-4">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h2 class="fw-bold text-dark mb-1">
                About Section
            </h2>

            <p class="text-secondary mb-0">
                Manage About section content, counters, and timeline.
            </p>

        </div>


        {{-- Add About Content --}}
        @if(!$aboutContent)

            <a href="{{ route('admin.about_content.create') }}"
               class="btn btn-primary rounded-3 px-4 py-2 fw-bold">

                <i class="bi bi-plus-lg me-1"></i>
                Add About Content

            </a>

        @else

            <a href="{{ route('admin.about_content.edit', $aboutContent->id) }}"
               class="btn btn-primary rounded-3 px-4 py-2 fw-bold">

                <i class="bi bi-pencil me-1"></i>
                Edit About Content

            </a>

        @endif

    </div>

</div>


{{-- Success Message --}}
@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm"
         role="alert">

        <i class="bi bi-check-circle me-2"></i>

        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Error Message --}}
@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show rounded-3 border-0 shadow-sm"
         role="alert">

        <i class="bi bi-exclamation-circle me-2"></i>

        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Info Message --}}
@if(session('info'))

    <div class="alert alert-info alert-dismissible fade show rounded-3 border-0 shadow-sm"
         role="alert">

        <i class="bi bi-info-circle me-2"></i>

        {{ session('info') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- Validation Errors --}}
@if($errors->any())

    <div class="alert alert-danger rounded-3 border-0 shadow-sm">

        <ul class="mb-0">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif



<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-0">


        {{-- ====================================================== --}}
        {{-- TABS --}}
        {{-- ====================================================== --}}

        <ul class="nav nav-tabs px-4 pt-3 border-bottom"
            id="aboutTabs"
            role="tablist">


            {{-- Main Content --}}
            <li class="nav-item" role="presentation">

                <button class="nav-link active fw-semibold"
                        id="content-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#content"
                        type="button"
                        role="tab">

                    <i class="bi bi-file-text me-1"></i>

                    Main Content

                </button>

            </li>


            {{-- Timeline --}}
            <li class="nav-item" role="presentation">

                <button class="nav-link fw-semibold"
                        id="timeline-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#timeline"
                        type="button"
                        role="tab">

                    <i class="bi bi-clock-history me-1"></i>

                    Timeline

                    <span class="badge bg-primary-subtle text-primary ms-1">
                        {{ $timelines->count() }}
                    </span>

                </button>

            </li>

        </ul>



        <div class="tab-content">


            {{-- ====================================================== --}}
            {{-- MAIN CONTENT --}}
            {{-- ====================================================== --}}

            <div class="tab-pane fade show active"
                 id="content"
                 role="tabpanel">

                <div class="p-4">


                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Main About Content
                            </h5>

                            <p class="text-secondary small mb-0">
                                Content and counters displayed in the About section.
                            </p>

                        </div>


                        @if($aboutContent)

                            <a href="{{ route('admin.about_content.edit', $aboutContent->id) }}"
                               class="btn btn-primary btn-sm rounded-3">

                                <i class="bi bi-pencil me-1"></i>

                                Edit Content

                            </a>

                        @endif

                    </div>



                    @if($aboutContent)

                        <div class="row g-4">


                            {{-- Main Title --}}
                            <div class="col-md-6">

                                <div class="card bg-light border-0 rounded-4 h-100">

                                    <div class="card-body">

                                        <label class="fw-bold small text-uppercase text-secondary mb-2">
                                            Main Title
                                        </label>

                                        <h4 class="fw-bold mb-0">

                                            {!! $aboutContent->main_title !!}

                                        </h4>

                                    </div>

                                </div>

                            </div>



                            {{-- Main Description --}}
                            <div class="col-md-6">

                                <div class="card bg-light border-0 rounded-4 h-100">

                                    <div class="card-body">

                                        <label class="fw-bold small text-uppercase text-secondary mb-2">
                                            Main Description
                                        </label>

                                        <p class="text-secondary mb-0">

                                            {{ $aboutContent->main_description }}

                                        </p>

                                    </div>

                                </div>

                            </div>


                        </div>



                        {{-- ====================================================== --}}
                        {{-- COUNTERS --}}
                        {{-- ====================================================== --}}

                        <div class="row g-3 mt-2">


                            {{-- Topics --}}
                            <div class="col-md-4">

                                <div class="card border-0 bg-primary-subtle rounded-4">

                                    <div class="card-body text-center">

                                        <div class="fs-3 fw-bold text-primary">

                                            {{ number_format($aboutContent->topic_count) }}+

                                        </div>

                                        <div class="small fw-semibold text-secondary">
                                            Topics
                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Questions --}}
                            <div class="col-md-4">

                                <div class="card border-0 bg-success-subtle rounded-4">

                                    <div class="card-body text-center">

                                        <div class="fs-3 fw-bold text-success">

                                            {{ number_format($aboutContent->question_count) }}+

                                        </div>

                                        <div class="small fw-semibold text-secondary">
                                            Questions
                                        </div>

                                    </div>

                                </div>

                            </div>


                            {{-- Students --}}
                            <div class="col-md-4">

                                <div class="card border-0 bg-warning-subtle rounded-4">

                                    <div class="card-body text-center">

                                        <div class="fs-3 fw-bold text-warning">

                                            {{ number_format($aboutContent->student_count) }}+

                                        </div>

                                        <div class="small fw-semibold text-secondary">
                                            Students
                                        </div>

                                    </div>

                                </div>

                            </div>


                        </div>


                    @else


                        {{-- ====================================================== --}}
                        {{-- NO ABOUT CONTENT --}}
                        {{-- ====================================================== --}}

                        <div class="text-center py-5">

                            <i class="bi bi-file-earmark-text fs-1 text-secondary"></i>

                            <h5 class="fw-bold mt-3">
                                No About Content Found
                            </h5>

                            <p class="text-secondary">
                                Add your main About section content and counters.
                            </p>

                            <a href="{{ route('admin.about_content.create') }}"
                               class="btn btn-primary rounded-3">

                                <i class="bi bi-plus-lg me-1"></i>

                                Add About Content

                            </a>

                        </div>


                    @endif


                </div>

            </div>



            {{-- ====================================================== --}}
            {{-- TIMELINE --}}
            {{-- ====================================================== --}}

            <div class="tab-pane fade"
                 id="timeline"
                 role="tabpanel">

                <div class="p-4">


                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                About Timeline
                            </h5>

                            <p class="text-secondary small mb-0">
                                Manage timeline entries displayed on the website.
                            </p>

                        </div>


                        {{-- Add Timeline --}}
                        <a href="{{ route('admin.about_timelines.create') }}"
                           class="btn btn-primary btn-sm rounded-3">

                            <i class="bi bi-plus-lg me-1"></i>

                            Add Timeline

                        </a>

                    </div>



                    {{-- ====================================================== --}}
                    {{-- TIMELINE TABLE --}}
                    {{-- ====================================================== --}}

                    <div class="table-responsive">

                        <table class="table align-middle mb-0">


                            <thead class="table-light">

                                <tr>

                                    <th width="60">
                                        #
                                    </th>

                                    <th width="130">
                                        Year
                                    </th>

                                    <th>
                                        Timeline Title
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th width="150">
                                        Actions
                                    </th>

                                </tr>

                            </thead>



                            <tbody>


                                @forelse($timelines as $timeline)


                                    <tr>


                                        {{-- Number --}}
                                        <td class="fw-semibold text-secondary">

                                            {{ $loop->iteration }}

                                        </td>



                                        {{-- Year --}}
                                        <td>

                                            <span class="badge bg-primary-subtle text-primary">

                                                {{ $timeline->year }}

                                            </span>

                                        </td>



                                        {{-- Title --}}
                                        <td class="fw-semibold">

                                            {{ $timeline->title }}

                                        </td>



                                        {{-- Description --}}
                                        <td class="text-secondary">

                                            {{ $timeline->description }}

                                        </td>



                                        {{-- Actions --}}
                                        <td>

                                            <div class="d-flex gap-1">


                                                {{-- Edit --}}
                                                <a href="{{ route('admin.about_timelines.edit', $timeline->id) }}"
                                                   class="btn btn-sm btn-light border"
                                                   title="Edit">

                                                    <i class="bi bi-pencil"></i>

                                                </a>



                                                {{-- Delete --}}
                                                <form action="{{ route('admin.about_timelines.destroy', $timeline->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this timeline?');">

                                                    @csrf

                                                    @method('DELETE')

                                                    <button type="submit"
                                                            class="btn btn-sm btn-light border text-danger"
                                                            title="Delete">

                                                        <i class="bi bi-trash"></i>

                                                    </button>

                                                </form>


                                            </div>

                                        </td>


                                    </tr>


                                @empty


                                    {{-- No Timelines --}}
                                    <tr>

                                        <td colspan="5"
                                            class="text-center py-5">

                                            <div class="text-secondary">

                                                <i class="bi bi-clock-history fs-1"></i>

                                                <p class="mt-2 mb-2 fw-semibold">
                                                    No timeline entries found.
                                                </p>

                                                <a href="{{ route('admin.about_timelines.create') }}"
                                                   class="btn btn-primary btn-sm rounded-3">

                                                    <i class="bi bi-plus-lg me-1"></i>

                                                    Add Timeline

                                                </a>

                                            </div>

                                        </td>

                                    </tr>


                                @endforelse


                            </tbody>

                        </table>

                    </div>


                </div>

            </div>


        </div>

    </div>

</div>

@endsection