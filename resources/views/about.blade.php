@extends('layouts.frontend')

@section('title', 'About Us')

@section('content')

{{-- =========================================================
     HERO SECTION
========================================================= --}}

<section class="bg-light py-5">
    <div class="container py-lg-5">

        <div class="row align-items-center g-5">

            {{-- Hero Content --}}
            <div class="col-lg-7">

                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-2 mb-3">
                    <i class="bi bi-stars me-1"></i>
                    Empowering Better Learning
                </span>

                <h1 class="display-3 fw-bold text-dark mb-4">
                    Learn Smarter.
                    <br>
                    Grow <span class="text-primary">Further.</span>
                </h1>

                <p class="lead text-secondary mb-4 lh-lg">
                    We help physiotherapy students spend less time searching
                    for resources and figuring out what to study—and more time
                    learning and revising. With structured study resources,
                    personalized doubt-solving, and focused webinars,
                    PhysioSource helps you make better use of your study time.
                </p>

                <div class="d-flex flex-wrap gap-2">

                    <a href="#our-story"
                       class="btn btn-primary btn-lg px-4 rounded-3">
                        Discover Our Story
                        <i class="bi bi-arrow-down ms-2"></i>
                    </a>

                    <a href="#why-us"
                       class="btn btn-outline-secondary btn-lg px-4 rounded-3">
                        Why Choose Us
                    </a>

                </div>

            </div>

            {{-- Hero Card --}}
            <div class="col-lg-5">

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                    <div class="card-body p-4 p-lg-5">

                        <div class="bg-primary text-white rounded-4 d-flex align-items-center justify-content-center mb-4"
                             style="width:75px;height:75px;">

                            <i class="bi bi-mortarboard-fill fs-1"></i>

                        </div>

                        <h3 class="fw-bold mb-3">
                            Your Learning Journey Starts Here
                        </h3>

                        <p class="text-secondary lh-lg mb-0">
                            A clear plan makes studying more effective.
                            PhysioSource helps you organize your preparation,
                            find relevant resources, and decide what to focus
                            on next—so you have more time for understanding
                            concepts and revising what you've learned.
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>


{{-- =========================================================
     OUR STORY
========================================================= --}}

<section id="our-story" class="py-5 bg-light">

    <div class="container py-lg-5">

        <div class="row align-items-center g-5">

            {{-- Visual --}}
            <div class="col-lg-6">

                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">

                    <div class="card-body bg-primary text-white p-5 text-center">

                        <div class="py-5">

                            <div class="bg-white bg-opacity-10 rounded-4 d-inline-flex align-items-center justify-content-center mb-4"
                                 style="width:120px;height:120px;">

                                <i class="bi bi-book-half display-4"></i>

                            </div>

                            <h2 class="fw-bold">
                                Knowledge Without Limits
                            </h2>

                            <p class="mb-0 opacity-75">
                                Learn. Practice. Improve. Succeed.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

            {{-- Content --}}
            <div class="col-lg-6">

                <div class="text-primary text-uppercase fw-bold small mb-2">
                    Our Story
                </div>

                <h2 class="display-5 fw-bold text-dark mb-4">
                    The Challenge Isn't Always Finding More to Study.
                    <span class="text-primary">It's Knowing How to Study.</span>
                </h2>

                <p class="text-secondary lead lh-lg">
                    Physiotherapy students often spend valuable hours searching
                    for resources, organizing material, and figuring out how to
                    prepare for exams. That leaves less time for what matters
                    most: understanding concepts, practising, and revising.
                </p>

                <p class="text-secondary lh-lg">
                    PhysioSource was created to bring structure to this
                    process. We help students navigate their learning with
                    relevant resources and practical study guidance, so they
                    can spend less time figuring things out and more time
                    making progress.
                </p>

                <p class="text-primary fst-italic fw-semibold lh-lg">
                    Find Direction. Learn with Purpose. Revise with Confidence.
                </p>

                <div class="d-flex align-items-center mt-4">

                    <div class="bg-primary text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:55px;height:55px;">

                        <i class="bi bi-lightbulb fs-4"></i>

                    </div>

                    <div class="ms-3">

                        <div class="fw-bold">
                            Our Mission
                        </div>

                        <div class="text-secondary">
                            To help physiotherapy students use their study time
                            effectively through structured learning, focused
                            academic support, and better revision.
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


{{-- =========================================================
     WHY CHOOSE US
========================================================= --}}

<section id="why-us" class="py-5">

    <div class="container py-lg-5">

        <div class="text-center mx-auto mb-5" style="max-width:700px;">

            <div class="text-primary text-uppercase fw-bold small mb-2">
                Why Choose Us
            </div>

            <h2 class="display-5 fw-bold text-dark mb-3">
                Less Time Searching.
                <span class="text-primary">More Time Studying.</span>
            </h2>

            <p class="text-secondary lead">
                You don't always need more study material. You need a clearer
                way to use your time, find what matters, and make consistent
                progress.
            </p>

        </div>

        <div class="row g-4">

            @php
                $features = [
                    ['icon' => 'bi-collection', 'title' => 'Structured Study & Exam Preparation', 'text' => 'Organized resources and practical study guidance to help you prepare systematically and make time for revision.'],
                    ['icon' => 'bi-chat-dots', 'title' => 'One-to-One Doubt Classes', 'text' => 'Get individual academic support focused on your specific questions and difficult concepts.'],
                    ['icon' => 'bi-camera-video', 'title' => 'Webinars & Group Doubt Sessions', 'text' => 'Explore relevant topics, resolve common doubts, and learn through focused group discussions.'],
                    ['icon' => 'bi-clock-history', 'title' => 'Save Valuable Study Time', 'text' => 'Spend less time searching for resources and organizing your preparation from scratch.'],
                    ['icon' => 'bi-bullseye', 'title' => 'Focus on What Matters', 'text' => 'Bring greater direction to your syllabus, study priorities, and exam preparation.'],
                    ['icon' => 'bi-arrow-repeat', 'title' => 'More Time for Revision', 'text' => 'Use the time you save to revisit concepts, strengthen understanding, and prepare for exams.'],
                ];
            @endphp

            @foreach($features as $feature)
                <div class="col-md-6 col-lg-4">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body p-4">

                            <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                                 style="width:58px;height:58px;">

                                <i class="bi {{ $feature['icon'] }} fs-4"></i>

                            </div>

                            <h4 class="fw-bold">
                                {{ $feature['title'] }}
                            </h4>

                            <p class="text-secondary lh-lg mb-0">
                                {{ $feature['text'] }}
                            </p>

                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

