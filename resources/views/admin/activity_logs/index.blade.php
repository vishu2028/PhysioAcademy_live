@extends('layouts.admin')

@section('title', 'Admin Activity Logs')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold text-dark">Audit & Activity Logs</h2>
    <p class="text-secondary">Track every administrative action performed across the system.</p>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.activity-logs.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <label class="form-label small fw-bold">Admin User</label>
                <select name="user_id" class="form-select rounded-3 small">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Action Type</label>
                <select name="action" class="form-select rounded-3 small">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                        <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Unit</label>
                <select name="module" class="form-select rounded-3 small">
                    <option value="">All Units</option>
                    @foreach($modules as $mod)
                        <option value="{{ $mod }}" {{ request('module') == $mod ? 'selected' : '' }}>{{ $mod }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">From Date</label>
                <input type="date" name="date_from" class="form-control rounded-3 small" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">To Date</label>
                <input type="date" name="date_to" class="form-control rounded-3 small" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary rounded-3 w-100 fw-bold py-2">
                    <i class="bi bi-filter me-1"></i> Filter
                </button>
                <a href="{{ route('admin.activity-logs.index') }}" class="btn btn-light border rounded-3 w-100 fw-bold py-2">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<x-admin.data-table
    title="System Activity Stream"
    :headers="['Timestamp', 'Admin', 'Action', 'Module', 'Description', 'IP Address']"
>
    @foreach($logs as $log)
    <tr class="align-middle">
        <td class="ps-4 small text-muted">
            {{ $log->created_at->format('M d, H:i:s') }} <br/>
            <span class="x-small text-secondary">{{ $log->created_at->diffForHumans() }}</span>
        </td>
        <td>
            <div class="d-flex align-items-center gap-2">
                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.7rem;">
                    {{ substr($log->user?->name ?? '?', 0, 1) }}
                </div>
                <div>
                    <p class="mb-0 fw-bold small">{{ $log->user?->name ?? 'System/Deleted' }}</p>
                    @if($log->user)
                        <span class="x-small text-muted">{{ $log->user->roles->first()?->name }}</span>
                    @endif
                </div>
            </div>
        </td>
        <td>
            @php
                $color = match($log->action) {
                    'CREATE' => 'success',
                    'DELETE' => 'danger',
                    'UPDATE' => 'warning',
                    'LOGIN' => 'info',
                    'LOGOUT' => 'secondary',
                    default => 'primary'
                };
            @endphp
            <span class="badge bg-{{ $color }}-subtle text-{{ $color }} px-3 py-2 rounded-pill x-small fw-bold">
                {{ $log->action }}
            </span>
        </td>
        <td><span class="text-secondary small fw-medium">{{ $log->module }}</span></td>
        <td>
            <p class="mb-0 small text-dark fw-medium">{{ $log->description }}</p>
            @if($log->properties && isset($log->properties['original']) && count($log->properties['original']) > 0)
                <button class="btn btn-link p-0 x-small text-decoration-none text-primary mt-1" type="button" data-bs-toggle="collapse" data-bs-target="#log-{{ $log->id }}">
                    View Raw Meta <i class="bi bi-chevron-down"></i>
                </button>
                <div class="collapse mt-2" id="log-{{ $log->id }}">
                    <pre class="bg-light p-2 rounded-3 x-small mb-0" style="max-height: 200px; overflow-y: auto;">{{ json_encode($log->properties, JSON_PRETTY_PRINT) }}</pre>
                </div>
            @endif
        </td>
        <td>
            <code class="x-small text-secondary fw-bold">{{ $log->ip_address }}</code>
            <div class="x-small text-muted text-truncate" style="max-width: 150px;" title="{{ $log->user_agent }}">
                {{ $log->user_agent }}
            </div>
        </td>
    </tr>
    @endforeach
</x-admin.data-table>

<div class="mt-4">
    {{ $logs->links() }}
</div>

<style>
    .x-small { font-size: 0.7rem; }
</style>
@endsection
