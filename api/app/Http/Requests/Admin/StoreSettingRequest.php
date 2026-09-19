<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'facebook_url' => ['nullable', 'url'],
            'instagram_url' => ['nullable', 'url'],
            'tiktok_url' => ['nullable', 'url'],
            'address' => ['nullable', 'string', 'max:255'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'privacy_policy' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'about_content' => ['nullable', 'string'],
            'size_guide' => ['nullable', 'array'],
            'size_guide.*.height' => ['required_with:size_guide', 'string', 'max:100'],
            'size_guide.*.size' => ['required_with:size_guide', 'string', 'max:100'],
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'about_images' => ['nullable', 'array'],
            'about_images.*' => ['image', 'max:4096'],
        ];
    }
}
