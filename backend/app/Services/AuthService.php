<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthService
{
    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
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
