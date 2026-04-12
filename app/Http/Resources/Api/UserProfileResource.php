<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $authenticatedUser = $request->user();
        $isOwnProfile = $authenticatedUser?->is($this->resource) ?? false;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->username,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->toDateString(),
            'address' => $this->address,
            'bio' => $this->bio,
            'avatar' => $this->avatar,
            'email_verified_at' => $this->email_verified_at,
            'social_profiles' => [
                'website_url' => $this->url_website,
                'facebook_url' => $this->url_facebook,
                'twitter_url' => $this->url_twitter,
                'instagram_url' => $this->url_instagram,
                'youtube_url' => $this->url_youtube,
                'linkedin_url' => $this->url_linkedin,
            ],
            'providers' => $this->when(
                $isOwnProfile,
                fn () => $this->providers
                    ->map(fn ($provider) => [
                        'id' => $provider->id,
                        'provider' => $provider->provider,
                    ])
                    ->values()
            ),
            'roles' => $this->when(
                $isOwnProfile,
                fn () => $this->roles->pluck('name')->values()
            ),
            'permissions' => $this->when(
                $isOwnProfile,
                fn () => $this->getAllPermissions()->pluck('name')->values()
            ),
            'can' => [
                'edit_profile' => $isOwnProfile,
            ],
        ];
    }
}
