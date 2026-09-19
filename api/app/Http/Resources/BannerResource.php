<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'button_text' => $this->button_text,
            'button_url' => $this->button_url,
            'order' => $this->order,
            'is_active' => $this->is_active,
            'image' => $this->getFirstMedia('image')
                ? ($this->getFirstMedia('image')->hasGeneratedConversion('banner')
                    ? $this->getFirstMediaUrl('image', 'banner')
                    : $this->getFirstMediaUrl('image'))
                : null,
        ];
    }
}
