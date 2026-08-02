<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        /**
         * Reset authentication state between simulated HTTP requests.
         *
         * Laravel Feature Tests reuse the same application instance.
         * JWT package keeps the parsed token inside singleton services,
         * therefore both the auth guards and JWT singletons must be reset
         * before issuing the next simulated request.
         */
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

    public function test_refresh_requires_authentication(): void
    {
        $this->postJson('/api/v1/auth/refresh')
            ->assertStatus(401);
    }

    public function test_invalid_token_cannot_be_refreshed(): void
    {
        $this
            ->withToken('invalid-token')
            ->postJson('/api/v1/auth/refresh')
            ->assertStatus(401);
    }
}
