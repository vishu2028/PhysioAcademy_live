<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Forgot Password — Physio Academy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}" />
</head>
<body>
<main class="pauth-page pauth-compact" data-auth-page="forgot">
  <div class="pauth-particles"></div>
  <section class="pauth-shell">
    <div class="pauth-showcase">
      <a href="{{ url('/') }}" class="nav-logo">
        <div class="logo-icon">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none"><path d="M12 2L2 7l10 5 10-5-10-5z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 17l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="M2 12l10 5 10-5" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/></svg>
        </div>
        <span class="logo-text">Physio<span class="logo-accent">Academy</span></span>
      </a>
      <div class="pauth-floating-stack" aria-hidden="true">
        <div class="pauth-float-card"><strong>Secure Reset</strong><span>Protected account recovery</span></div>
        <div class="pauth-float-card"><strong>Email Link</strong><span>Fast simulated delivery</span></div>
        <div class="pauth-float-card"><strong>Student Safe</strong><span>Modern recovery UX</span></div>
        <div class="pauth-float-card"><strong>24/7 Access</strong><span>Return to study quickly</span></div>
      </div>
      <div class="pauth-showcase-copy">
        <span class="pauth-eyebrow">Account Recovery</span>
        <h1>Reset access without losing your study momentum.</h1>
        <p>We will verify your registered email and prepare a secure password reset path for your Physio Academy workspace.</p>
      </div>
    </div>

    <div class="pauth-panel-wrap">
      <div class="pauth-form-panel">
        <div class="pauth-form-head">
          <h2>Forgot Your Password?</h2>
          <p>Enter your registered email address and we’ll send you a password reset link.</p>
        </div>

        <!-- Session Status -->
        @if (session('status'))
            <div class="alert alert-success mb-4" style="color: #10b981; background: rgba(16,185,129,0.1); padding: 10px; border-radius: 8px; font-size: 0.9rem;">
                {{ session('status') }}
            </div>
        @endif

        <form class="pauth-form" id="forgotForm" method="POST" action="{{ route('password.email') }}">
          @csrf
          <div class="pauth-field">
            <input class="pauth-input @error('email') is-invalid @enderror" id="forgotEmail" name="email" type="email" placeholder=" " value="{{ old('email') }}" autocomplete="email" required autofocus>
            <label class="pauth-label" for="forgotEmail">Registered email</label>
            <span class="pauth-field-icon">@</span>
            @error('email')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
            @enderror
          </div>

          <button class="pauth-submit" type="submit">
            <span class="pauth-btn-text">Email Password Reset Link</span>
          </button>
        </form>

        <p class="pauth-switch">Remembered it? <a class="pauth-link" href="{{ route('login') }}">Back to login</a></p>
      </div>
    </div>
  </section>
</main>

<script src="{{ asset('ui-physio/footer.js') }}"></script>
<script src="{{ asset('ui-physio/auth.js') }}"></script>
</body>
</html>
