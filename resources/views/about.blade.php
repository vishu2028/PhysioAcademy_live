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
                    We are building a smarter learning experience that brings
                    quality educational resources, expert guidance, and
                    practical knowledge together in one place.
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

                        <p class="text-secondary lh-lg">
                            Access organized resources, useful study materials,
                            exam preparation tools, and learning support
                            designed to help you move forward with confidence.
                        </p>

                        <hr class="my-4">

                        <div class="d-flex align-items-center">

                            <div class="d-flex">

                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center border border-3 border-white"
                                     style="width:42px;height:42px;">
                                    <i class="bi bi-person"></i>
                                </div>

                                <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center border border-3 border-white"
                                     style="width:42px;height:42px;margin-left:-12px;">
                                    <i class="bi bi-person"></i>
                                </div>

                                <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center border border-3 border-white"
                                     style="width:42px;height:42px;margin-left:-12px;">
                                    <i class="bi bi-person"></i>
                                </div>

                            </div>

                            <div class="ms-3">
                                <div class="fw-bold">
                                    10,000+ Learners
                                </div>

                                <small class="text-secondary">
                                    Growing every day
                                </small>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>
</section>


{{-- =========================================================
     STATISTICS
========================================================= --}}

<section class="py-5">

    <div class="container">

        <div class="row g-4">

            <div class="col-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100 text-center">
                    <div class="card-body py-4">

                        <div class="display-6 fw-bold text-primary">
                            10K+
                        </div>

                        <div class="text-secondary">
                            Active Learners
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100 text-center">
                    <div class="card-body py-4">

                        <div class="display-6 fw-bold text-primary">
                            500+
                        </div>

                        <div class="text-secondary">
                            Learning Resources
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100 text-center">
                    <div class="card-body py-4">

                        <div class="display-6 fw-bold text-primary">
                            50+
                        </div>

                        <div class="text-secondary">
                            Subjects Covered
                        </div>

                    </div>
                </div>

            </div>

            <div class="col-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100 text-center">
                    <div class="card-body py-4">

                        <div class="display-6 fw-bold text-primary">
                            95%
                        </div>

                        <div class="text-secondary">
                            Learner Satisfaction
                        </div>

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
                    Education should be
                    <span class="text-primary">accessible</span>,
                    organized, and inspiring.
                </h2>

                <p class="text-secondary lead lh-lg">
                    We started with a simple idea: students should not have
                    to spend hours searching for the right learning material.
                    Everything they need should be organized, easy to discover,
                    and available when they need it.
                </p>

                <p class="text-secondary lh-lg">
                    Our platform brings together study resources, exam
                    preparation materials, practical guides, and useful
                    academic content to create a learning environment that
                    helps students focus on what matters most.
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
                            Make learning simpler and more effective.
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
                Everything you need to
                <span class="text-primary">learn better</span>
            </h2>

            <p class="text-secondary lead">
                We focus on creating a simple, useful, and reliable learning
                experience for students at every stage of their journey.
            </p>

        </div>

        <div class="row g-4">

            {{-- Feature 1 --}}
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                             style="width:58px;height:58px;">

                            <i class="bi bi-collection fs-4"></i>

                        </div>

                        <h4 class="fw-bold">
                            Organized Resources
                        </h4>

                        <p class="text-secondary lh-lg mb-0">
                            Find study materials, guides, questions, and
                            learning resources organized in one convenient
                            place.
                        </p>

                    </div>

                </div>

            </div>

            {{-- Feature 2 --}}
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                             style="width:58px;height:58px;">

                            <i class="bi bi-lightning-charge fs-4"></i>

                        </div>

                        <h4 class="fw-bold">
                            Learn Efficiently
                        </h4>

                        <p class="text-secondary lh-lg mb-0">
                            Spend less time searching and more time learning
                            with resources designed around your academic
                            needs.
                        </p>

                    </div>

                </div>

            </div>

            {{-- Feature 3 --}}
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                             style="width:58px;height:58px;">

                            <i class="bi bi-shield-check fs-4"></i>

                        </div>

                        <h4 class="fw-bold">
                            Reliable Content
                        </h4>

                        <p class="text-secondary lh-lg mb-0">
                            We aim to provide useful, relevant, and dependable
                            learning resources that students can confidently
                            use.
                        </p>

                    </div>

                </div>

            </div>

            {{-- Feature 4 --}}
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                             style="width:58px;height:58px;">

                            <i class="bi bi-phone fs-4"></i>

                        </div>

                        <h4 class="fw-bold">
                            Learn Anywhere
                        </h4>

                        <p class="text-secondary lh-lg mb-0">
                            Access your learning resources whenever you need
                            them, whether you're studying at home or on the
                            move.
                        </p>

                    </div>

                </div>

            </div>

            {{-- Feature 5 --}}
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                             style="width:58px;height:58px;">

                            <i class="bi bi-bar-chart fs-4"></i>

                        </div>

                        <h4 class="fw-bold">
                            Track Your Progress
                        </h4>

                        <p class="text-secondary lh-lg mb-0">
                            Keep improving with tools and resources that help
                            you prepare, practice, and build confidence.
                        </p>

                    </div>

                </div>

            </div>

            {{-- Feature 6 --}}
            <div class="col-md-6 col-lg-4">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <div class="bg-primary-subtle text-primary rounded-3 d-inline-flex align-items-center justify-content-center mb-4"
                             style="width:58px;height:58px;">

                            <i class="bi bi-people fs-4"></i>

                        </div>

                        <h4 class="fw-bold">
                            Built for Students
                        </h4>

                        <p class="text-secondary lh-lg mb-0">
                            Every feature is designed with the real challenges
                            students face during their learning journey in
                            mind.
                        </p>

                    </div>

                </div>

            </div>

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
                A simpler way to
                <span class="text-primary">keep learning</span>
            </h2>

            <p class="text-secondary lead">
                From discovering resources to preparing for your next exam,
                your learning journey can be simple and focused.
            </p>

        </div>

        <div class="row g-4">

            <div class="col-md-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <span class="badge bg-primary rounded-3 fs-6 px-3 py-2 mb-4">
                            01
                        </span>

                        <h5 class="fw-bold">
                            Discover
                        </h5>

                        <p class="text-secondary lh-lg mb-0">
                            Explore subjects, topics, and resources relevant
                            to what you're currently learning.
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-md-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <span class="badge bg-primary rounded-3 fs-6 px-3 py-2 mb-4">
                            02
                        </span>

                        <h5 class="fw-bold">
                            Learn
                        </h5>

                        <p class="text-secondary lh-lg mb-0">
                            Study through carefully organized notes, guides,
                            videos, and educational materials.
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-md-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <span class="badge bg-primary rounded-3 fs-6 px-3 py-2 mb-4">
                            03
                        </span>

                        <h5 class="fw-bold">
                            Practice
                        </h5>

                        <p class="text-secondary lh-lg mb-0">
                            Test your knowledge with questions, revision
                            material, and practical learning resources.
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-md-6 col-lg-3">

                <div class="card border-0 shadow-sm rounded-4 h-100">

                    <div class="card-body p-4">

                        <span class="badge bg-primary rounded-3 fs-6 px-3 py-2 mb-4">
                            04
                        </span>

                        <h5 class="fw-bold">
                            Succeed
                        </h5>

                        <p class="text-secondary lh-lg mb-0">
                            Build confidence, strengthen your knowledge, and
                            approach your exams with better preparation.
                        </p>

                    </div>

                </div>

            </div>

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
                            Ready to make your learning journey better?
                        </h2>

                        <p class="lead opacity-75 mb-lg-0">
                            Discover resources, prepare smarter, and take
                            your next step with confidence.
                        </p>

                    </div>

                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

                        <a href="#"
                           class="btn btn-light btn-lg px-4 rounded-3 fw-bold">

                            Explore Resources
                            <i class="bi bi-arrow-right ms-2"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection