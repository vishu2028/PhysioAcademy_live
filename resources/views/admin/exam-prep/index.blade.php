@extends('layouts.admin')

@section('title', 'Exam Prep Section')

@section('content')
    <div class="mb-4">
        <h2 class="fw-bold text-dark">Exam Prep Section</h2>
        <p class="text-secondary mb-0">Control the "Exam Aid Center" section on the homepage: its heading and the cards shown in it.</p>
    </div>

    <form action="{{ route('admin.exam-prep.settings') }}" method="POST" class="card border-0 shadow-sm rounded-4 mb-4">
        @csrf
        @method('PUT')

        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Section Heading</h5>

            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                <input class="form-check-input" type="checkbox" role="switch" name="exam_prep_enabled" id="exam_prep_enabled"
                       value="1" @checked(old('exam_prep_enabled', $settings['exam_prep_enabled']))>
                <label class="form-check-label fw-bold" for="exam_prep_enabled">Show section on homepage</label>
            </div>
        </div>

        <div class="card-body p-4 pt-0">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Small Tag</label>
                    <input type="text" name="exam_prep_tag" class="form-control @error('exam_prep_tag') is-invalid @enderror"
                           value="{{ old('exam_prep_tag', $settings['exam_prep_tag']) }}">
                    @error('exam_prep_tag') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Title</label>
                    <input type="text" name="exam_prep_title" class="form-control @error('exam_prep_title') is-invalid @enderror"
                           value="{{ old('exam_prep_title', $settings['exam_prep_title']) }}">
                    @error('exam_prep_title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">Highlighted Title Part</label>
                    <input type="text" name="exam_prep_title_highlight" class="form-control @error('exam_prep_title_highlight') is-invalid @enderror"
                           value="{{ old('exam_prep_title_highlight', $settings['exam_prep_title_highlight']) }}">
                    @error('exam_prep_title_highlight') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label class="form-label fw-bold">Subtitle</label>
                    <input type="text" name="exam_prep_subtitle" class="form-control @error('exam_prep_subtitle') is-invalid @enderror"
                           value="{{ old('exam_prep_subtitle', $settings['exam_prep_subtitle']) }}">
                    @error('exam_prep_subtitle') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>

        <div class="card-footer bg-light p-3 text-end">
            <button class="btn btn-primary px-4">Save Heading</button>
        </div>
    </form>

    <x-admin.data-table
        title="Cards"
        :headers="['Card', 'Links To', 'Size', 'Order', 'Status', 'Actions']"
        :createRoute="route('admin.exam-prep-cards.create')"
        createText="Add Card"
    >
        @foreach($cards as $card)
            <tr>
                <td class="ps-4">
                    <div class="fw-bold">{{ $card->title }}</div>
                    <div class="small text-muted">{{ \Illuminate\Support\Str::limit($card->description, 70) }}</div>
                </td>
                <td class="small">
                    @if($card->custom_url)
                        <i class="bi bi-link-45deg"></i> {{ $card->custom_url }}
                    @elseif($card->category)
                        <i class="bi bi-tag"></i> {{ $card->category->name }}
                        @unless($card->category->is_active)
                            <span class="badge bg-warning-subtle text-warning">inactive</span>
                        @endunless
                    @else
                        <i class="bi bi-journal-text"></i> Exam Aid page
                    @endif
                </td>
                <td>{{ $card->is_large ? 'Large' : 'Normal' }}</td>
                <td>{{ $card->sort_order }}</td>
                <td>
                    @if($card->is_active)
                        <span class="badge bg-success-subtle text-success px-3">Visible</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3">Hidden</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.exam-prep-cards.edit', $card) }}" class="btn btn-primary btn-sm rounded-3">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('admin.exam-prep-cards.destroy', $card) }}" method="POST"
                              onsubmit="return confirm('Delete this card?')">
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
