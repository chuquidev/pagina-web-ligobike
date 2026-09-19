<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'store_name' => $this->store_name,
            'whatsapp_number' => $this->whatsapp_number,
            'email' => $this->email,
            'facebook_url' => $this->facebook_url,
            'instagram_url' => $this->instagram_url,
            'tiktok_url' => $this->tiktok_url,
            'address' => $this->address,
            'schedule' => $this->schedule,
            'privacy_policy' => $this->privacy_policy,
            'terms_conditions' => $this->terms_conditions,
            'about_content' => $this->about_content,
            'size_guide' => $this->size_guide ?? [],
            'primary_color' => $this->primary_color,
            'secondary_color' => $this->secondary_color,
            'logo' => $this->getFirstMediaUrl('logo') ?: null,
            'about_images' => $this->getMedia('about')->map(fn($m) => [
                'id' => $m->id,
                'url' => $m->hasGeneratedConversion('gallery') ? $m->getUrl('gallery') : $m->getUrl(),
            ])->all(),
        ];
    }
}