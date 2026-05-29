@extends('layouts.admin')

@section('title', 'Edit FAQ')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-light border btn-sm mb-3">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <h2 class="fw-bold text-dark">Edit FAQ</h2>
    <p class="text-secondary">Modify existing question and answer.</p>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="card-body p-4">
        <form action="{{ route('admin.faqs.update', $faq) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Question</label>
                        <input type="text" name="question" class="form-control rounded-3 py-2 @error('question') is-invalid @enderror" value="{{ old('question', $faq->question) }}" placeholder="What is Physio Academy?" required>
                        @error('question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-uppercase text-secondary">Answer</label>
                        <textarea name="answer" class="form-control rounded-3 @error('answer') is-invalid @enderror" rows="8" placeholder="Detailed answer here..." required>{{ old('answer', $faq->answer) }}</textarea>
                        @error('answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card bg-light border-0 rounded-4 p-3 h-100">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Display Order</label>
                            <input type="number" name="order" class="form-control rounded-3" value="{{ old('order', $faq->order) }}" required>
                            <div class="form-text">Lower numbers show first.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-uppercase text-secondary">Category</label>
                            <select name="category" class="form-select rounded-3">
                                <option value="General" {{ $faq->category == 'General' ? 'selected' : '' }}>General</option>
                                <option value="Account" {{ $faq->category == 'Account' ? 'selected' : '' }}>Account</option>
                                <option value="Curriculum" {{ $faq->category == 'Curriculum' ? 'selected' : '' }}>Curriculum</option>
                                <option value="Technical" {{ $faq->category == 'Technical' ? 'selected' : '' }}>Technical</option>
                            </select>
                        </div>

                        <hr class="my-4">

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" {{ $faq->status ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold ms-2" for="statusSwitch">Visible on Website</label>
                        </div>

                        <div class="mt-auto">
                            <button type="submit" class="btn btn-warning w-100 py-3 rounded-3 fw-bold">
                                <i class="bi bi-check-circle d-block fs-4 mb-1"></i> Update FAQ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
