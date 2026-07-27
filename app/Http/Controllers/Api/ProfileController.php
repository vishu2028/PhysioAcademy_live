<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return new UserResource($request->user());
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }
    public function updateAvatar(Request $request)
    {
        $validated = $request->validate([
            'avatar' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        $user = $request->user();

        if (
            $user->avatar &&
            Storage::disk('public')->exists($user->avatar)
        ) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->avatar = $request
            ->file('avatar')
            ->store('avatars', 'public');

        $user->save();

        return response()->json([
            'message' => 'Profile avatar updated successfully.',
            'data' => new UserResource($user->fresh()),
        ]);
    }
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'different:current_password',
            ],
        ]);

        $user = $request->user();

        if (! Hash::check(
            $validated['current_password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => [
                        'The current password is incorrect.',
                    ],
                ],
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }
    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'current_password' => [
                'required',
                'string',
            ],
        ]);

        $user = $request->user();

        if (! Hash::check(
            $validated['current_password'],
            $user->password
        )) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => [
                        'The current password is incorrect.',
                    ],
                ],
            ], 422);
        }

        $avatarPath = $user->avatar;
        $userId = $user->id;
        $userEmail = $user->email;

        DB::transaction(function () use (
            $user,
            $userId,
            $userEmail
        ) {
            // Revoke all mobile/API login tokens.
            $user->tokens()->delete();

            // Delete database notifications.
            $user->notifications()->delete();

            // Remove assigned roles.
            $user->roles()->detach();

            // Remove database sessions.
            DB::table('sessions')
                ->where('user_id', $userId)
                ->delete();

            // Remove any unused password reset token.
            DB::table('password_reset_tokens')
                ->where('email', $userEmail)
                ->delete();

            // Permanently delete the user account.
            $user->delete();
        });

        // Delete uploaded avatar after successful database deletion.
        if (
            $avatarPath &&
            Storage::disk('public')->exists($avatarPath)
        ) {
            Storage::disk('public')->delete($avatarPath);
        }

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}
