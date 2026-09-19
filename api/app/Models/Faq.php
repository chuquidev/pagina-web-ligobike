<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Faq extends Model
{
    protected $fillable = ['question', 'answer', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(fn() => Cache::forget('public.faqs'));
        static::deleted(fn() => Cache::forget('public.faqs'));
    }
}
