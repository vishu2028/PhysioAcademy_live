<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login — PhysioSource</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}">
    <style>
    </style>
</head>
<body class="pauth-body">
    <main class="pauth-page" data-auth-page="login">
        <div class="pauth-particles"></div>
        <section class="pauth-shell pauth-single" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
            <div class="pauth-panel-wrap">
                <div class="pauth-form-panel">
                    <a href="{{ url('/') }}" class="auth-close" aria-label="Close login">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </a>
                    <div class="pauth-form-head">
                        <h1 id="loginTitle">Welcome Back</h1>
                        <p>Continue your academic journey with PhysioSource.</p>
                    </div>
                    @if (session('status'))
                        <div class="alert alert-success mb-4" role="status">{{ session('status') }}</div>
                    @endif
                    <p class="social-auth-intro">Sign in or create your account securely with a provider.</p>
                    @include('auth.partials.provider-buttons')
                </div>
            </div>
        </section>
    </main>
    <script src="{{ asset('ui-physio/footer.js') }}?v={{ filemtime(public_path('ui-physio/footer.js')) }}"></script>
</body>
</html>
