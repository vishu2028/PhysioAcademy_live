@extends('layouts.admin')

@section('content')
<div class="mb-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active">User Management</li>
        </ol>
    </nav>
    <h2 class="fw-bold">Users</h2>
</div>

<x-admin.data-table 
    title="All Users" 
    :headers="['User', 'Email', 'Role', 'Joined At', 'Actions']" 
    :createRoute="route('admin.users.create')"
>
    @foreach($users as $user)
    <tr>
        <td class="ps-4">
            <div class="d-flex align-items-center gap-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="rounded-2" width="40">
                <span class="fw-semibold">{{ $user->name }}</span>
            </div>
        </td>
        <td>{{ $user->email }}</td>
        <td>
            @foreach($user->roles as $role)
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ ucfirst($role->name) }}</span>
            @endforeach
        </td>
        <td class="small text-muted">{{ $user->created_at->format('M d, Y') }}</td>
        <td>
            <div class="dropdown">
                <button class="btn btn-light btn-sm rounded-circle p-2" data-bs-toggle="dropdown">
                    <i class="bi bi-three-dots-vertical"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 p-2">
                    <li><a class="dropdown-item py-2 rounded-2" href="{{ route('admin.users.edit', $user) }}"><i class="bi bi-pencil me-2 text-primary"></i> Edit</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="dropdown-item py-2 rounded-2 text-danger"><i class="bi bi-trash me-2"></i> Delete</button>
                        </form>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>
@endsection
