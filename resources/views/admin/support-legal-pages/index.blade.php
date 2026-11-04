@extends('layouts.admin')

@section('title', 'Support & Legal Pages')

@section('content')
<div class="mb-4">
    <h2 class="fw-bold text-dark">Support &amp; Legal Pages</h2>
    <p class="text-secondary mb-0">Manage the published support and legal content shown in the website footer.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Page</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th class="text-end pe-4">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pages as $page)
                    <tr>
                        <td class="ps-4 fw-semibold">{{ $page->title }}</td>
                        <td><code>/{{ $page->slug }}</code></td>
                        <td>
                            <span class="badge rounded-pill {{ $page->status ? 'text-bg-success' : 'text-bg-secondary' }}">
                                {{ $page->status ? 'Published' : 'Unpublished' }}
                            </span>
                        </td>
                        <td>{{ $page->updated_at?->diffForHumans() }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('admin.support-legal-pages.edit', $page->slug) }}" class="btn btn-primary btn-sm rounded-3">
                                <i class="bi bi-pencil me-1"></i> Edit
                            </a>
                            @if($page->status)
                                <a href="{{ route('support.page', $page->slug) }}" target="_blank" rel="noopener" class="btn btn-light btn-sm rounded-3">
                                    <i class="bi bi-eye"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
