<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        event(new Registered($user));

        return $user;
    }

    /**
     * Authenticate user credentials and issue JWT access token.
     */
    public function login(array $credentials): ?array
    {
        $token = JWTAuth::attempt($credentials);

        if (! $token) {
            return null;
        }

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => JWTAuth::user(),
        ];
    }

    /**
     * Refresh the current JWT access token.
     */
    public function refresh(): array
    {
        $token = JWTAuth::parseToken()->refresh();

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => JWTAuth::setToken($token)->user(),
        ];
    }

    /**
     * Send a password reset link to the given user.
     */
    public function forgotPassword(array $credentials): string
    {
        return Password::sendResetLink($credentials);
    }

    /**
     * Reset the user's password.
     */
    public function resetPassword(array $credentials): string
    {
        return Password::reset(
            $credentials,
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($user));
            }
        );
    }

    /**
     * Get the currently authenticated user.
     *
     * @throws AuthenticationException
     */
    public function me(): User
    {
        $user = JWTAuth::user();

        if (! $user) {
            throw new AuthenticationException();
        }

        return $user;
    }

    /**
     * Invalidate the current JWT token.
     */
    public function logout(): void
    {
        $token = JWTAuth::getToken();

        if ($token) {
            JWTAuth::invalidate($token);
        }
    }
}
