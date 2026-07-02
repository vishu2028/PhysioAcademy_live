@extends('layouts.admin')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold">Student Doubts</h2>
        <p class="text-secondary">Review student doubts and send academic responses.</p>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">



            @if($errors->any())
                <div class="alert alert-danger rounded-3">
                    Please fix the errors and try again.
                </div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                    <tr>
                        <th>Student</th>
                        <th>Year</th>
                        <th>Subject</th>
                        <th>Topic</th>
                        <th>Doubt</th>
                        <th>Status</th>
                        <th style="width: 320px;">Response</th>
                        <th>Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($doubts as $doubt)
                        <tr>
                            <td>
                                <strong>{{ $doubt->user?->name ?? 'N/A' }}</strong><br>
                                <small class="text-muted">{{ $doubt->user?->email ?? '-' }}</small>
                            </td>

                            <td>{{ $doubt->academicYear?->name ?? '-' }}</td>

                            <td>{{ $doubt->subject?->name ?? '-' }}</td>

                            <td>{{ $doubt->topic ?: '-' }}</td>

                            <td style="max-width: 260px;">
                                <div class="small text-muted">
                                    {{ $doubt->message }}
                                </div>
                            </td>

                            <td>
                                @php
                                    $badgeClass = match($doubt->status) {
                                        'answered' => 'success',
                                        'rejected' => 'danger',
                                        'in_progress' => 'info',
                                        default => 'warning',
                                    };
                                @endphp

                                <span class="badge bg-{{ $badgeClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $doubt->status)) }}
                                </span>
                            </td>



                            <td>
                                <form action="{{ route('admin.doubts.update', $doubt) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <select name="status" class="form-select form-select-sm mb-2">
                                        <option value="pending" @selected($doubt->status === 'pending')>Pending</option>
                                        <option value="in_progress" @selected($doubt->status === 'in_progress')>In Progress</option>
                                        <option value="answered" @selected($doubt->status === 'answered')>Answered</option>
                                        <option value="rejected" @selected($doubt->status === 'rejected')>Rejected</option>
                                    </select>

                                    <textarea
                                        name="answer"
                                        class="form-control form-control-sm mb-2"
                                        rows="4"
                                        placeholder="Write answer here..."
                                    >{{ old('answer', $doubt->answer) }}</textarea>

                                    @if($doubt->answeredBy)
                                        <small class="text-muted d-block mb-2">
                                            Answered by {{ $doubt->answeredBy?->name }}
                                            @if($doubt->answered_at)
                                                on {{ $doubt->answered_at->format('d M Y h:i A') }}
                                            @endif
                                        </small>
                                    @endif

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        Save Response
                                    </button>
                                </form>
                            </td>

                            <td>
                                <form action="{{ route('admin.doubts.destroy', $doubt) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this doubt?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                No doubts found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $doubts->links() }}
            </div>
        </div>
    </div>
@endsection
