@extends('layouts.admin')

@section('title', 'Team Members')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Team Members</h2>
        <p class="text-secondary mb-0">
            Shown in the "Meet Our Team" section of the About page. The section stays hidden until at least one member is visible.
        </p>
    </div>

    <x-admin.data-table
        title="Team"
        :headers="['Member', 'Designation', 'Order', 'Status', 'Actions']"
        :createRoute="route('admin.team-members.create')"
        createText="Add Member"
    >
        @foreach($members as $member)
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        @if($member->photo_url)
                            <img src="{{ $member->photo_url }}" alt="" class="rounded-circle object-fit-cover" width="40" height="40">
                        @else
                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                {{ $member->initials }}
                            </div>
                        @endif
                        <div>
                            <div class="fw-bold">{{ $member->name }}</div>
                            <div class="small text-muted">{{ \Illuminate\Support\Str::limit($member->bio, 60) }}</div>
                        </div>
                    </div>
                </td>
                <td>{{ $member->designation }}</td>
                <td>{{ $member->sort_order }}</td>
                <td>
                    @if($member->is_active)
                        <span class="badge bg-success-subtle text-success px-3">Visible</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3">Hidden</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.team-members.edit', $member) }}" class="btn btn-primary btn-sm rounded-3">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.team-members.destroy', $member) }}" method="POST"
                              onsubmit="return confirm('Delete this team member?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm rounded-3"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
@endsection
