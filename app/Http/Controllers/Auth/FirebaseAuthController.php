<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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
        } catch (\RuntimeException) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in could not verify your account. Please try again.',
            ], 401);
        }

        $email = strtolower(trim((string) $firebaseUser->email));

        if ($email === '' || ! $firebaseUser->emailVerified) {
            return response()->json([
                'success' => false,
                'message' => 'Google sign-in requires a verified email address.',
            ], 403);
        }

        $userByUid = User::where('firebase_uid', $firebaseUser->uid)->first();
        $userByEmail = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (
            ($userByUid && $userByEmail && $userByUid->isNot($userByEmail))
            || ($userByEmail && filled($userByEmail->firebase_uid) && $userByEmail->firebase_uid !== $firebaseUser->uid)
        ) {
            return response()->json([
                'success' => false,
                'message' => 'This Google account is linked to another user.',
            ], 409);
        }

        $user = $userByUid ?? $userByEmail;

        if ($user?->hasAnyRole(['admin', 'super_admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Administrator accounts must use the administrator login.',
            ], 403);
        }

        if (! $user) {
            $user = DB::transaction(function () use ($firebaseUser, $email): User {
                $user = User::create([
                    'name' => $firebaseUser->displayName ?: 'User',
                    'email' => $email,
                    'password' => Hash::make(Str::random(40)),
                    'firebase_uid' => $firebaseUser->uid,
                    'avatar' => $firebaseUser->photoUrl,
                ]);

                $user->forceFill(['email_verified_at' => now()])->save();
                Role::findOrCreate('user', 'web');
                $user->assignRole('user');

                return $user;
            });
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
                $user->forceFill($updates)->save();
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
    }
}
