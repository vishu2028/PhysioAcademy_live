@extends('layouts.admin')

@section('title', 'Community & Announcements')

@section('content')

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center">

        <div>
            <h2 class="fw-bold text-dark mb-1">
                Community & Announcements
            </h2>

            <p class="text-secondary mb-0">
                Manage recent activities, announcements, and trending topics.
            </p>
        </div>

        <a href=""
           class="btn btn-primary rounded-3 px-4 py-2 fw-bold">

            <i class="bi bi-plus-lg me-1"></i>
            Add Content

        </a>

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


<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-0">

        {{-- Tabs --}}
        <ul class="nav nav-tabs px-4 pt-3 border-bottom"
            id="communityTabs"
            role="tablist">

            {{-- Recent Activities --}}
            <li class="nav-item" role="presentation">

                <button class="nav-link active fw-semibold"
                        id="activities-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#activities"
                        type="button"
                        role="tab">

                    <i class="bi bi-activity me-1"></i>

                    Recent Activities

                    <span class="badge bg-primary-subtle text-primary ms-1">
                        5
                    </span>

                </button>

            </li>


            {{-- Announcements --}}
            <li class="nav-item" role="presentation">

                <button class="nav-link fw-semibold"
                        id="announcements-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#announcements"
                        type="button"
                        role="tab">

                    <i class="bi bi-megaphone me-1"></i>

                    Announcements

                    <span class="badge bg-primary-subtle text-primary ms-1">
                        3
                    </span>

                </button>

            </li>


            {{-- Trending --}}
            <li class="nav-item" role="presentation">

                <button class="nav-link fw-semibold"
                        id="trending-tab"
                        data-bs-toggle="tab"
                        data-bs-target="#trending"
                        type="button"
                        role="tab">

                    <i class="bi bi-fire me-1"></i>

                    Trending

                    <span class="badge bg-primary-subtle text-primary ms-1">
                        8
                    </span>

                </button>

            </li>

        </ul>


        <div class="tab-content">


            {{-- ====================================================== --}}
            {{-- RECENT ACTIVITIES --}}
            {{-- ====================================================== --}}

            <div class="tab-pane fade show active"
                 id="activities"
                 role="tabpanel">

                <div class="p-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>
                            <h5 class="fw-bold mb-1">
                                Recent Activities
                            </h5>

                            <p class="text-secondary small mb-0">
                                Manage the live activity feed shown on the website.
                            </p>
                        </div>

                        <a href="{{ route('admin.community.create') }}"
                           class="btn btn-primary btn-sm rounded-3">

                            <i class="bi bi-plus-lg me-1"></i>
                            Add Activity

                        </a>

                    </div>


                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th width="60">#</th>

                                    <th>Activity</th>

                                    <th>Subject</th>

                                    <th>Time</th>

                                    <th>Icon</th>

                                    <th width="150">Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                                {{-- Activity 1 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        1
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-primary-subtle text-primary rounded-3 p-2">

                                                <i class="bi bi-folder"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                New Notes Added: Electrotherapy Unit 3
                                            </span>

                                        </div>

                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            Year 3
                                        </span>
                                    </td>

                                    <td class="text-secondary">
                                        2 hours ago
                                    </td>

                                    <td>
                                        <code>folder</code>
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border"
                                               title="Edit">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger"
                                                    title="Delete"
                                                    onclick="alert('Delete functionality will be added later.')">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                {{-- Activity 2 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        2
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Brachial Plexus guide updated with new diagrams
                                            </span>

                                        </div>

                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            Anatomy
                                        </span>
                                    </td>

                                    <td class="text-secondary">
                                        5 hours ago
                                    </td>

                                    <td>
                                        <code>flame</code>
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                {{-- Activity 3 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        3
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-warning-subtle text-warning rounded-3 p-2">

                                                <i class="bi bi-question-circle"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                32 new doubts answered this week
                                            </span>

                                        </div>

                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            All Subjects
                                        </span>
                                    </td>

                                    <td class="text-secondary">
                                        Today
                                    </td>

                                    <td>
                                        <code>help</code>
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                {{-- Activity 4 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        4
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-info-subtle text-info rounded-3 p-2">

                                                <i class="bi bi-arrow-repeat"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Curriculum update: New competency topics added
                                            </span>

                                        </div>

                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            Curriculum
                                        </span>
                                    </td>

                                    <td class="text-secondary">
                                        Yesterday
                                    </td>

                                    <td>
                                        <code>refresh</code>
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                {{-- Activity 5 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        5
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-primary-subtle text-primary rounded-3 p-2">

                                                <i class="bi bi-megaphone"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Gait Cycle flowchart now available in Resources
                                            </span>

                                        </div>

                                    </td>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            Biomechanics
                                        </span>
                                    </td>

                                    <td class="text-secondary">
                                        2 days ago
                                    </td>

                                    <td>
                                        <code>megaphone</code>
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- ====================================================== --}}
            {{-- ANNOUNCEMENTS --}}
            {{-- ====================================================== --}}

            <div class="tab-pane fade"
                 id="announcements"
                 role="tabpanel">

                <div class="p-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Announcements
                            </h5>

                            <p class="text-secondary small mb-0">
                                Manage announcements displayed on the website.
                            </p>

                        </div>

                        <a href="{{ route('admin.announcements.create') }}"
                           class="btn btn-primary btn-sm rounded-3">

                            <i class="bi bi-plus-lg me-1"></i>
                            Add Announcement

                        </a>

                    </div>


                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th width="60">#</th>

                                    <th>Icon</th>

                                    <th>Title</th>

                                    <th>Date</th>

                                    <th width="150">Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                                {{-- Announcement 1 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        1
                                    </td>

                                    <td>

                                        <div class="bg-primary-subtle text-primary rounded-3 p-2 d-inline-flex">

                                            <i class="bi bi-megaphone"></i>

                                        </div>

                                    </td>

                                    <td class="fw-semibold">
                                        New 2024 Syllabus Fully Mapped
                                    </td>

                                    <td class="text-secondary">
                                        Jan 15, 2025
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                {{-- Announcement 2 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        2
                                    </td>

                                    <td>

                                        <div class="bg-primary-subtle text-primary rounded-3 p-2 d-inline-flex">

                                            <i class="bi bi-megaphone"></i>

                                        </div>

                                    </td>

                                    <td class="fw-semibold">
                                        University Exam Pattern Analysis Released
                                    </td>

                                    <td class="text-secondary">
                                        Jan 10, 2025
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                {{-- Announcement 3 --}}
                                <tr>

                                    <td class="fw-semibold text-secondary">
                                        3
                                    </td>

                                    <td>

                                        <div class="bg-primary-subtle text-primary rounded-3 p-2 d-inline-flex">

                                            <i class="bi bi-megaphone"></i>

                                        </div>

                                    </td>

                                    <td class="fw-semibold">
                                        Clinical Cases Section Coming Soon
                                    </td>

                                    <td class="text-secondary">
                                        Jan 5, 2025
                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- ====================================================== --}}
            {{-- TRENDING --}}
            {{-- ====================================================== --}}

            <div class="tab-pane fade"
                 id="trending"
                 role="tabpanel">

                <div class="p-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">

                        <div>

                            <h5 class="fw-bold mb-1">
                                Trending Topics
                            </h5>

                            <p class="text-secondary small mb-0">
                                Manage topics displayed in the trending ticker.
                            </p>

                        </div>

                        <a href="{{ route('admin.trending.create') }}"
                           class="btn btn-primary btn-sm rounded-3">

                            <i class="bi bi-plus-lg me-1"></i>
                            Add Trending Topic

                        </a>

                    </div>


                    <div class="table-responsive">

                        <table class="table align-middle mb-0">

                            <thead class="table-light">

                                <tr>

                                    <th width="80">Order</th>

                                    <th>Trending Topic</th>

                                    <th width="150">Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            1
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Brachial Plexus
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            2
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Gait Cycle
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            3
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                UMN vs LMN
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            4
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Muscle Spindle
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            5
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Reflex Arc
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            6
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Spinal Cord Tracts
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            7
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Muscle Contraction
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>


                                <tr>

                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            8
                                        </span>
                                    </td>

                                    <td>

                                        <div class="d-flex align-items-center gap-3">

                                            <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                <i class="bi bi-fire"></i>

                                            </div>

                                            <span class="fw-semibold">
                                                Cerebellum Functions
                                            </span>

                                        </div>

                                    </td>

                                    <td>

                                        <div class="d-flex gap-1">

                                            <a href="#"
                                               class="btn btn-sm btn-light border">

                                                <i class="bi bi-pencil"></i>

                                            </a>

                                            <button type="button"
                                                    class="btn btn-sm btn-light border text-danger">

                                                <i class="bi bi-trash"></i>

                                            </button>

                                        </div>

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

@endsection