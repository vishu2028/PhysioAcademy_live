<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reset Password — Physio Academy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}" />
</head>
<body>
<main class="pauth-page pauth-compact" data-auth-page="reset">
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
        <div class="pauth-float-card"><strong>Strong Password</strong><span>Live security scoring</span></div>
        <div class="pauth-float-card"><strong>Protected Login</strong><span>Modern reset workflow</span></div>
        <div class="pauth-float-card"><strong>Clinical Notes</strong><span>Your saved study flow</span></div>
        <div class="pauth-float-card"><strong>Exam Aid</strong><span>Return securely</span></div>
      </div>
      <div class="pauth-showcase-copy">
        <span class="pauth-eyebrow">Secure Password Reset</span>
        <h1>Create a stronger key for your academic workspace.</h1>
        <p>Choose a secure password that protects bookmarks, topic history, and your learning progress across Physio Academy.</p>
      </div>
    </div>

    <div class="pauth-panel-wrap">
      <div class="pauth-form-panel">
        <div class="pauth-form-head">
          <h2>Reset Password</h2>
          <p>Enter a new secure password for your Physio Academy account.</p>
        </div>

        <form class="pauth-form" id="resetForm" method="POST" action="{{ route('password.store') }}">
          @csrf
          
          <!-- Password Reset Token -->
          <input type="hidden" name="token" value="{{ $request->route('token') }}">

          <div class="pauth-field">
            <input class="pauth-input @error('email') is-invalid @enderror" id="resetEmail" name="email" type="email" placeholder=" " value="{{ old('email', $request->email) }}" required autocomplete="email">
            <label class="pauth-label" for="resetEmail">Email</label>
            @error('email')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
            @enderror
          </div>

          <div class="pauth-field">
            <input class="pauth-input @error('password') is-invalid @enderror" id="resetPassword" name="password" type="password" placeholder=" " autocomplete="new-password" required>
            <label class="pauth-label" for="resetPassword">New password</label>
            <button class="pauth-pass-toggle" type="button" data-toggle-password="resetPassword" aria-label="Show password">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            @error('password')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
            @enderror
          </div>

          <div class="pauth-strength" data-strength-for="resetPassword">
            <div class="pauth-strength-track"><div class="pauth-strength-bar"></div></div>
            <span class="pauth-strength-label">Password strength: Waiting for input</span>
          </div>

          <div class="pauth-rules" data-rules-for="resetPassword">
            <span class="pauth-rule" data-rule="length">8 characters</span>
            <span class="pauth-rule" data-rule="upper">Uppercase</span>
            <span class="pauth-rule" data-rule="lower">Lowercase</span>
            <span class="pauth-rule" data-rule="number">Number</span>
            <span class="pauth-rule" data-rule="special">Special character</span>
          </div>

          <div class="pauth-field">
            <input class="pauth-input" id="resetConfirm" name="password_confirmation" type="password" placeholder=" " autocomplete="new-password" required>
            <label class="pauth-label" for="resetConfirm">Confirm password</label>
            <button class="pauth-pass-toggle" type="button" data-toggle-password="resetConfirm" aria-label="Show password">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z"/><circle cx="12" cy="12" r="3"/></svg>
            </button>
            @error('password_confirmation')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
            @enderror
          </div>

          <button class="pauth-submit" type="submit">
            <span class="pauth-btn-text">Reset Password</span>
          </button>
        </form>

        <p class="pauth-switch"><a class="pauth-link" href="{{ route('login') }}">Return to login</a></p>
      </div>
    </div>
  </section>
</main>

<script src="{{ asset('ui-physio/footer.js') }}"></script>
<script src="{{ asset('ui-physio/auth.js') }}"></script>
</body>
</html>
