<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password as PasswordBroker;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:191'],
            'email' => ['required', 'string', 'email:rfc', 'max:191', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
            'password_confirmation' => ['required', 'string'],
        ]);

        // Never accept role or status from a public registration request.
        $user = new User();
        $user->name = $validated['name'];
        $user->email = strtolower($validated['email']);
        $user->role_as = User::ROLE_USER;
        $user->status = User::STATUS_ACTIVE;
        $user->password = Hash::make($validated['password']);
        $user->save();

        $newToken = $user->createToken($user->email.'_Token', ['server:user']);

        return response()->json([
            'status' => 201,
            'id' => $user->id,
            'role_as' => $user->role_as,
            'name' => $user->name,
            'email' => $user->email,
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->created_at
                ->addMinutes(config('sanctum.expiration'))
                ->toIso8601String(),
            'message' => 'Registered successfully',
        ], 201);
    }

    public function fetchUserList()
    {
        $users = User::where('status', User::STATUS_ACTIVE)
            ->get(['id', 'name', 'email', 'role_as', 'status', 'created_at']);

        return response()->json($users);
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'You cannot delete your own account.',
            ], 422);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->noContent();
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();

        if (
            ! $user
            || $user->status !== User::STATUS_ACTIVE
            || ! Hash::check($validated['password'], $user->password)
        ) {
            return response()->json([
                'status' => 401,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->role_as === User::ROLE_ADMIN) {
            $newToken = $user->createToken($user->email.'_AdminToken', ['server:admin']);
        } else {
            $newToken = $user->createToken($user->email.'_Token', ['server:user']);
        }

        return response()->json([
            'status' => 200,
            'id' => $user->id,
            'username' => $user->name,
            'name' => $user->name,
            'email' => $user->email,
            'role_as' => $user->role_as,
            'token' => $newToken->plainTextToken,
            'expires_at' => $newToken->accessToken->created_at
                ->addMinutes(config('sanctum.expiration'))
                ->toIso8601String(),
            'message' => 'Logged in successfully',
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Log out Successfully',
        ]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        if (Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'message' => 'The new password must be different from the current password.',
                'errors' => [
                    'password' => ['The new password must be different from the current password.'],
                ],
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        $currentToken = $user->currentAccessToken();

        if ($currentToken && isset($currentToken->id)) {
            $user->tokens()->where('id', '!=', $currentToken->id)->delete();
        } else {
            $user->tokens()->delete();
        }

        return response()->json([
            'message' => 'Password changed successfully.',
        ]);
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
        ]);

        PasswordBroker::sendResetLink([
            'email' => strtolower($validated['email']),
        ]);

        // Always return the same response so this endpoint cannot reveal accounts.
        return response()->json([
            'message' => 'If an account exists for that email, a password reset link has been sent.',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'string', 'email:rfc', 'max:191'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(12)->mixedCase()->numbers()->symbols(),
            ],
            'password_confirmation' => ['required', 'string'],
        ]);

        $status = PasswordBroker::reset(
            [
                'email' => strtolower($validated['email']),
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete();

                event(new PasswordReset($user));
            }
        );

        if ($status !== PasswordBroker::PASSWORD_RESET) {
            return response()->json([
                'message' => 'This password reset link is invalid or has expired.',
                'errors' => [
                    'email' => ['This password reset link is invalid or has expired.'],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Password reset successfully. You can now sign in.',
        ]);
    }
}


