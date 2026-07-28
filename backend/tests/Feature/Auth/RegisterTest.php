<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;


    public function test_user_can_register_successfully(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);


        $response
            ->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Register successfully.',
            ]);


        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }


    public function test_user_cannot_register_with_existing_email(): void
    {
        User::factory()->create([
            'email' => 'john@example.com',
        ]);


        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);


        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
            ]);
    }


    public function test_user_password_is_hashed_when_registering(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);


        $response->assertStatus(201);


        $user = User::where(
            'email',
            'john@example.com'
        )->first();


        $this->assertNotEquals(
            'password123',
            $user->password
        );
    }

    public function test_user_cannot_register_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/register', []);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'email',
                'password',
            ]);
    }


    public function test_user_cannot_register_with_invalid_password_confirmation(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'wrong-password',
        ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'password',
            ]);
    }
}
