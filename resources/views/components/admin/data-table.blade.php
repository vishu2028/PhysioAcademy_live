@props([
    'headers' => [],
    'title' => null,
    'createRoute' => null,
    'createText' => 'Add New',
])

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">{{ $title }}</h5>
        @if($createRoute)
            <a href="{{ $createRoute }}" class="btn btn-primary rounded-3 px-4">
                <i class="bi bi-plus-lg me-2"></i> {{ $createText }}
            </a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle datatable w-100">
                <thead class="bg-light">
                    <tr>
                        @foreach($headers as $header)
                            <th class="{{ $loop->first ? 'ps-4' : '' }}">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    {{ $slot }}
                </tbody>
            </table>
        </div>
    </div>
</div>
