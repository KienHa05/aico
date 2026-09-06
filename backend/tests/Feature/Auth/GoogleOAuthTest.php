<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_returns_redirect_url(): void
    {
        $response = $this->getJson(
            '/api/v1/auth/google/redirect'
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google OAuth redirect URL generated successfully.',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'redirect_url',
                ],
            ]);

        $redirectUrl = $response->json('data.redirect_url');

        $this->assertNotEmpty($redirectUrl);

        $this->assertStringStartsWith(
            'https://accounts.google.com/o/oauth2/auth?',
            $redirectUrl
        );

        $this->assertStringContainsString(
            'client_id=' . rawurlencode(config('services.google.client_id')),
            $redirectUrl
        );

        $this->assertStringContainsString(
            'redirect_uri=' . rawurlencode(config('services.google.redirect')),
            $redirectUrl
        );

        $this->assertStringContainsString(
            'scope=openid%20profile%20email',
            $redirectUrl
        );

        $this->assertStringContainsString(
            'response_type=code',
            $redirectUrl
        );
    }

    public function test_google_callback_creates_new_user_and_returns_token(): void
    {
        $googleUser = $this->makeGoogleUser(
            id: 'google-sub-123',
            email: 'test@gmail.com',
            name: 'Test User',
            emailVerified: true
        );

        $this->mockGoogleCallback($googleUser);

        $response = $this->getJson(
            '/api/v1/auth/google/callback'
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google login successful.',
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
                    'user' => [
                        'id',
                        'name',
                        'email',
                    ],
                ],
            ]);

        $this->assertNotEmpty(
            $response->json('data.access_token')
        );

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@gmail.com',
        ]);

        $user = User::query()
            ->where('email', 'test@gmail.com')
            ->firstOrFail();

        $this->assertNotNull(
            $user->email_verified_at
        );

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-sub-123',
            'provider_email' => 'test@gmail.com',
        ]);
    }

    public function test_google_callback_links_existing_user_with_verified_email(): void
    {
        $user = User::factory()->unverified()->create([
            'email' => 'test@gmail.com',
        ]);

        $googleUser = $this->makeGoogleUser(
            id: 'google-sub-123',
            email: 'test@gmail.com',
            name: 'Test User',
            emailVerified: true
        );

        $this->mockGoogleCallback($googleUser);

        $response = $this->getJson(
            '/api/v1/auth/google/callback'
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google login successful.',
                'data' => [
                    'token_type' => 'Bearer',
                ],
            ]);

        $this->assertSame(
            1,
            User::query()->count()
        );

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-sub-123',
        ]);

        $this->assertNotNull(
            $user->fresh()->email_verified_at
        );
    }

    public function test_google_callback_assigns_customer_role_to_new_user(): void
    {
        $customerRole = Role::create([
            'name' => 'Customer',
            'slug' => 'customer',
            'description' => 'Store customer',
        ]);

        $googleUser = $this->makeGoogleUser(
            id: 'google-sub-123',
            email: 'test@gmail.com',
            name: 'Test User',
            emailVerified: true
        );

        $this->mockGoogleCallback($googleUser);

        $response = $this->getJson(
            '/api/v1/auth/google/callback'
        );

        $response->assertStatus(200);

        $user = User::query()
            ->where('email', 'test@gmail.com')
            ->firstOrFail();

        $this->assertTrue(
            $user->hasRole('customer')
        );

        $this->assertDatabaseHas('role_user', [
            'role_id' => $customerRole->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_google_callback_returns_token_for_existing_social_account(): void
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
        ]);

        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-sub-123',
            'provider_email' => 'test@gmail.com',
        ]);

        $googleUser = $this->makeGoogleUser(
            id: 'google-sub-123',
            email: 'test@gmail.com',
            name: 'Test User',
            emailVerified: true
        );

        $this->mockGoogleCallback($googleUser);

        $response = $this->getJson(
            '/api/v1/auth/google/callback'
        );

        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Google login successful.',
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

        $this->assertSame(
            1,
            User::query()->count()
        );

        $this->assertSame(
            1,
            SocialAccount::query()->count()
        );
    }

    public function test_google_callback_does_not_link_unverified_email(): void
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
        ]);

        $googleUser = $this->makeGoogleUser(
            id: 'google-sub-123',
            email: 'test@gmail.com',
            name: 'Test User',
            emailVerified: false
        );

        $this->mockGoogleCallback($googleUser);

        $response = $this->getJson(
            '/api/v1/auth/google/callback'
        );

        $response
            ->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Google email address has not been verified.',
            ]);

        $this->assertDatabaseCount(
            'social_accounts',
            0
        );

        $this->assertDatabaseCount(
            'users',
            1
        );

        $this->assertFalse(
            $user->fresh()->socialAccounts()->exists()
        );
    }

    /**
     * Mock the Socialite factory without importing
     * Socialite facade or provider implementation classes.
     */
    private function mockSocialiteDriver(object $driver): void
    {
        $factory = Mockery::mock();

        $factory
            ->shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($driver);

        $this->app->instance(
            'Laravel\Socialite\Contracts\Factory',
            $factory
        );
    }

    /**
     * Mock the Google OAuth callback.
     */
    private function mockGoogleCallback(object $googleUser): void
    {
        $driver = Mockery::mock();

        $driver
            ->shouldReceive('stateless')
            ->once()
            ->andReturnSelf();

        $driver
            ->shouldReceive('user')
            ->once()
            ->andReturn($googleUser);

        $this->mockSocialiteDriver($driver);
    }

    /**
     * Create a fake Google user object.
     *
     * This intentionally uses a generic object so the test
     * does not depend on Socialite implementation classes.
     */
    private function makeGoogleUser(
        string $id,
        string $email,
        string $name,
        bool $emailVerified
    ): object {
        $googleUser = Mockery::mock();

        $googleUser
            ->shouldReceive('getId')
            ->andReturn($id);

        $googleUser
            ->shouldReceive('getEmail')
            ->andReturn($email);

        $googleUser
            ->shouldReceive('getName')
            ->andReturn($name);

        $googleUser
            ->shouldReceive('getNickname')
            ->andReturn(null);

        $googleUser->user = [
            'sub' => $id,
            'email' => $email,
            'email_verified' => $emailVerified,
            'name' => $name,
        ];

        return $googleUser;
    }
}
