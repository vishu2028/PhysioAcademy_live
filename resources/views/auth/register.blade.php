<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register — Physio Academy</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=Outfit:wght@300;400;500;600;700&display=swap"
    rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}" />
</head>

<body class="pauth-body">
  <main class="pauth-page" data-auth-page="signup">
    <div class="pauth-particles"></div>
    <section class="pauth-shell pauth-single" role="dialog" aria-modal="true" aria-labelledby="signupTitle">
      <div class="pauth-panel-wrap">
        <div class="pauth-form-panel">
          <a href="{{ url('/') }}" class="auth-close" aria-label="Close register">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </a>

          <div class="pauth-form-head">
            <h2 id="signupTitle">Create Account</h2>
            <p>Start your structured learning journey with Physio Academy.</p>
          </div>

          <form class="pauth-form" id="signupForm" method="POST" action="{{ route('register') }}">
            @csrf
            <div class="pauth-field">
              <input class="pauth-input @error('name') is-invalid @enderror" id="signupName" name="name" type="text"
                placeholder=" " value="{{ old('name') }}" autocomplete="name" required>
              <label class="pauth-label" for="signupName">Full name</label>
              @error('name')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
              @enderror
            </div>

            <div class="pauth-field">
              <input class="pauth-input @error('email') is-invalid @enderror" id="signupEmail" name="email" type="email"
                placeholder=" " value="{{ old('email') }}" autocomplete="email" required>
              <label class="pauth-label" for="signupEmail">Email address</label>
              <span class="pauth-field-icon">@</span>
              @error('email')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
              @enderror
            </div>

            <div class="pauth-field">
              <input class="pauth-input @error('password') is-invalid @enderror" id="signupPassword" name="password"
                type="password" placeholder=" " autocomplete="new-password" required>
              <label class="pauth-label" for="signupPassword">Password</label>
              <button class="pauth-pass-toggle" type="button" data-toggle-password="signupPassword"
                aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
              @error('password')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
              @enderror
            </div>

            <div class="pauth-strength" data-strength-for="signupPassword">
              <div class="pauth-strength-track">
                <div class="pauth-strength-bar"></div>
              </div>
              <span class="pauth-strength-label">Password strength: Waiting for input</span>
            </div>

            <div class="pauth-rules" data-rules-for="signupPassword">
              <span class="pauth-rule" data-rule="length">8 characters</span>
              <span class="pauth-rule" data-rule="upper">Uppercase</span>
              <span class="pauth-rule" data-rule="lower">Lowercase</span>
              <span class="pauth-rule" data-rule="number">Number</span>
              <span class="pauth-rule" data-rule="special">Special character</span>
            </div>

            <div class="pauth-field">
              <input class="pauth-input" id="signupConfirm" name="password_confirmation" type="password" placeholder=" "
                autocomplete="new-password" required>
              <label class="pauth-label" for="signupConfirm">Confirm password</label>
              <button class="pauth-pass-toggle" type="button" data-toggle-password="signupConfirm"
                aria-label="Show password">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12Z" />
                  <circle cx="12" cy="12" r="3" />
                </svg>
              </button>
              @error('password_confirmation')
                <p class="pauth-error" style="display: block;">{{ $message }}</p>
              @enderror
            </div>

            <button class="pauth-submit" type="submit">
              <span class="pauth-btn-text">Create Account</span>
            </button>

            <div class="pauth-divider"><span>or</span></div>

            <!-- <button class="pauth-google" type="button" data-google-action>
              <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M21.6 12.23c0-.78-.07-1.53-.2-2.23H12v4.22h5.38a4.6 4.6 0 0 1-2 3.02v2.51h3.24c1.9-1.75 2.98-4.33 2.98-7.52Z"/><path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.43l-3.24-2.51c-.9.6-2.04.95-3.38.95-2.6 0-4.8-1.76-5.59-4.12H3.06v2.59A10 10 0 0 0 12 22Z"/><path fill="#FBBC05" d="M6.41 13.89A6 6 0 0 1 6.1 12c0-.65.11-1.29.31-1.89V7.52H3.06A10 10 0 0 0 2 12c0 1.61.39 3.14 1.06 4.48l3.35-2.59Z"/><path fill="#EA4335" d="M12 5.99c1.47 0 2.78.5 3.82 1.5l2.87-2.87C16.95 3 14.69 2 12 2a10 10 0 0 0-8.94 5.52l3.35 2.59C7.2 7.75 9.4 5.99 12 5.99Z"/></svg>
              Sign up with Google
            </button> -->
          </form>

          <p class="pauth-switch">Already have an account? <a class="pauth-link" href="{{ route('login') }}">Login</a>
          </p>
        </div>
      </div>
    </section>
  </main>

  <script src="{{ asset('ui-physio/footer.js') }}"></script>
  <script src="{{ asset('ui-physio/auth.js') }}"></script>
</body>

</html>