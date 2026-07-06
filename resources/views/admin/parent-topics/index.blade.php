@extends('layouts.admin')

@section('title', 'Parent Topics')

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1">Parent Topic Management</h1>
                <p class="text-muted mb-0">Create and manage parent topics under topics.</p>
            </div>

            <a href="{{ route('admin.parent-topics.create') }}" class="btn btn-primary">
                + Add Parent Topic
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Parent Topic List</h5>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Parent Topic</th>
                        <th>Subject</th>
                        <th>Unit</th>
                        <th>Topic</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($parentTopics as $parentTopic)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>
                                <strong>{{ $parentTopic->title }}</strong>
                                <div class="text-muted small">{{ $parentTopic->slug }}</div>
                            </td>

                            <td>
                                <span class="badge bg-primary-subtle text-primary">
                                    {{ $parentTopic->subject?->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-secondary-subtle text-secondary">
                                    {{ $parentTopic->unit?->name ?? 'N/A' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-info-subtle text-info">
                                    {{ $parentTopic->unitTopic?->title ?? 'N/A' }}
                                </span>
                            </td>

                            <td>{{ $parentTopic->sort_order }}</td>

                            <td>
                                @if($parentTopic->status)
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <a href="{{ route('admin.parent-topics.edit', $parentTopic) }}" class="btn btn-sm btn-primary">
                                    Edit
                                </a>

                                <form action="{{ route('admin.parent-topics.destroy', $parentTopic) }}"
                                      method="POST"
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure you want to delete this parent topic?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                No parent topics found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
