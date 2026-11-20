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

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">All Users</h5>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary rounded-3 px-4">
            <i class="bi bi-plus-lg me-2"></i> Add New
        </a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 mb-3">
            <div class="col-sm-8 col-md-6">
                <label for="user-search" class="visually-hidden">Search users</label>
                <input
                    type="search"
                    id="user-search"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Search users..."
                >
            </div>
            <div class="col-sm-4 col-md-3">
                <label for="joined-direction" class="visually-hidden">Joined date order</label>
                <select id="joined-direction" name="direction" class="form-select">
                    <option value="desc" @selected($direction === 'desc')>Newest first</option>
                    <option value="asc" @selected($direction === 'asc')>Oldest first</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-primary">Apply</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle w-100">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">User</th>
                        <th>Actions</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th aria-sort="{{ $direction === 'asc' ? 'ascending' : 'descending' }}">
                            <a
                                href="{{ $joinedDateSortUrl }}"
                                class="text-decoration-none text-reset"
                                aria-label="Sort Joined Date {{ $direction === 'asc' ? 'descending' : 'ascending' }}"
                            >
                                Joined Date
                                <i class="bi bi-arrow-{{ $direction === 'asc' ? 'up' : 'down' }} ms-1" aria-hidden="true"></i>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}" class="rounded-2" width="40" alt="">
                                    <span class="fw-semibold">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn btn-light btn-sm rounded-circle p-2" data-bs-toggle="dropdown" aria-label="Actions for {{ $user->name }}">
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
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ ucfirst($role->name) }}</span>
                                @endforeach
                            </td>
                            <td class="small text-muted">{{ $user->created_at->format('M d, Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
            <small class="text-muted">
                Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users
            </small>
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
