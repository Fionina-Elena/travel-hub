<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login_with_valid_credentials(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'secret123',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'user']);
    }

    public function test_cannot_login_with_invalid_password(): void
    {
        AdminUser::factory()->create([
            'email' => 'admin@test.com',
            'password' => Hash::make('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@test.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_cannot_login_with_invalid_email(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'notexist@test.com',
            'password' => 'anypassword',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }

    public function test_login_requires_email_and_password(): void
    {
        $response = $this->postJson('/api/v1/auth/login', []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_can_get_current_user_with_valid_token(): void
    {
        $user = AdminUser::factory()->create();
        $token = base64_encode(json_encode([
            'user_id' => $user->id,
            'exp' => time() + 86400,
        ]));

        $response = $this->withHeader('X-Admin-Token', $token)->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('id', $user->id);
    }

    public function test_cannot_access_me_without_token(): void
    {
        $response = $this->getJson('/api/v1/auth/me');

        $response->assertUnauthorized()
            ->assertJsonPath('error', 'Unauthorized');
    }

    public function test_cannot_access_me_with_expired_token(): void
    {
        $user = AdminUser::factory()->create();
        $token = base64_encode(json_encode([
            'user_id' => $user->id,
            'exp' => time() - 3600,
        ]));

        $response = $this->getJson('/api/v1/auth/me', [
            'X-Admin-Token' => $token,
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('error', 'Token expired');
    }
}
