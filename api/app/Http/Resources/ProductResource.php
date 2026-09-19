<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'features' => $this->features,
            'price' => $this->price,
            'sale_price' => $this->sale_price,
            'stock' => $request->user() ? $this->stock : null,
            'min_price' => $request->user() ? $this->min_price : null,
            'availability' => $this->availability,
            'is_featured' => $this->is_featured,
            'is_active' => $this->is_active,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => $this->brand ? new BrandResource($this->brand) : null,
            'images' => $this->getMedia('images')->map(fn($media) => [
                'id' => $media->id,
                'thumb' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                'large' => $media->hasGeneratedConversion('large') ? $media->getUrl('large') : $media->getUrl(),
            ])->all(),
        ];
    }
}
