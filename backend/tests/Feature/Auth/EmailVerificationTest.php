<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_registration_sends_email_verification_notification(): void
    {
        Notification::fake();

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated();

        $user = User::where('email', 'test@example.com')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        Notification::assertSentTo(
            $user,
            VerifyEmail::class
        );
    }

    public function test_user_can_verify_email_with_valid_signed_url(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'api.v1.auth.verify-email',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.url'));

        $this->assertNotNull(
            $user->fresh()->email_verified_at
        );
    }

    public function test_invalid_email_hash_cannot_verify_email(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = URL::temporarySignedRoute(
            'api.v1.auth.verify-email',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1('wrong@example.com'),
            ]
        );

        $response = $this->get($url);

        $response->assertForbidden();

        $this->assertNull(
            $user->fresh()->email_verified_at
        );
    }

    public function test_already_verified_user_remains_verified(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $verifiedAt = $user->email_verified_at;

        $url = URL::temporarySignedRoute(
            'api.v1.auth.verify-email',
            now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $response = $this->get($url);

        $response->assertRedirect(config('app.url'));

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals(
            $verifiedAt->timestamp,
            $user->email_verified_at->timestamp
        );
    }

    public function test_email_verification_requires_valid_signed_url(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $url = route('api.v1.auth.verify-email', [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ]);

        $response = $this->get($url);

        $response->assertForbidden();

        $this->assertNull(
            $user->fresh()->email_verified_at
        );
    }
}
