<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Requests\Api\V1\RegisterRequest;
use App\Http\Requests\Api\V1\ResetPasswordRequest;
use App\Models\User;
use App\Services\AuthService;
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
    public function verifyEmail(int $id, string $hash): RedirectResponse
    {
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
