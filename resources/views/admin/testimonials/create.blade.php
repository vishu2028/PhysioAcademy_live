@extends('layouts.admin')

@section('title', 'Create Testimonial')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.testimonials.index') }}" class="text-decoration-none small text-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
    <h2 class="fw-bold text-dark mt-2">Add New Testimonial</h2>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('admin.testimonials.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="row g-4">
                <div class="col-md-8">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Name</label>
                        <input type="text" name="client_name" class="form-control rounded-3" value="{{ old('client_name') }}" placeholder="e.g. John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Designation / Year</label>
                        <input type="text" name="client_designation" class="form-control rounded-3" value="{{ old('client_designation', 'Third Year BPT') }}" placeholder="e.g. Third Year Student">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Review / Feedback</label>
                        <textarea name="content" class="form-control rounded-3" rows="4" placeholder="What the student said..." required>{{ old('content') }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Rating (1-5)</label>
                            <select name="rating" class="form-select rounded-3">
                                @for($i=5; $i>=1; $i--)
                                <option value="{{ $i }}" {{ old('rating', 5) == $i ? 'selected' : '' }}>{{ $i }} Stars</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="form-check form-switch mt-4 pt-2">
                                <input class="form-check-input" type="checkbox" name="status" id="statusSwitch" value="1" {{ old('status', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="statusSwitch">Featured (Show on site)</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Student Photo (Optional)</label>
                        <input type="file" name="client_image" class="form-control rounded-3">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Display Order</label>
                        <input type="number" name="order" class="form-control rounded-3" value="{{ old('order', 0) }}">
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-top d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-3 px-5 py-2 fw-bold">Save Testimonial</button>
                <a href="{{ route('admin.testimonials.index') }}" class="btn btn-light rounded-3 px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
