@extends('layouts.frontend')

@section('title', 'Request a Group Webinar')

@section('content')
<style>
    .group-webinar-page { background: #f4f8ff; min-height: 75vh; padding: 48px 0; }
    .group-webinar-card { background: #fff; border: 1px solid #e4ecfa; border-radius: 22px; box-shadow: 0 16px 48px rgba(27, 70, 130, .08); }
    .group-webinar-accent { color: #2563eb; }
    .group-webinar-summary { background: linear-gradient(135deg, #eff6ff, #f8fbff); border: 1px solid #dbeafe; border-radius: 16px; }
    .group-webinar-page .form-control:focus { border-color: #60a5fa; box-shadow: 0 0 0 .2rem rgba(37, 99, 235, .12); }
</style>

<main class="group-webinar-page">
    <div class="container" style="max-width: 960px">
        <div class="mb-4">
            <a href="{{ route('webinars.index') }}" class="text-decoration-none small text-secondary">← Back to webinars</a>
            <div class="mt-3">
                <span class="badge rounded-pill bg-primary-subtle text-primary px-3 py-2">Group learning · individual payment</span>
                <h1 class="fw-bold mt-3 mb-2">Request a Group Webinar</h1>
                <p class="text-secondary mb-0">Bring a group together around a shared topic. Every participant, including you, receives an individual invitation and pays separately.</p>
            </div>
        </div>

        @if($errors->any())
            <div class="alert alert-danger rounded-4">
                <strong>Please check the form:</strong>
                <ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        @if(session('webinar_error'))<div class="alert alert-danger rounded-4">{{ session('webinar_error') }}</div>@endif

        <form method="POST" action="{{ route('group-webinars.store') }}" class="group-webinar-card p-3 p-md-5" id="groupWebinarForm">
            @csrf
            <div class="row g-4">
                <div class="col-12">
                    <h2 class="h5 fw-bold mb-3">Organizer</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="organizerName">Name</label>
                            <input class="form-control" id="organizerName" value="{{ auth()->user()->name }}" readonly>
                            <div class="form-text">Automatically taken from your account.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="organizerEmail">Email</label>
                            <input class="form-control" id="organizerEmail" type="email" value="{{ auth()->user()->email }}" readonly>
                            <div class="form-text">You are included as participant 1.</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <h2 class="h5 fw-bold mb-3">Shared topic</h2>
                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="topic">Topic or doubt <span class="text-danger">*</span></label>
                        <input class="form-control" id="topic" name="topic" value="{{ old('topic') }}" maxlength="255" required>
                    </div>
                    <div>
                        <label class="form-label fw-semibold" for="description">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" minlength="10" maxlength="5000" required>{{ old('description') }}</textarea>
                        <div class="form-text">Describe what the group wants the instructor to cover.</div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1">Additional participants</h2>
                            <p class="small text-secondary mb-0">Add each participant's email. The organizer is counted automatically.</p>
                        </div>
                        <button class="btn btn-outline-primary rounded-pill" type="button" id="addParticipant"><i class="bi bi-person-plus me-1"></i>Add participant</button>
                    </div>
                    <div id="participantsList" class="d-grid gap-3">
                        @foreach(old('participants', [['name' => '', 'email' => '']]) as $index => $entry)
                            <div class="participant-row row g-2 align-items-end rounded-4 border p-3 mx-0">
                                <div class="col-md-5">
                                    <label class="form-label small fw-semibold">Name (optional)</label>
                                    <input class="form-control participant-name" name="participants[{{ $index }}][name]" value="{{ $entry['name'] ?? '' }}" maxlength="255" autocomplete="name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email address <span class="text-danger">*</span></label>
                                    <input class="form-control participant-email" type="email" name="participants[{{ $index }}][email]" value="{{ $entry['email'] ?? '' }}" maxlength="255" required autocomplete="email">
                                </div>
                                <div class="col-md-1 text-end">
                                    <button type="button" class="btn btn-outline-danger remove-participant" aria-label="Remove participant"><i class="bi bi-trash"></i></button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12">
                    <div class="group-webinar-summary p-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-md-4">
                                <div class="small text-secondary">Price per participant</div>
                                <div class="h4 fw-bold mb-0 group-webinar-accent">{{ $isFree ? 'Free' : '₹'.number_format($price, 2) }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-secondary">Participants (organizer included)</div>
                                <div class="h4 fw-bold mb-0"><span id="participantCount">2</span> <span class="small fw-normal text-secondary">/ {{ $maximumParticipants }}</span></div>
                            </div>
                            <div class="col-md-4">
                                <div class="small text-secondary">Expected total collection</div>
                                <div class="h4 fw-bold mb-0 group-webinar-accent" id="expectedTotal">{{ $isFree ? 'Free' : '₹'.number_format($price * 2, 2) }}</div>
                            </div>
                        </div>
                        <hr>
                        <div class="fw-semibold mb-2">Participant review</div>
                        <ol id="participantReview" class="small text-secondary mb-2">
                            <li>{{ auth()->user()->name }} — {{ auth()->user()->email }} (Organizer)</li>
                        </ol>
                        <div class="small text-secondary">
                            Each person gets their own invitation and payment link. The organizer is not charged for other participants.
                            Minimum group size: {{ $minimumParticipants }}. Each individual invitation expires after the configured period.
                        </div>
                    </div>
                </div>

                <div class="col-12 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <a class="small text-decoration-none" href="{{ route('doubt-sessions.create') }}">Looking for a One-on-One Doubt Session?</a>
                    <button class="btn btn-primary btn-lg rounded-pill px-4" type="submit">Review &amp; submit request <i class="bi bi-arrow-right ms-1"></i></button>
                </div>
            </div>
        </form>
    </div>
</main>

<script>
(() => {
    const list = document.getElementById('participantsList');
    const count = document.getElementById('participantCount');
    const total = document.getElementById('expectedTotal');
    const review = document.getElementById('participantReview');
    const maxParticipants = {{ (int) $maximumParticipants }};
    const minParticipants = {{ (int) $minimumParticipants }};
    const price = {{ (float) $price }};
    const isFree = @json($isFree);
    const organizerName = @json(auth()->user()->name);
    const organizerEmail = @json(auth()->user()->email);
    const formatMoney = amount => '₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    function renumber() {
        [...list.querySelectorAll('.participant-row')].forEach((row, index) => {
            row.querySelector('.participant-name').name = `participants[${index}][name]`;
            row.querySelector('.participant-email').name = `participants[${index}][email]`;
        });
        updateReview();
    }

    function updateReview() {
        const rows = [...list.querySelectorAll('.participant-row')];
        const totalParticipants = rows.length + 1;
        count.textContent = totalParticipants;
        total.textContent = isFree ? 'Free' : formatMoney(price * totalParticipants);
        review.replaceChildren();
        const organizerItem = document.createElement('li');
        organizerItem.textContent = `${organizerName} — ${organizerEmail} (Organizer)`;
        review.append(organizerItem);
        rows.forEach(row => {
            const email = row.querySelector('.participant-email').value.trim();
            if (!email) return;
            const item = document.createElement('li');
            const name = row.querySelector('.participant-name').value.trim();
            item.textContent = name ? `${name} — ${email}` : email;
            review.append(item);
        });
        document.getElementById('addParticipant').disabled = totalParticipants >= maxParticipants;
    }

    document.getElementById('addParticipant').addEventListener('click', () => {
        if (list.querySelectorAll('.participant-row').length + 1 >= maxParticipants) return;
        const wrapper = document.createElement('div');
        wrapper.innerHTML = `<div class="participant-row row g-2 align-items-end rounded-4 border p-3 mx-0">
            <div class="col-md-5"><label class="form-label small fw-semibold">Name (optional)</label><input class="form-control participant-name" maxlength="255" autocomplete="name"></div>
            <div class="col-md-6"><label class="form-label small fw-semibold">Email address <span class="text-danger">*</span></label><input class="form-control participant-email" type="email" maxlength="255" required autocomplete="email"></div>
            <div class="col-md-1 text-end"><button type="button" class="btn btn-outline-danger remove-participant" aria-label="Remove participant"><i class="bi bi-trash"></i></button></div>
        </div>`;
        list.append(wrapper.firstElementChild);
        renumber();
    });

    list.addEventListener('click', event => {
        const button = event.target.closest('.remove-participant');
        if (!button) return;
        button.closest('.participant-row').remove();
        renumber();
    });
    list.addEventListener('input', updateReview);
    document.getElementById('groupWebinarForm').addEventListener('submit', event => {
        const totalParticipants = list.querySelectorAll('.participant-row').length + 1;
        if (totalParticipants < minParticipants || totalParticipants > maxParticipants) {
            event.preventDefault();
            window.alert(`Choose between ${minParticipants} and ${maxParticipants} participants, including the organizer.`);
        }
    });
    renumber();
})();
</script>
@endsection
