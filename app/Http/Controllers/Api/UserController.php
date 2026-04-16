<?php

namespace App\Http\Controllers\Api;

use App\ApiAuthorizable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\Api\UserProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserController extends Controller
{
    use ApiAuthorizable;

    public function __construct()
    {
        $this->setApiAbilities([
            'show' => null,
            'update' => null,
        ]);
    }

    /**
     * Show the authenticated user's profile or another user's profile by username.
     */
    public function show(Request $request, ?string $username = null): JsonResource
    {
        $user = $username
            ? User::query()
                ->with(['providers', 'roles.permissions', 'permissions'])
                ->where('username', $username)
                ->firstOrFail()
            : $request->user()->loadMissing(['providers', 'roles.permissions', 'permissions']);

        return UserProfileResource::make($user);
    }

    /**
     * Update the authenticated user's profile using the same self-service rules as the frontend.
     */
    public function update(UpdateProfileRequest $request): JsonResource
    {
        $user = $request->user()->loadMissing(['providers', 'roles.permissions', 'permissions']);

        $data = $request->safe()->except(['avatar']);

        // Keep the name column in sync with first_name / last_name.
        $firstName = $data['first_name'] ?? $user->first_name;
        $lastName = $data['last_name'] ?? $user->last_name;
        $data['name'] = trim($firstName.' '.$lastName);

        $user->update($data);

        if ($request->hasFile('avatar')) {
            $existingMedia = $user->getMedia('users')->first();

            if ($existingMedia) {
                $existingMedia->delete();
            }

            $media = $user->addMedia($request->file('avatar'))
                ->toMediaCollection('users');

            $user->update(['avatar' => $media->getUrl()]);
        }

        return UserProfileResource::make(
            $user->fresh()->loadMissing(['providers', 'roles.permissions', 'permissions'])
        );
    }
}