</section>


{{-- =========================================================
     HOW IT WORKS
========================================================= --}}

<section class="py-5 bg-light">

    <div class="container py-lg-5">

        <div class="text-center mx-auto mb-5" style="max-width:700px;">

            <div class="text-primary text-uppercase fw-bold small mb-2">
                How It Works
            </div>

            <h2 class="display-5 fw-bold text-dark mb-3">
                A Clearer Path from
                <span class="text-primary">Studying to Revision</span>
            </h2>

            <p class="text-secondary lead">
                Make your learning time count with a more structured approach
                to your academic goals.
            </p>

        </div>

        <div class="row g-4">

            @php
                $steps = [
                    ['number' => '01', 'title' => 'Find Your Direction', 'text' => 'Explore relevant resources and identify what you need to focus on next.'],
                    ['number' => '02', 'title' => 'Study Systematically', 'text' => 'Use structured resources and practical guidance to make your study sessions more focused.'],
                    ['number' => '03', 'title' => 'Clear Your Doubts', 'text' => 'Get individual academic help or join group sessions when you need support.'],
                    ['number' => '04', 'title' => 'Practise and Revise', 'text' => 'Use your study time to strengthen understanding, practise, and prepare for exams.'],
                ];
            @endphp

            @foreach($steps as $step)
                <div class="col-md-6 col-lg-3">

                    <div class="card border-0 shadow-sm rounded-4 h-100">

                        <div class="card-body p-4">

                            <span class="badge bg-primary rounded-3 fs-6 px-3 py-2 mb-4">
                                {{ $step['number'] }}
                            </span>

                            <h5 class="fw-bold">
                                {{ $step['title'] }}
                            </h5>

                            <p class="text-secondary lh-lg mb-0">
                                {{ $step['text'] }}
                            </p>

                        </div>

                    </div>

                </div>
            @endforeach

        </div>

    </div>

</section>


{{-- =========================================================
     CTA
========================================================= --}}

<section class="py-5">

    <div class="container py-lg-4">

        <div class="card border-0 bg-primary text-white rounded-4 shadow-lg overflow-hidden">

            <div class="card-body p-4 p-lg-5">

                <div class="row align-items-center">

                    <div class="col-lg-8">

                        <div class="small text-uppercase fw-bold opacity-75 mb-2">
                            Start Your Journey
                        </div>

                        <h2 class="display-6 fw-bold mb-3">
                            Make More Time for What Matters.
                        </h2>

                        <p class="lead opacity-75 mb-lg-0">
                            Stop spending so much time figuring out where to
                            start. Find the structure and academic support you
                            need to study with direction and make more time
                            for revision.
                        </p>

                    </div>

                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

                        <a href="{{ route('exam-aid') }}"
                           class="btn btn-light btn-lg px-4 rounded-3 fw-bold">

                            Explore Exam Aid
                            <i class="bi bi-arrow-right ms-2"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>


@if(($teamMembers ?? collect())->isNotEmpty())
    <section id="team" class="py-5 bg-light">

        <div class="container py-lg-5">

            <div class="text-center mx-auto mb-5" style="max-width:700px;">

                <div class="text-primary text-uppercase fw-bold small mb-2">
                    Meet Our Team
                </div>

                <h2 class="display-5 fw-bold text-dark mb-3">
                    The People Behind
                    <span class="text-primary">PhysioSource</span>
                </h2>

                <p class="text-secondary lead">
                    PhysioSource brings together people committed to helping
                    physiotherapy students learn with greater clarity,
                    confidence, and direction.
                </p>

            </div>

            <div class="row g-4 justify-content-center">

                @foreach($teamMembers as $member)
                    <div class="col-sm-6 col-lg-4">

                        <div class="card border-0 shadow-sm rounded-4 h-100 text-center">

                            <div class="card-body p-4">

                                @if($member->photo_url)
                                    <img src="{{ $member->photo_url }}"
                                         alt="{{ $member->name }}"
                                         class="rounded-circle object-fit-cover mb-3"
                                         width="110" height="110">
                                @else
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-inline-flex align-items-center justify-content-center mb-3"
                                         style="width:110px;height:110px;font-size:2rem;">
                                        {{ $member->initials }}
                                    </div>
                                @endif

                                <h5 class="fw-bold mb-1">
                                    {{ $member->name }}
                                </h5>

                                @if($member->designation)
                                    <div class="text-primary small fw-semibold mb-3">
                                        {{ $member->designation }}
                                    </div>
                                @endif

                                @if($member->bio)
                                    <p class="text-secondary lh-lg mb-0">
                                        {{ $member->bio }}
                                    </p>
                                @endif

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

        </div>

    </section>
@endif

@endsection
