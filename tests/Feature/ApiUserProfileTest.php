<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiUserProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_own_profile(): void
    {
        $user = User::factory()->create([
            'username' => 'profile-owner',
            'status' => 1,
            'social_profiles' => [
                'website_url' => 'https://example.com',
            ],
        ]);

        $token = $user->createToken('iPhone')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/users/profile')
            ->assertOk()
            ->assertJsonPath('data.username', 'profile-owner')
            ->assertJsonPath('data.social_profiles.website_url', 'https://example.com')
            ->assertJsonPath('data.can.edit_profile', true);
    }

    public function test_authenticated_user_can_view_another_users_profile(): void
    {
        $viewer = User::factory()->create([
            'username' => 'viewer-user',
            'status' => 1,
        ]);

        $profileOwner = User::factory()->create([
            'username' => 'target-user',
            'status' => 1,
        ]);

        $token = $viewer->createToken('Android')->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/v1/users/profile/'.$profileOwner->username)
            ->assertOk()
            ->assertJsonPath('data.username', 'target-user')
            ->assertJsonMissingPath('data.providers')
            ->assertJsonMissingPath('data.roles')
            ->assertJsonPath('data.can.edit_profile', false);
    }

    public function test_authenticated_user_can_update_own_profile(): void
    {
        $user = User::factory()->create([
            'username' => 'update-user',
            'status' => 1,
            'social_profiles' => null,
        ]);

        $token = $user->createToken('Pixel')->plainTextToken;

        $response = $this->withToken($token)
            ->patchJson('/api/v1/users/profile', [
                'first_name' => 'Nasir',
                'last_name' => 'Khan',
                'mobile' => '01700000000',
                'gender' => 'male',
                'date_of_birth' => '2000-01-01',
                'address' => 'Dhaka',
                'bio' => 'API updated bio',
                'social_profiles' => [
                    'website_url' => 'https://example.org',
                    'facebook_url' => 'https://facebook.com/example',
                ],
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.first_name', 'Nasir')
            ->assertJsonPath('data.last_name', 'Khan')
            ->assertJsonPath('data.mobile', '01700000000')
            ->assertJsonPath('data.social_profiles.website_url', 'https://example.org');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nasir Khan',
            'first_name' => 'Nasir',
            'last_name' => 'Khan',
            'mobile' => '01700000000',
            'gender' => 'male',
            'address' => 'Dhaka',
            'bio' => 'API updated bio',
        ]);
    }
}
