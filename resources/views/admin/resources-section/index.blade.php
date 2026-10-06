@extends('layouts.admin')

@section('title', 'Resources Section')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark">Resources Section</h2>
            <p class="text-secondary mb-0">Show or hide the "Learning Materials" section on the homepage.</p>
        </div>

        {{-- Section Visibility Toggle --}}
        <form action="{{ route('admin.resources-section.section-toggle') }}" method="POST">
            @csrf
            @method('PATCH')

            <input type="hidden" name="section_enabled" value="0">

            <div class="d-flex align-items-center gap-3 bg-white px-4 py-3 rounded-4 shadow-sm">
                <span class="fw-bold {{ $sectionEnabled ? 'text-success' : 'text-danger' }}">
                    Section: {{ $sectionEnabled ? 'Visible' : 'Hidden' }}
                </span>

                <div class="form-check form-switch m-0">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="section_enabled"
                        value="1"
                        onchange="this.form.submit()"
                        @checked($sectionEnabled)
                    >
                </div>
            </div>
        </form>
    </div>
@endsection
