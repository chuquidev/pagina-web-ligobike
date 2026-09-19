<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Image\Enums\Fit;

class Product extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia;

    protected $fillable = [
        'sku',
        'category_id',
        'brand_id',
        'name',
        'slug',
        'description',
        'features',
        'price',
        'sale_price',
        'stock',
        'min_price',
        'availability',
        'is_featured',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'min_price' => 'decimal:2',
        'stock' => 'integer',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Solo productos con stock rastreado (importados/gestionados por inventario) se
        // sincronizan automáticamente. Productos con stock=null conservan el control
        // manual de disponibilidad que ya existía.
        static::saving(function (Product $product) {
            if ($product->stock === null) {
                return;
            }

            if ($product->stock <= 0) {
                $product->availability = 'out_of_stock';
            } elseif ((int) $product->getOriginal('stock') <= 0) {
                $product->availability = 'in_stock';
            }
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->fit(Fit::Crop, 400, 400)
            ->format('webp');

        $this->addMediaConversion('large')
            ->fit(Fit::Max, 1200, 1200)
            ->format('webp');
    }
}
