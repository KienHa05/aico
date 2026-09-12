<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class RefreshTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_refresh_token(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        $response = $this
            ->withToken($accessToken)
            ->postJson('/api/v1/auth/refresh');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user',
                ],
            ]);

        $this->assertNotEmpty(
            $response->json('data.access_token')
        );

        $this->assertNotEquals(
            $accessToken,
            $response->json('data.access_token')
        );
    }

    public function test_new_token_can_access_protected_route(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        $refreshResponse = $this
            ->withToken($accessToken)
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(200);

        $newToken = $refreshResponse->json('data.access_token');

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        $response = $this
            ->withToken($newToken)
            ->getJson('/api/v1/auth/me');

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSame(
            'test@example.com',
            $response->json('data.email')
        );
    }

    public function test_old_token_cannot_be_used_after_refresh(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        $this
            ->withToken($accessToken)
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(200);

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        $response = $this
            ->withToken($accessToken)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_old_token_cannot_be_refreshed_again(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        $this
            ->withToken($accessToken)
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(200);

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        $response = $this
            ->withToken($accessToken)
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(401);
    }

    public function test_expired_token_within_refresh_ttl_can_be_refreshed(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        /*
         * Create a real signed JWT two hours in the past.
         *
         * JWT_TTL = 60 minutes, so the token is expired.
         * JWT_REFRESH_TTL = 20160 minutes, so it is still refreshable.
         */
        Carbon::setTestNow(
            Carbon::now()->subHours(2)
        );

        try {
            $expiredAccessToken = JWTAuth::fromUser($user);
        } finally {
            Carbon::setTestNow();
        }

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        /*
         * Refresh the expired token.
         */
        $refreshResponse = $this
            ->withToken($expiredAccessToken)
            ->postJson('/api/v1/auth/refresh');

        $refreshResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Token refreshed successfully.',
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user',
                ],
            ]);

        $newToken = $refreshResponse->json('data.access_token');

        $this->assertNotEmpty($newToken);
        $this->assertNotSame($expiredAccessToken, $newToken);

        /*
         * Clear authentication/JWT state before testing the old token.
         */
        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        /*
         * The original token must be revoked after rotation.
         */
        $oldTokenResponse = $this
            ->withToken($expiredAccessToken)
            ->getJson('/api/v1/auth/me');

        $oldTokenResponse->assertStatus(401);

        /*
         * Clear authentication/JWT state before testing the new token.
         */
        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        /*
         * The new token must be a normal usable access token.
         */
        $newTokenResponse = $this
            ->withToken($newToken)
            ->getJson('/api/v1/auth/me');

        $newTokenResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertSame(
            'test@example.com',
            $newTokenResponse->json('data.email')
        );
    }

    public function test_expired_token_outside_refresh_ttl_cannot_be_refreshed(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $issuedAt = Carbon::now()->subMinutes(
            config('jwt.refresh_ttl') + 60
        );

        Carbon::setTestNow($issuedAt);

        try {
            $expiredAccessToken = JWTAuth::fromUser($user);
        } finally {
            Carbon::setTestNow();
        }

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        $response = $this
            ->withToken($expiredAccessToken)
            ->postJson('/api/v1/auth/refresh');

        $response->assertStatus(401);
    }

    public function test_refresh_requires_authentication(): void
    {
        $this
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(401);
    }

    public function test_invalid_token_cannot_be_refreshed(): void
    {
        $this
            ->withToken('invalid-token')
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(401);
    }

    public function test_refreshed_token_is_revoked_after_logout(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $loginResponse = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $accessToken = $loginResponse->json('data.access_token');

        $refreshResponse = $this
            ->withToken($accessToken)
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(200);

        $refreshedToken = $refreshResponse->json('data.access_token');

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        $logoutResponse = $this
            ->withToken($refreshedToken)
            ->postJson('/api/v1/auth/logout');

        $logoutResponse
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Logout successful.',
                'data' => [],
            ]);

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        $response = $this
            ->withToken($refreshedToken)
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(401);
    }

    public function test_refresh_window_rolls_forward_after_token_rotation(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $initialTime = Carbon::now();

        /*
         * Create Token A at the initial time.
         */
        Carbon::setTestNow($initialTime);

        try {
            $firstToken = JWTAuth::fromUser($user);
        } finally {
            Carbon::setTestNow();
        }

        /*
         * Refresh Token A close to the end of its original
         * 14-day refresh window.
         *
         * Token A is expired because JWT_TTL is 60 minutes,
         * but it is still inside JWT_REFRESH_TTL.
         */
        $firstRefreshTime = $initialTime->copy()->addDays(13)->addHours(23);

        Carbon::setTestNow($firstRefreshTime);

        try {
            $firstRefreshResponse = $this
                ->withToken($firstToken)
                ->postJson('/api/v1/auth/refresh');

            $firstRefreshResponse->assertStatus(200);

            $secondToken = $firstRefreshResponse->json(
                'data.access_token'
            );
        } finally {
            Carbon::setTestNow();
        }

        $this->assertNotEmpty($secondToken);
        $this->assertNotSame($firstToken, $secondToken);

        app('auth')->forgetGuards();
        app('tymon.jwt')->unsetToken();
        app('tymon.jwt.auth')->unsetToken();

        /*
         * Move past Token A's original 14-day refresh window.
         *
         * We are now:
         *
         * T0 + 14 days + 1 hour
         *
         * But Token B was issued at:
         *
         * T0 + 13 days + 23 hours
         *
         * Therefore Token B is only 2 hours old and remains inside
         * its own rolling 14-day refresh window.
         */
        $secondRefreshTime = $initialTime
            ->copy()
            ->addDays(14)
            ->addHours(1);

        Carbon::setTestNow($secondRefreshTime);

        try {
            $secondRefreshResponse = $this
                ->withToken($secondToken)
                ->postJson('/api/v1/auth/refresh');

            $secondRefreshResponse
                ->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Token refreshed successfully.',
                    'data' => [
                        'token_type' => 'Bearer',
                    ],
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'access_token',
                        'token_type',
                        'expires_in',
                        'user',
                    ],
                ]);
        } finally {
            Carbon::setTestNow();
        }

        $thirdToken = $secondRefreshResponse->json(
            'data.access_token'
        );

        $this->assertNotEmpty($thirdToken);
        $this->assertNotSame($secondToken, $thirdToken);
    }
}
