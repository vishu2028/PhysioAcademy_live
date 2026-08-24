@extends('layouts.admin')

@section('title', 'Exam Aid Banner')

@section('content')

<div class="mb-4 d-flex justify-content-between align-items-center">
    <div>
        <h2 class="fw-bold text-dark mb-1">Exam Aid Banner</h2>
        <p class="text-secondary mb-0">
            Manage Exam Aid banner content.
        </p>
    </div>

    <a href="{{ route('admin.exam-aid-banner.create') }}"
       class="btn btn-primary rounded-3">
        <i class="bi bi-plus-lg me-1"></i>
        Add Banner
    </a>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">

    <div class="card-body p-0">

        @if($banners->count())

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="px-4">#</th>
                            <th>Main Title</th>
                            <th>Title 1</th>
                            <th>Title 2</th>
                            <th>Heading 1</th>
                            <th>Percentage</th>
                            <th>Created</th>
                            <th class="text-end px-4">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($banners as $banner)

                            <tr>

                                <td class="px-4">
                                    {{ $loop->iteration }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $banner->main_title }}
                                    </div>

                                    <small class="text-muted">
                                        {{ Str::limit($banner->description, 50) }}
                                    </small>
                                </td>

                                <td>
                                    {{ $banner->title_1 ?? '-' }}
                                </td>

                                <td>
                                    {{ $banner->title_2 ?? '-' }}
                                </td>

                                <td>
                                    {{ $banner->heading_1 ?? '-' }}
                                </td>

                                <td>
                                    <span class="badge bg-primary rounded-pill">
                                        {{ $banner->percentage_value }}%
                                    </span>
                                </td>

                                <td>
                                    {{ $banner->created_at->format('d M Y') }}
                                </td>

                                <td class="text-end px-4">

                                    <div class="d-inline-flex gap-1">
                                       
                                        {{-- Edit --}}
                                        <a href="{{ route('admin.exam-aid-banner.edit', $banner->id) }}"
                                           class="btn btn-sm btn-light border"
                                           title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        {{-- Delete --}}
                                        <form action="{{ route('admin.exam-aid-banner.destroy', $banner->id) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this banner?');">

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

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="text-center py-5">

                <i class="bi bi-megaphone fs-1 text-secondary"></i>

                <h5 class="mt-3 fw-bold">
                    No Exam Aid Banner Found
                </h5>

                <p class="text-secondary">
                    Create your first Exam Aid banner.
                </p>

                <a href="{{ route('admin.exam-aid-banner.create') }}"
                   class="btn btn-primary rounded-3">
                    <i class="bi bi-plus-lg me-1"></i>
                    Add Banner
                </a>

            </div>

        @endif

    </div>

</div>

@endsection