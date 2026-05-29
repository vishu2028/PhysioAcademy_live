<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\FirebaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class FirebaseAuthController extends Controller
{
    protected $firebase;

    public function __construct(FirebaseService $firebase)
    {
        $this->firebase = $firebase;
    }

    /**
     * Handle Firebase ID Token login.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        try {
            $firebaseUser = $this->firebase->verifyToken($request->idToken);
            
            $user = User::where('firebase_uid', $firebaseUser->uid)
                ->orWhere('email', $firebaseUser->email)
                ->first();

            if (!$user) {
                // Create new user if not exists
                $user = User::create([
                    'name' => $firebaseUser->displayName ?? 'User',
                    'email' => $firebaseUser->email,
                    'password' => bcrypt(Str::random(16)),
                    'firebase_uid' => $firebaseUser->uid,
                ]);
                
                $user->assignRole('user');
            } else {
                // Update firebase_uid if not set
                if (!$user->firebase_uid) {
                    $user->update(['firebase_uid' => $firebaseUser->uid]);
                }
            }

            Auth::login($user);

            return response()->json([
                'success' => true,
                'redirect' => route('dashboard'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
