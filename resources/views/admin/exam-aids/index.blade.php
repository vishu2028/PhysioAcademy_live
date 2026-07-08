@extends('layouts.admin')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-0">Exam Aid</h2>
            <div class="text-muted small">Manage learning materials, viva questions and exam questions.</div>
        </div>

        <a href="{{ route('admin.exam-aids.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Create Exam Aid
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Title</th>
                    <th>Subject</th>
                    <th>Unit</th>
                    <th>Academic Year</th>
                    <th>Semester</th>
                    <th>Status</th>
                    <th width="160">Action</th>
                </tr>
                </thead>

                <tbody>
                @forelse($examAids as $examAid)
                    <tr>
                        <td>
                            <strong>{{ $examAid->title }}</strong>
                            <div class="small text-muted">
                                {{ \Illuminate\Support\Str::limit($examAid->description, 70) }}
                            </div>
                        </td>

                        <td>{{ $examAid->subject->name ?? '-' }}</td>
                        <td>{{ $examAid->unit->name ?? '-' }}</td>
                        <td>{{ $examAid->academicYear->name ?? '-' }}</td>
                        <td>{{ $examAid->semester->name ?? '-' }}</td>

                        <td>
                            @if($examAid->status)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <a href="{{ route('admin.exam-aids.edit', $examAid) }}" class="btn btn-sm btn-outline-primary">
                                Edit
                            </a>

                            <form action="{{ route('admin.exam-aids.destroy', $examAid) }}" method="POST" class="d-inline"
                                  onsubmit="return confirm('Are you sure you want to delete this Exam Aid?')">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            No Exam Aid records found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($examAids->hasPages())
            <div class="card-footer bg-white">
                {{ $examAids->links() }}
            </div>
        @endif
    </div>
@endsection
