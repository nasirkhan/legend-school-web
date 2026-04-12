<?php

namespace App\Http\Controllers\Api;

use App\Events\Auth\UserLoginSuccess;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Exchange user credentials for a Sanctum token for mobile clients.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()
            ->where('email', $request->string('email')->toString())
            ->where('status', 1)
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        event(new UserLoginSuccess($request, $user));

        $token = $user->createToken(
            name: $request->string('device_name')->toString(),
            abilities: ['*'],
        )->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token_type' => 'Bearer',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'username' => $user->username,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    /**
     * Revoke the current access token (logout for mobile clients).
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }
}
