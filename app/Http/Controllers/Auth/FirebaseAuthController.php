<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FirebaseAuthController extends Controller
{
    public function __construct(
        protected FirebaseService $firebase
    ) {
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $firebaseUser = $this->firebase->verifyToken($validated['id_token']);
            $email = strtolower(trim((string) $firebaseUser->email));

            if ($email === '' || ! $firebaseUser->emailVerified) {
                throw new \RuntimeException('Google account email is not verified.');
            }

            $userByUid = User::where('firebase_uid', $firebaseUser->uid)->first();
            $userByEmail = User::whereRaw('LOWER(email) = ?', [$email])->first();

            if ($userByUid && $userByEmail && $userByUid->isNot($userByEmail)) {
                throw new \RuntimeException('This Google account is linked to another user.');
            }

            $user = $userByUid ?? $userByEmail;

            if (! $user) {
                $user = User::create([
                    'name' => $firebaseUser->displayName ?: 'User',
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                    'firebase_uid' => $firebaseUser->uid,
                    'avatar' => $firebaseUser->photoUrl,
                ]);

                $user->assignRole('user');
            } else {
                $updates = [];

                if (! $user->firebase_uid) {
                    $updates['firebase_uid'] = $firebaseUser->uid;
                }

                if (! $user->avatar && $firebaseUser->photoUrl) {
                    $updates['avatar'] = $firebaseUser->photoUrl;
                }

                if (! $user->email_verified_at) {
                    $updates['email_verified_at'] = now();
                }

                if ($updates) {
                    $user->update($updates);
                }
            }

            Auth::login($user);
            $request->session()->regenerate();

            $redirect = $request->session()->pull(
                'url.intended',
                route('home', absolute: false)
            );

            $redirectPath = is_string($redirect) ? parse_url($redirect, PHP_URL_PATH) : null;

            if (! is_string($redirect) || $redirectPath === null || str_starts_with($redirectPath, '/admin')) {
                $redirect = route('home', absolute: false);
            }

            return response()->json([
                'success' => true,
                'redirect' => $redirect,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Google sign-in could not be completed. Please try again.',
            ], 401);
        }
    }
}
