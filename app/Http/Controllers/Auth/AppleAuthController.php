<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AppleIdentityTokenService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AppleAuthController extends Controller
{
    public function __construct(
        private readonly AppleIdentityTokenService $apple
    ) {
    }

    public function challenge(): JsonResponse
    {
        if (! $this->apple->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Apple sign-in is not configured for this website yet.',
            ], 503);
        }

        $state = Str::random(64);
        $nonce = Str::random(64);

        session([
            'apple_login_state' => $state,
            'apple_login_nonce' => $nonce,
            'apple_login_challenged_at' => now()->timestamp,
        ]);

        return response()->json([
            'success' => true,
            'state' => $state,
            'nonce' => $nonce,
        ])->header('Cache-Control', 'no-store');
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string', 'max:10000'],
            'state' => ['required', 'string', 'max:128'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $state = session()->pull('apple_login_state');
        $nonce = session()->pull('apple_login_nonce');
        $challengedAt = session()->pull('apple_login_challenged_at');

        if (
            ! is_string($state)
            || ! is_string($nonce)
            || ! is_numeric($challengedAt)
            || (int) $challengedAt < now()->subMinutes(10)->timestamp
            || ! hash_equals($state, $validated['state'])
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Apple sign-in expired. Please try again.',
            ], 419);
        }

        try {
            $identity = $this->apple->verify($validated['id_token'], $nonce);
        } catch (\RuntimeException) {
            return response()->json([
                'success' => false,
                'message' => $this->apple->isConfigured()
                    ? 'Apple sign-in could not verify your account. Please try again.'
                    : 'Apple sign-in is not configured for this website yet.',
            ], $this->apple->isConfigured() ? 401 : 503);
        }

        $appleSub = $identity->sub;
        $email = isset($identity->email)
            ? strtolower(trim((string) $identity->email))
            : null;

        if ($email !== null && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'success' => false,
                'message' => 'Apple returned an invalid email address.',
            ], 401);
        }

        $userByAppleSub = User::where('apple_sub', $appleSub)->first();
        $userByEmail = $email
            ? User::whereRaw('LOWER(email) = ?', [$email])->first()
            : null;

        if (
            ($userByAppleSub && $userByEmail && $userByAppleSub->isNot($userByEmail))
            || ($userByEmail && filled($userByEmail->apple_sub) && $userByEmail->apple_sub !== $appleSub)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This Apple account is linked to another user.',
            ], 409);
        }

        $user = $userByAppleSub ?? $userByEmail;

        if ($user?->hasAnyRole(['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator accounts must use the administrator login.',
            ], 403);
        }

        if (! $user && ! $email) {
            return response()->json([
                'success' => false,
                'message' => 'Apple did not provide an email for this account. Contact support to link your account.',
            ], 422);
        }

        try {
            if (! $user) {
                $user = DB::transaction(function () use ($appleSub, $email, $validated): User {
                    $user = User::create([
                        'name' => $this->displayName($validated['name'] ?? null),
                        'email' => $email,
                        'password' => Hash::make(Str::random(64)),
                        'apple_sub' => $appleSub,
                    ]);

                    $user->forceFill(['email_verified_at' => now()])->save();
                    Role::findOrCreate('user', 'web');
                    $user->assignRole('user');

                    return $user;
                });
            } else {
                $updates = [];

                if (! $user->apple_sub) {
                    $updates['apple_sub'] = $appleSub;
                }

                if (! $user->email_verified_at) {
                    $updates['email_verified_at'] = now();
                }

                if ($updates) {
                    $user->forceFill($updates)->save();
                }

                if ($user->getRoleNames()->isEmpty()) {
                    Role::findOrCreate('user', 'web');
                    $user->assignRole('user');
                }
            }
        } catch (QueryException $exception) {
            $error = strtolower($exception->getMessage());

            if (! str_contains($error, 'duplicate') && ! str_contains($error, 'unique constraint')) {
                throw $exception;
            }

            $user = User::where('apple_sub', $appleSub)->first()
                ?? ($email ? User::whereRaw('LOWER(email) = ?', [$email])->first() : null);

            if (! $user || $user->hasAnyRole(['admin', 'super_admin'])) {
                throw $exception;
            }

            if (filled($user->apple_sub) && $user->apple_sub !== $appleSub) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Apple account is linked to another user.',
                ], 409);
            }

            if (! $user->apple_sub) {
                $user->forceFill(['apple_sub' => $appleSub])->save();
            }
        }

        Auth::login($user);
        $request->session()->regenerate();

        $redirect = $request->session()->pull('url.intended', route('home', absolute: false));
        $redirectPath = is_string($redirect) ? parse_url($redirect, PHP_URL_PATH) : null;

        if (! is_string($redirect) || $redirectPath === null || str_starts_with($redirectPath, '/admin')) {
            $redirect = route('home', absolute: false);
        }

        return response()->json([
            'success' => true,
            'redirect' => $redirect,
        ]);
    }

    private function displayName(?string $name): string
    {
        $name = trim((string) $name);

        return $name !== '' ? $name : 'Apple User';
    }
}
