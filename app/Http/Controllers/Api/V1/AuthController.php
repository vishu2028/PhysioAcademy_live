<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        // 1. Email se user find karo
        $user = User::where('email', $request->email)->first();

        // 2. Email ya password incorrect ho
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password is incorrect.',
            ], 422);
        }

        // 3. ROLE CHECK YAHAN ADD KARNA HAI
        // Sirf "user" role wala mobile app mein login kar sakta hai
        if (! $user->hasRole('user')) {
            return response()->json([
                'success' => false,
                'message' => 'You are not allowed to access the user application.',
            ], 403);
        }

        // 4. Login successful ho to token create karo
        $token = $user
            ->createToken($request->device_name)
            ->plainTextToken;

        // 5. Response return karo
        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ]);
    }
}
