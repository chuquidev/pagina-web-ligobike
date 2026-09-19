<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class StoreSetting extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'store_name',
        'whatsapp_number',
        'email',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'address',
        'schedule',
        'privacy_policy',
        'terms_conditions',
        'primary_color',
        'secondary_color',
        'about_content',
        'size_guide',
    ];

    protected $casts = [
        'size_guide' => 'array',
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('public.settings'));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
        $this->addMediaCollection('about');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('logo')
            ->fit(Fit::Max, 512, 512)
            ->format('webp');

        $this->addMediaConversion('gallery')
            ->fit(Fit::Crop, 800, 600)
            ->format('webp');
    }

    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'store_name' => 'Mi Tienda',
            'whatsapp_number' => '',
        ]);
    }
}
