@extends('layouts.admin')


@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Units</h4>

            <a href="{{ route('admin.units.create') }}" class="btn btn-primary">
                Add Unit
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

        <div class="card">
            <div class="card-body table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                    <tr>
                        <th width="60">#</th>
                        <th>Unit</th>
                        <th>Subject</th>
                        <th>Sort Order</th>
                        <th>Status</th>
                        <th width="180">Actions</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td>{{ $units->firstItem() + $loop->index }}</td>

                            <td>
                                <strong>{{ $unit->name }}</strong>

                                @if($unit->description)
                                    <div class="text-muted small">
                                        {{ Str::limit($unit->description, 80) }}
                                    </div>
                                @endif
                            </td>

                            <td>{{ $unit->subject?->name ?? '-' }}</td>

                            <td>{{ $unit->sort_order }}</td>

                            <td>
                                <form action="{{ route('admin.units.toggle-status', $unit) }}" method="POST">
                                    @csrf
                                    @method('PATCH')

                                    <button type="submit" class="btn btn-sm {{ $unit->is_active ? 'btn-success' : 'btn-secondary' }}">
                                        {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                    </button>
                                </form>
                            </td>

                            <td>
                                <a href="{{ route('admin.units.edit', $unit) }}" class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <form action="{{ route('admin.units.destroy', $unit) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this unit?')">
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
                            <td colspan="6" class="text-center">No units found.</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                {{ $units->links() }}
            </div>
        </div>
    </div>
@endsection
