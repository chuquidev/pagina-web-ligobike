<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            });

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->string('category')->value()));
        }

        if ($request->filled('brand')) {
            $query->whereHas('brand', fn($q) => $q->where('slug', $request->string('brand')->value()));
        }

        if ($request->filled('search')) {
            $term = $request->string('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'ilike', '%' . $term . '%')
                    ->orWhereHas('category', fn($c) => $c->where('name', 'ilike', '%' . $term . '%'))
                    ->orWhereHas('brand', fn($b) => $b->where('name', 'ilike', '%' . $term . '%'));
            });
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        match ($request->string('sort', 'newest')->value()) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'name' => $query->orderBy('name', 'asc'),
            default => $query->latest(),
        };

        return ProductResource::collection($query->paginate(20));
    }

    public function show(string $slug)
    {
        $product = Product::where('slug', $slug)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('stock')->orWhere('stock', '>', 0);
            })
            ->with('category')
            ->firstOrFail();

        return new ProductResource($product);
    }
}
