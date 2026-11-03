<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Administrator Login — PhysioSource</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700&family=Outfit:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('ui-physio/style.css') }}">
    <style>
        .admin-auth-card { width: min(100% - 32px, 440px); margin: 10vh auto; padding: 36px; border: 1px solid #e2e8f0; border-radius: 22px; background: #fff; box-shadow: 0 24px 70px rgba(15, 23, 42, .12); color: #172033; }
        .admin-auth-card h1 { margin: 0 0 8px; font: 700 1.6rem Sora, sans-serif; }
        .admin-auth-card p { color: #64748b; }
        .admin-auth-field { display: grid; gap: 7px; margin: 18px 0; }
        .admin-auth-field input { width: 100%; padding: 12px 14px; border: 1px solid #cbd5e1; border-radius: 10px; }
        .admin-auth-submit { width: 100%; border: 0; border-radius: 10px; padding: 13px; background: #004aad; color: white; font-weight: 700; }
        .admin-auth-error { color: #b42318; font-size: .9rem; }
        .admin-auth-back { display: inline-block; margin-bottom: 22px; color: #004aad; font-weight: 600; text-decoration: none; }
        .admin-auth-back:hover { text-decoration: underline; }
    </style>
</head>
<body class="pauth-body">
    <main class="admin-auth-card">
        <a class="admin-auth-back" href="{{ route('login') }}">← Back to User Login</a>
        <h1>Administrator Login</h1>
        <p>Sign in with your administrator account.</p>
        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <label class="admin-auth-field">
                <span>Registered Admin Email</span>
                <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                @error('email')<span class="admin-auth-error">{{ $message }}</span>@enderror
            </label>
            <label class="admin-auth-field">
                <span>Password</span>
                <input type="password" name="password" autocomplete="current-password" required>
                @error('password')<span class="admin-auth-error">{{ $message }}</span>@enderror
            </label>
            <label><input type="checkbox" name="remember"> Remember me</label>
            <button class="admin-auth-submit" type="submit">Sign in to Admin</button>
        </form>
        <p><a href="{{ route('password.request') }}">Forgot your password?</a></p>
    </main>
</body>
</html>
