<div class="social-auth-options">
    <button class="social-auth-button social-auth-google" type="button" data-google-action>
        <svg width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#4285F4" d="M21.6 12.23c0-.78-.07-1.53-.2-2.23H12v4.22h5.38a4.6 4.6 0 0 1-2 3.02v2.51h3.24c1.9-1.75 2.98-4.33 2.98-7.52Z"/>
            <path fill="#34A853" d="M12 22c2.7 0 4.96-.9 6.62-2.43l-3.24-2.51c-.9.6-2.04.95-3.38.95-2.6 0-4.8-1.76-5.59-4.12H3.06v2.59A10 10 0 0 0 12 22Z"/>
            <path fill="#FBBC05" d="M6.41 13.89A6 6 0 0 1 6.1 12c0-.65.11-1.29.31-1.89V7.52H3.06A10 10 0 0 0 2 12c0 1.61.39 3.14 1.06 4.48l3.35-2.59Z"/>
            <path fill="#EA4335" d="M12 5.99c1.47 0 2.78.5 3.82 1.5l2.87-2.87C16.95 3 14.69 2 12 2a10 10 0 0 0-8.94 5.52l3.35 2.59C7.2 7.75 9.4 5.99 12 5.99Z"/>
        </svg>
        <span>Continue with Google</span>
    </button>
    <button class="social-auth-button social-auth-apple" type="button" data-apple-action>
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
            <path d="M16.37 12.2c.02 2.19 1.92 2.92 1.94 2.93-.02.05-.3 1.04-.99 2.06-.6.89-1.23 1.78-2.22 1.8-.97.02-1.28-.58-2.39-.58-1.1 0-1.45.56-2.36.6-.95.03-1.67-.97-2.28-1.85-1.25-1.8-2.2-5.08-.92-7.3.63-1.1 1.76-1.79 2.99-1.81.93-.02 1.8.63 2.37.63.57 0 1.64-.78 2.76-.67.47.02 1.8.19 2.65 1.43-.07.05-1.58.92-1.55 2.76ZM14.56 5.9c.5-.6.83-1.43.74-2.26-.72.03-1.59.48-2.1 1.08-.46.53-.86 1.37-.75 2.18.8.06 1.62-.4 2.11-1Z"/>
        </svg>
        <span>Continue with Apple</span>
    </button>
    <p class="social-auth-error" data-provider-error role="alert" aria-live="polite"></p>
</div>

<style>
    .social-auth-options { display: grid; gap: 12px; width: 100%; }
    .social-auth-button {
        display: flex; align-items: center; justify-content: center; gap: 12px;
        width: 100%; min-height: 52px; padding: 12px 18px; border: 1px solid #dbe3ee;
        border-radius: 14px; background: #fff; color: #172033; font: 650 15px/1.2 Outfit, sans-serif;
        cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, opacity .18s ease;
    }
    .social-auth-button:hover:not(:disabled) { transform: translateY(-1px); box-shadow: 0 10px 24px rgba(15, 23, 42, .1); }
    .social-auth-button:disabled { cursor: wait; opacity: .65; }
    .social-auth-apple { background: #000; border-color: #000; color: #fff; }
    .social-auth-error { display: none; margin: 0; color: #b42318; font-size: .88rem; line-height: 1.5; }
    .social-auth-error.is-visible { display: block; }
    .auth-modal .social-auth-options { padding: 8px 0 20px; }
</style>

@php
    $firebaseWebConfig = config('services.firebase.web', []);
    $firebaseWebConfigured = collect(['apiKey', 'authDomain', 'projectId', 'appId'])
        ->every(fn ($key) => filled($firebaseWebConfig[$key] ?? null));
    $appleConfig = config('services.apple', []);
    $appleConfigured = filled($appleConfig['client_id'] ?? null)
        && filter_var($appleConfig['redirect_uri'] ?? null, FILTER_VALIDATE_URL);
@endphp
<script>
    window.physioFirebaseConfig = @json($firebaseWebConfig);
    window.physioFirebaseConfigured = @json($firebaseWebConfigured);
    window.physioFirebaseLoginUrl = @json(route('login.firebase'));
    window.physioFirebaseCsrf = @json(csrf_token());
    window.physioAppleConfig = {
        enabled: @json($appleConfigured),
        clientId: @json($appleConfig['client_id'] ?? ''),
        redirectUri: @json($appleConfig['redirect_uri'] ?? ''),
        challengeUrl: @json(route('login.apple.challenge')),
        loginUrl: @json(route('login.apple'))
    };
</script>
<script type="module" src="{{ asset('ui-physio/social-auth.js') }}?v={{ filemtime(public_path('ui-physio/social-auth.js')) }}"></script>
