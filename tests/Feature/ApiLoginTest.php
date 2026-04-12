<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_issues_a_sanctum_token_for_valid_api_login_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'mobile@example.com',
            'password' => 'secret-password',
            'status' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret-password',
            'device_name' => 'Nasir Android',
        ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'message',
                'token_type',
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'username',
                    'email_verified_at',
                ],
            ])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.email', $user->email);

        $this->assertCount(1, $user->fresh()->tokens);
    }

    public function test_mobile_token_can_access_the_authenticated_api_user_route(): void
    {
        $user = User::factory()->create([
            'email' => 'token-user@example.com',
            'password' => 'secret-password',
            'status' => 1,
        ]);

        $token = $user->createToken('Test Device')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('email', $user->email);
    }
}
