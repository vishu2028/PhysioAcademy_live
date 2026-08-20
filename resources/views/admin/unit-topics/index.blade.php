@extends('layouts.admin')

@section('title', 'Topics')

@section('content')

    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark">Topic Management</h2>
            <p class="text-secondary">
                Create and manage topics under subjects and units.
            </p>
        </div>
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

    <x-admin.data-table
        title="Topic List"
        :headers="[
            '#',
            'Topic',
            'Subject',
            'Unit',
            'Sort Order',
            'Status',
            'Actions'
        ]"
        :createRoute="route('admin.unit-topics.create')"
    >

        @foreach($unitTopics as $unitTopic)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    <div class="fw-bold">
                        {{ $unitTopic->title }}
                    </div>

                    <div class="small text-muted">
                        {{ $unitTopic->slug }}
                    </div>
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

                <td>
                    <div class="d-inline-flex gap-2">

                        <a href="{{ route('admin.unit-topics.edit', $unitTopic) }}"
                           class="btn btn-primary btn-sm rounded-3">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <form action="{{ route('admin.unit-topics.destroy', $unitTopic) }}"
                              method="POST"
                              onsubmit="return confirm('Delete this topic?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-danger btn-sm rounded-3">
                                <i class="bi bi-trash"></i>
                            </button>

                        </form>

                    </div>
                </td>

            </tr>

        @endforeach

    </x-admin.data-table>

@endsection