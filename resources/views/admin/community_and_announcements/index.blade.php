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

        <a href="{{ route('admin.community.create') }}"
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


        {{-- ====================================================== --}}
        {{-- TABS --}}
        {{-- ====================================================== --}}

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
                        {{ $activities->count() }}
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
                        {{ $announcements->count() }}
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
                        {{ $trendings->count() }}
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

                                    <th width="150">Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($activities as $activity)

                                    <tr>

                                        <td class="fw-semibold text-secondary">
                                            {{ $loop->iteration }}
                                        </td>


                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <div class="bg-primary-subtle text-primary rounded-3 p-2">

                                                    <i class="bi bi-folder"></i>

                                                </div>

                                                <span class="fw-semibold">
                                                    {{ $activity->title }}
                                                </span>

                                            </div>

                                        </td>


                                        <td>

                                            <span class="badge bg-light text-dark border">
                                                {{ $activity->subject }}
                                            </span>

                                        </td>


                                        <td class="text-secondary">
                                            {{ $activity->time }}
                                        </td>


                                        <td>

                                            <div class="d-flex gap-1">

                                                {{-- Edit --}}
                                                <a href="{{ route('admin.community.edit', $activity->id) }}"
                                                   class="btn btn-sm btn-light border"
                                                   title="Edit">

                                                    <i class="bi bi-pencil"></i>

                                                </a>


                                                {{-- Delete --}}
                                                <form action="{{ route('admin.community.destroy', $activity->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this activity?');">

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

                                    <tr>

                                        <td colspan="5"
                                            class="text-center text-secondary py-5">

                                            <i class="bi bi-inbox fs-3 d-block mb-2"></i>

                                            No recent activities found.

                                        </td>

                                    </tr>

                                @endforelse

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

                                @forelse($announcements as $announcement)

                                    <tr>

                                        <td class="fw-semibold text-secondary">
                                            {{ $loop->iteration }}
                                        </td>


                                        <td>

                                            <div class="bg-primary-subtle text-primary rounded-3 p-2 d-inline-flex">

                                                <i class="bi bi-{{ $announcement->icon }}"></i>

                                            </div>

                                        </td>


                                        <td class="fw-semibold">
                                            {{ $announcement->title }}
                                        </td>


                                        <td class="text-secondary">

                                            {{ \Carbon\Carbon::parse($announcement->date)->format('M d, Y') }}

                                        </td>


                                        <td>

                                            <div class="d-flex gap-1">

                                                {{-- Edit --}}
                                                <a href="{{ route('admin.announcements.edit', $announcement->id) }}"
                                                   class="btn btn-sm btn-light border"
                                                   title="Edit">

                                                    <i class="bi bi-pencil"></i>

                                                </a>


                                                {{-- Delete --}}
                                                <form action="{{ route('admin.announcements.destroy', $announcement->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this announcement?');">

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

                                    <tr>

                                        <td colspan="5"
                                            class="text-center text-secondary py-5">

                                            <i class="bi bi-megaphone fs-3 d-block mb-2"></i>

                                            No announcements found.

                                        </td>

                                    </tr>

                                @endforelse

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
                                   

                                    <th>
                                        Trending Topic
                                    </th>

                                    <th width="150">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @forelse($trendings as $trending)

                                    <tr>                                       

                                        <td>

                                            <div class="d-flex align-items-center gap-3">

                                                <div class="bg-danger-subtle text-danger rounded-3 p-2">

                                                    <i class="bi bi-fire"></i>

                                                </div>

                                                <span class="fw-semibold">
                                                    {{ $trending->title }}
                                                </span>

                                            </div>

                                        </td>


                                        <td>

                                            <div class="d-flex gap-1">

                                                {{-- Edit --}}
                                                <a href="{{ route('admin.trending.edit', $trending->id) }}"
                                                   class="btn btn-sm btn-light border"
                                                   title="Edit">

                                                    <i class="bi bi-pencil"></i>

                                                </a>


                                                {{-- Delete --}}
                                                <form action="{{ route('admin.trending.destroy', $trending->id) }}"
                                                      method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this trending topic?');">

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

                                    <tr>

                                        <td colspan="3"
                                            class="text-center text-secondary py-5">

                                            <i class="bi bi-fire fs-3 d-block mb-2"></i>

                                            No trending topics found.

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