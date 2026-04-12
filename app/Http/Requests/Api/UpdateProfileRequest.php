<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:191'],
            'last_name' => ['required', 'string', 'max:191'],
            'mobile' => ['nullable', 'string', 'max:191'],
            'gender' => ['nullable', 'string', 'max:191'],
            'date_of_birth' => ['nullable', 'date'],
            'address' => ['nullable', 'string'],
            'bio' => ['nullable', 'string', 'max:191'],
            'avatar' => ['nullable', 'image', 'max:2048'],
            'social_profiles' => ['nullable', 'array'],
            'social_profiles.website_url' => ['nullable', 'url', 'max:191'],
            'social_profiles.facebook_url' => ['nullable', 'url', 'max:191'],
            'social_profiles.twitter_url' => ['nullable', 'url', 'max:191'],
            'social_profiles.instagram_url' => ['nullable', 'url', 'max:191'],
            'social_profiles.youtube_url' => ['nullable', 'url', 'max:191'],
            'social_profiles.linkedin_url' => ['nullable', 'url', 'max:191'],
        ];
    }
}
