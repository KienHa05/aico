<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

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
     * Authenticate user credentials.
     */
    public function login(array $data): ?User
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            return null;
        }

        if (! Hash::check($data['password'], $user->password)) {
            return null;
        }

        return $user;
    }
}
