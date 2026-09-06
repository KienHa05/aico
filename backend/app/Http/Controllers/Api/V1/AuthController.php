<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Register successfully.',
            'data' => $user,
        ], 201);
    }

    /**
     * Authenticate user credentials.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $tokenData = $this->authService->login(
            $request->validated()
        );

        if (! $tokenData) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => $tokenData,
        ]);
    }

    /**
     * Generate the Google OAuth authorization URL.
     *
     * The authorization URL is built explicitly using RFC 3986
     * encoding so OAuth scopes are encoded as %20 instead of +.
     */
    public function googleRedirect(): JsonResponse
    {
        $query = http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'scope' => 'openid profile email',
            'response_type' => 'code',
        ], '', '&', PHP_QUERY_RFC3986);

        $redirectUrl = 'https://accounts.google.com/o/oauth2/auth?' . $query;

        return response()->json([
            'success' => true,
            'message' => 'Google OAuth redirect URL generated successfully.',
            'data' => [
                'redirect_url' => $redirectUrl,
            ],
        ]);
    }

    /**
     * Handle the Google OAuth callback and issue an AICO JWT.
     */
    public function googleCallback(): JsonResponse
    {
        try {
            $socialite = app('Laravel\Socialite\Contracts\Factory');

            $googleUser = $socialite
                ->driver('google')
                ->stateless()
                ->user();

            $tokenData = $this->authService->loginWithGoogle(
                $googleUser
            );

            return response()->json([
                'success' => true,
                'message' => 'Google login successful.',
                'data' => $tokenData,
            ]);
        } catch (\Throwable $e) {
            /*
             * Socialite InvalidStateException.
             *
             * The class is referenced by string so the IDE does not
             * need to resolve the package implementation directly.
             */
            if (
                is_a(
                    $e,
                    'Laravel\\Socialite\\Two\\InvalidStateException'
                )
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Google OAuth state.',
                ], 422);
            }

            if ($e instanceof AuthenticationException) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 401);
            }

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Unable to authenticate with Google.',
            ], 500);
        }
    }

    /**
     * Refresh the current JWT access token.
     */
    public function refresh(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully.',
            'data' => $this->authService->refresh(),
        ]);
    }

    /**
     * Send password reset link.
     */
    public function forgotPassword(
        ForgotPasswordRequest $request
    ): JsonResponse {
        $status = $this->authService->forgotPassword(
            $request->validated()
        );

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'success' => false,
                'message' => __($status),
            ], 429);
        }

        if (
            $status === Password::RESET_LINK_SENT ||
            $status === Password::INVALID_USER
        ) {
            return response()->json([
                'success' => true,
                'message' => 'If your email address exists in our system, you will receive a password reset link shortly.',
                'data' => (object) [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 400);
    }

    /**
     * Reset password.
     */
    public function resetPassword(
        ResetPasswordRequest $request
    ): JsonResponse {
        $status = $this->authService->resetPassword(
            $request->validated()
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json([
                'success' => true,
                'message' => 'Password reset successfully.',
                'data' => (object) [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __($status),
        ], 400);
    }

    /**
     * Get the currently authenticated user.
     */
    public function me(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Authenticated user retrieved successfully.',
            'data' => $this->authService->me(),
        ]);
    }

    /**
     * Verify the user's email address.
     */
    public function verifyEmail(
        int $id,
        string $hash
    ): RedirectResponse {
        $user = User::findOrFail($id);

        if (! hash_equals(
            sha1($user->getEmailForVerification()),
            $hash
        )) {
            abort(403, 'Invalid email verification link.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return redirect()->to(config('app.url'));
    }

    /**
     * Logout the currently authenticated user.
     */
    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return response()->json([
            'success' => true,
            'message' => 'Logout successful.',
            'data' => (object) [],
        ]);
    }
}
