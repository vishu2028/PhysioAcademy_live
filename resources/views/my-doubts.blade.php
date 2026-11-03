@extends('layouts.frontend')

@section('title', 'My Doubts')

@section('content')
    <style>
        .my-doubts-page { padding: 112px 0 72px; }
        .doubt-library-shell { max-width: 1040px; margin: 0 auto; }
        .doubt-library-heading { margin-bottom: 22px; color: #172033; font-size: clamp(1.45rem, 3vw, 1.9rem); font-weight: 750; }
        .doubt-library-banner { padding: clamp(22px, 4vw, 34px); border-radius: 18px; background: linear-gradient(120deg, #0752b2, #0b63c7); color: #fff; box-shadow: 0 12px 30px rgba(7, 82, 178, .18); }
        .doubt-library-banner h2 { margin: 0 0 6px; font-size: clamp(1.2rem, 2.5vw, 1.55rem); font-weight: 750; }
        .doubt-library-banner p { margin: 0; color: rgba(255,255,255,.9); line-height: 1.6; }
        .doubt-library-tools { display: grid; gap: 20px; margin: 18px 0 30px; }
        .doubt-library-search { display: flex; align-items: center; gap: 12px; min-height: 58px; padding: 0 16px; border: 1px solid #dfe5ec; border-radius: 14px; background: #fff; }
        .doubt-library-search i { color: #1262c4; font-size: 1.1rem; }
        .doubt-library-search input { width: 100%; min-width: 0; border: 0; outline: 0; background: transparent; color: #172033; }
        .doubt-library-search button { flex: 0 0 auto; }
        .doubt-library-filter-group { display: grid; gap: 10px; }
        .doubt-library-filter-label { margin: 0; color: #273449; font-size: .92rem; font-weight: 700; }
        .doubt-library-chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .doubt-library-chip { display: inline-flex; align-items: center; min-height: 34px; padding: 6px 13px; border: 1px solid #e0e6ee; border-radius: 999px; background: #f7f9fc; color: #475569; font-size: .83rem; font-weight: 650; text-decoration: none; transition: .15s ease; }
        .doubt-library-chip:hover { border-color: #9dc2f2; color: #0752b2; }
        .doubt-library-chip.active { border-color: #0b63c7; background: #eaf3ff; color: #0752b2; }
        .doubt-library-divider { height: 1px; margin: 26px 0; border: 0; background: #e4e9f0; }
        .doubt-library-section { margin-top: 30px; }
        .doubt-library-section-heading { margin: 0 0 16px; color: #1d2939; font-size: 1.16rem; font-weight: 750; }
        .doubt-library-cards { display: grid; gap: 14px; }
        .doubt-library-card { min-width: 0; padding: clamp(18px, 3vw, 24px); border: 1px solid #e0e6ee; border-radius: 16px; background: #fff; box-shadow: 0 3px 12px rgba(20, 35, 55, .035); }
        .doubt-library-card-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
        .doubt-library-card-tags { display: flex; flex-wrap: wrap; gap: 7px; }
        .doubt-subject-tag, .doubt-status-tag { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 999px; background: #eef4fb; color: #42536a; font-size: .75rem; font-weight: 700; }
        .doubt-subject-tag { background: #e8f2ff; color: #1762b9; }
        .doubt-status-tag.answered { background: #e9f8ef; color: #247342; }
        .doubt-status-tag.pending { background: #fff5df; color: #946200; }
        .doubt-status-tag.closed { background: #f0f1f3; color: #5d6570; }
        .doubt-library-date { color: #737f8f; font-size: .8rem; white-space: nowrap; }
        .doubt-library-question-title { margin: 0 0 7px; color: #182230; font-size: 1.03rem; font-weight: 750; line-height: 1.45; overflow-wrap: anywhere; }
        .doubt-library-question { margin: 0; color: #3d4b5c; line-height: 1.65; white-space: pre-line; overflow-wrap: anywhere; }
        .doubt-library-privacy { margin: 8px 0 0; color: #7a8594; font-size: .8rem; }
        .doubt-library-answer { margin-top: 16px; padding-top: 15px; border-top: 1px solid #e9edf2; }
        .doubt-library-answer h4 { display: flex; flex-wrap: wrap; align-items: baseline; gap: 8px; margin: 0 0 8px; color: #253247; font-size: .94rem; font-weight: 750; }
        .doubt-library-answer h4 span { color: #788596; font-size: .78rem; font-weight: 500; }
        .doubt-library-answer-preview, .doubt-library-answer-full { margin: 0; color: #39485a; line-height: 1.7; overflow-wrap: anywhere; }
        .doubt-library-answer-details { margin-top: 7px; }
        .doubt-library-answer-details summary { width: fit-content; color: #075cbd; cursor: pointer; font-size: .87rem; font-weight: 700; }
        .doubt-library-answer-full { padding-top: 10px; }
        .doubt-library-answer-full img { max-width: 100%; height: auto; }
        .doubt-library-answer-full table { width: 100%; border-collapse: collapse; }
        .doubt-library-answer-full th, .doubt-library-answer-full td { padding: 7px; border: 1px solid #d7dee8; }
        .doubt-library-answer-full iframe { display: block; width: min(100%, 640px); aspect-ratio: 16 / 9; height: auto; border: 0; margin: 12px auto; }
        .doubt-library-waiting { display: flex; align-items: center; gap: 8px; margin: 16px 0 0; color: #936200; font-size: .9rem; }
        .doubt-library-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 18px; margin-top: 16px; }
        .doubt-library-actions form { margin: 0; }
        .doubt-library-action, .doubt-library-history-link { display: inline-flex; align-items: center; gap: 6px; padding: 0; border: 0; background: transparent; color: #4a596d; font-size: .85rem; font-weight: 650; text-decoration: none; }
        button.doubt-library-action { cursor: pointer; }
        .doubt-library-action:hover, .doubt-library-history-link:hover, .doubt-library-action.liked { color: #075cbd; }
        .doubt-like-count { color: inherit; }
        .doubt-library-history-link { margin-left: auto; color: #075cbd; }
        .doubt-library-empty { padding: 40px 22px; border: 1px dashed #cfd8e3; border-radius: 16px; background: #fafbfd; text-align: center; }
        .doubt-library-empty h3 { margin: 0 0 8px; color: #263449; font-size: 1.1rem; font-weight: 750; }
        .doubt-library-empty p { margin: 0 0 18px; color: #687587; }
        @media (max-width: 640px) {
            .my-doubts-page { padding: 88px 0 48px; }
            .doubt-library-card-heading { flex-direction: column; }
            .doubt-library-search { flex-wrap: wrap; padding: 12px 14px; }
            .doubt-library-search button { width: 100%; }
            .doubt-library-history-link { margin-left: 0; }
            .doubt-library-actions { gap: 12px 16px; }
        }
    </style>

    <section class="section my-doubts-page">
        <div class="section-container doubt-library-shell">
            <h1 class="doubt-library-heading">My Doubts</h1>

            <header class="doubt-library-banner">
                <h2>Doubt &amp; Answer Library</h2>
                <p>Explore your submitted questions, follow the conversation, and learn from expert answers.</p>
            </header>

            <div class="doubt-library-tools">
                <form method="GET" action="{{ route('doubts.mine') }}" class="doubt-library-search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <label class="visually-hidden" for="doubt-library-search">Search doubts by topic, subject, or question</label>
                    <input
                        id="doubt-library-search"
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search by topic, subject, or question"
                    >
                    <input type="hidden" name="status" value="{{ $status }}">
                    @if($subjectId !== '')
                        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
                    @endif
                    <button class="btn btn-primary rounded-pill px-4" type="submit">Search</button>
                </form>

                <div class="doubt-library-filter-group">
                    <p class="doubt-library-filter-label">Browse by Subject</p>
                    <nav class="doubt-library-chips" aria-label="Filter My Doubts by subject">
                        <a
                            class="doubt-library-chip {{ $subjectId === '' ? 'active' : '' }}"
                            href="{{ route('doubts.mine', array_filter(['status' => $status, 'search' => $search])) }}"
                            @if($subjectId === '') aria-current="page" @endif
                        >All Subjects</a>
                        @foreach($subjects as $subject)
                            <a
                                class="doubt-library-chip {{ (string) $subjectId === (string) $subject->id ? 'active' : '' }}"
                                href="{{ route('doubts.mine', array_filter(['status' => $status, 'search' => $search, 'subject_id' => $subject->id])) }}"
                                @if((string) $subjectId === (string) $subject->id) aria-current="page" @endif
                            >{{ $subject->name }}</a>
                        @endforeach
                        @if($hasOtherSubjectDoubts)
                            <a
                                class="doubt-library-chip {{ $subjectId === 'other' ? 'active' : '' }}"
                                href="{{ route('doubts.mine', array_filter(['status' => $status, 'search' => $search, 'subject_id' => 'other'])) }}"
                                @if($subjectId === 'other') aria-current="page" @endif
                            >Other Subjects</a>
                        @endif
                    </nav>
                </div>

                <div class="doubt-library-filter-group">
                    <p class="doubt-library-filter-label">Filter by Status</p>
                    <nav class="doubt-library-chips" aria-label="Filter My Doubts by status">
                        @foreach(['all' => 'All', 'pending' => 'Pending', 'answered' => 'Answered', 'closed' => 'Closed'] as $filter => $label)
                            <a
                                class="doubt-library-chip {{ $status === $filter ? 'active' : '' }}"
                                href="{{ route('doubts.mine', array_filter(['status' => $filter, 'search' => $search, 'subject_id' => $subjectId])) }}"
                                @if($status === $filter) aria-current="page" @endif
                            >{{ $label }}</a>
                        @endforeach
                    </nav>
                </div>
            </div>

            @if(session('success'))
                <div class="alert alert-success rounded-4">{{ session('success') }}</div>
            @endif

            @if($recentlyAnsweredDoubts->isNotEmpty())
                <section class="doubt-library-section" aria-labelledby="recently-answered-heading">
                    <h2 class="doubt-library-section-heading" id="recently-answered-heading">Recently Answered Doubts</h2>
                    <div class="doubt-library-cards">
                        @foreach($recentlyAnsweredDoubts as $doubt)
                            @include('doubts._library-card', ['doubt' => $doubt])
                        @endforeach
                    </div>
                </section>
                <hr class="doubt-library-divider">
            @endif

            <section class="doubt-library-section" aria-labelledby="my-doubt-history-heading">
                <h2 class="doubt-library-section-heading" id="my-doubt-history-heading">
                    {{ $status === 'all' ? 'Your Doubts' : ($status === 'answered' ? 'Your Answered Doubts' : ($status === 'pending' ? 'Your Pending Doubts' : 'Your Closed Doubts')) }}
                </h2>
                @if($doubts->isNotEmpty())
                    <div class="doubt-library-cards">
                        @foreach($doubts as $doubt)
                            @include('doubts._library-card', ['doubt' => $doubt])
                        @endforeach
                    </div>
                    <div class="mt-4">{{ $doubts->links() }}</div>
                @else
                    <div class="doubt-library-empty">
                        <h3>No doubts found</h3>
                        <p>
                            @if($status !== 'all' || $search !== '' || $subjectId !== '')
                                No doubts match these filters. Try another subject, status, or search.
                            @else
                                You have not submitted any doubts yet.
                            @endif
                        </p>
                        @if($status !== 'all' || $search !== '' || $subjectId !== '')
                            <a href="{{ route('doubts.mine') }}" class="btn btn-outline-primary rounded-pill px-4">Clear filters</a>
                        @else
                            <a href="{{ route('home') }}#ask-doubt" class="btn btn-primary rounded-pill px-4">Submit your first doubt</a>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    </section>
@endsection
