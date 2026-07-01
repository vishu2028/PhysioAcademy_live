@extends('layouts.admin')

@section('title', 'Student Testimonials')

@section('content')
    <div class="mb-4 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark">Student Testimonials</h2>
            <p class="text-secondary mb-0">Feedback from physiotherapy students using the platform.</p>
        </div>

        {{-- Section Visibility Toggle --}}
        <form action="{{ route('admin.testimonials.section-toggle') }}" method="POST">
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

    <x-admin.data-table
        title="Testimonial List"
        :headers="['Student', 'Review', 'Rating', 'Status', 'Actions']"
        :createRoute="route('admin.testimonials.create')"
    >
        @foreach($testimonials as $testimonial)
            <tr>
                <td class="ps-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($testimonial->name) }}&background=4f46e5&color=fff" class="rounded-circle" width="40">
                        <div>
                            <div class="fw-bold">{{ $testimonial->name }}</div>
                            <div class="small text-muted">{{ $testimonial->designation ?? 'Student' }}</div>
                        </div>
                    </div>
                </td>

                <td class="small">{{ Str::limit($testimonial->content, 80) }}</td>

                <td>
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= $testimonial->rating ? '-fill text-warning' : ' text-light' }}"></i>
                    @endfor
                </td>

                <td>
                    @if($testimonial->status)
                        <span class="badge bg-success-subtle text-success px-3">Featured</span>
                    @else
                        <span class="badge bg-light text-secondary px-3">Hidden</span>
                    @endif
                </td>

                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="btn btn-primary btn-sm rounded-3">
                            <i class="bi bi-pencil"></i>
                        </a>

                        <form action="{{ route('admin.testimonials.destroy', $testimonial) }}" method="POST" onsubmit="return confirm('Delete this testimonial?')">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-danger btn-sm rounded-3">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @endforeach
    </x-admin.data-table>
@endsection
