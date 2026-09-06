<?php

namespace App\Services;

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
     * Authenticate with Google and issue an AICO JWT access token.
     *
     * The Google user object is intentionally typed as object
     * to avoid IDE type-resolution issues with Socialite contracts.
     */
    public function loginWithGoogle(object $googleUser): array
    {
        $provider = 'google';
        $providerId = $googleUser->getId();
        $providerEmail = $googleUser->getEmail();

        if (! $providerId) {
            throw new AuthenticationException(
                'Google account identifier is unavailable.'
            );
        }

        if (! $providerEmail) {
            throw new AuthenticationException(
                'Google account email is unavailable.'
            );
        }

        $googleEmailVerified = filter_var(
            $googleUser->user['email_verified'] ?? false,
            FILTER_VALIDATE_BOOLEAN
        );

        /*
         * Existing Google social account.
         *
         * The Google identity has already been linked to
         * an AICO user, so login directly with that user.
         */
        $socialAccount = SocialAccount::query()
            ->where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;

            if (! $user) {
                throw new AuthenticationException(
                    'The Google account is not linked to a valid user.'
                );
            }

            $socialAccount->update([
                'provider_email' => $providerEmail,
            ]);

            $token = JWTAuth::fromUser($user);

            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => $user->fresh(),
            ];
        }

        /*
         * Google identity does not exist yet.
         *
         * Check whether an AICO local account already uses
         * the same email address.
         */
        $existingUser = User::query()
            ->where('email', $providerEmail)
            ->first();

        if ($existingUser) {
            /*
             * Do not automatically link an existing local account
             * unless Google confirms that the email is verified.
             */
            if (! $googleEmailVerified) {
                throw new AuthenticationException(
                    'Google email address has not been verified.'
                );
            }

            SocialAccount::create([
                'user_id' => $existingUser->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'provider_email' => $providerEmail,
            ]);

            /*
             * markEmailAsVerified() writes directly to the model,
             * so this does not depend on mass assignment.
             */
            if (! $existingUser->hasVerifiedEmail()) {
                $existingUser->markEmailAsVerified();
            }

            $token = JWTAuth::fromUser($existingUser);

            return [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => $existingUser->fresh(),
            ];
        }

        /*
         * Completely new AICO user.
         *
         * User + social account + default customer role
         * are created atomically.
         */
        $user = DB::transaction(function () use (
            $googleUser,
            $provider,
            $providerId,
            $providerEmail,
            $googleEmailVerified
        ): User {
            /*
             * The User model intentionally does not allow
             * email_verified_at through mass assignment.
             *
             * Therefore the verification timestamp is applied
             * after the user is created.
             */
            $user = User::create([
                'name' => $googleUser->getName()
                    ?: $googleUser->getNickname()
                    ?: 'Google User',

                'email' => $providerEmail,

                /*
                 * The users.password column is currently NOT NULL.
                 *
                 * Generate an unusable random password for a
                 * Google-authenticated account.
                 *
                 * User::$casts['password'] = 'hashed' will hash
                 * this value automatically.
                 */
                'password' => Str::random(40),
            ]);

            /*
             * Google confirmed the email.
             *
             * Mark it as verified after User::create() because
             * email_verified_at is intentionally not mass assignable.
             */
            if ($googleEmailVerified) {
                $user->markEmailAsVerified();
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerId,
                'provider_email' => $providerEmail,
            ]);

            /*
             * Assign the default customer role if it exists.
             */
            $customerRole = Role::query()
                ->where('slug', 'customer')
                ->first();

            if ($customerRole) {
                $user->roles()->attach($customerRole->id);
            }

            return $user;
        });

        $token = JWTAuth::fromUser($user);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
            'user' => $user->fresh(),
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
