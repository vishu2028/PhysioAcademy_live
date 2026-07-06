@extends('layouts.admin')

@section('title', 'Topics')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark">Topic Management</h2>
            <p class="text-secondary">Create and manage topics under subjects and units.</p>
        </div>

        <a href="{{ route('admin.unit-topics.create') }}" class="btn btn-primary rounded-3">
            <i class="bi bi-plus-lg"></i> Add Topic
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white p-4 border-0">
            <h5 class="fw-bold mb-0">Topic List</h5>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th class="ps-4">#</th>
                        <th>Topic</th>
                        <th>Subject</th>
                        <th>Unit</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($unitTopics as $unitTopic)
                        <tr>
                            <td class="ps-4">
                                {{ $unitTopics->firstItem() + $loop->index }}
                            </td>

                            <td>
                                <div class="fw-bold">{{ $unitTopic->title }}</div>
                                <div class="small text-muted">{{ $unitTopic->slug }}</div>
                            </td>

                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $unitTopic->subject->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ $unitTopic->unit->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                {{ $unitTopic->sort_order }}
                            </td>

                            <td>
                                @if($unitTopic->status)
                                    <span class="badge bg-success-subtle text-success px-3">
                                        Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-3">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="text-end pe-4">
                                <div class="d-inline-flex gap-2">
                                    <a href="{{ route('admin.unit-topics.edit', $unitTopic) }}" class="btn btn-primary btn-sm rounded-3">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    <form action="{{ route('admin.unit-topics.destroy', $unitTopic) }}" method="POST" onsubmit="return confirm('Delete this topic?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger btn-sm rounded-3">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                No topics found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($unitTopics->hasPages())
                <div class="p-4">
                    {{ $unitTopics->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
