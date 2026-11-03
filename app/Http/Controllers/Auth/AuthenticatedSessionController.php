<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(Request $request): View
    {
        $redirect = $request->query('redirect');

        if (
            is_string($redirect)
            && $redirect !== ''
            && parse_url($redirect, PHP_URL_HOST) === $request->getHost()
            && parse_url($redirect, PHP_URL_scheme) === $request->getScheme()
        ) {
            $request->session()->put('url.intended', $redirect);
        }

        return view('auth.login');
    }

    public function adminCreate(): View|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('auth.admin-login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        if ($request->user()->hasRole('admin') || $request->user()->hasRole('super_admin')) {
            return redirect()->intended(route('admin.dashboard', absolute: false));
        }

        return redirect()->intended(route('home', absolute: false));
    }

    public function adminStore(LoginRequest $request): RedirectResponse
    {
        $request->authenticate('admin');

        if (! Auth::guard('admin')->user()->hasAnyRole(['admin', 'super_admin'])) {
            Auth::guard('admin')->logout();

            throw ValidationException::withMessages([
                'email' => 'These credentials are not authorized for administrator access.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard', absolute: false));
    }

    public function adminDestroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->regenerate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
