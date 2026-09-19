<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index()
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function () {
            $baseUrl = rtrim(config('app.frontend_url'), '/');
            $today = now()->toAtomString();

            $urls = collect([
                ['loc' => $baseUrl, 'priority' => '1.0', 'changefreq' => 'daily', 'lastmod' => $today],
                ['loc' => $baseUrl . '/catalogo', 'priority' => '0.9', 'changefreq' => 'daily', 'lastmod' => $today],
                ['loc' => $baseUrl . '/nosotros', 'priority' => '0.5', 'changefreq' => 'monthly', 'lastmod' => $today],
                ['loc' => $baseUrl . '/preguntas-frecuentes', 'priority' => '0.4', 'changefreq' => 'monthly', 'lastmod' => $today],
                ['loc' => $baseUrl . '/guia-tallas', 'priority' => '0.4', 'changefreq' => 'monthly', 'lastmod' => $today],
                ['loc' => $baseUrl . '/reservar-mantenimiento', 'priority' => '0.6', 'changefreq' => 'monthly', 'lastmod' => $today],
            ]);

            foreach (Category::where('is_active', true)->get() as $category) {
                $urls->push([
                    'loc' => $baseUrl . '/catalogo?category=' . $category->slug,
                    'priority' => '0.7',
                    'changefreq' => 'daily',
                    'lastmod' => $category->updated_at?->toAtomString() ?? $today,
                ]);
            }

            foreach (Product::where('is_active', true)->get() as $product) {
                $urls->push([
                    'loc' => $baseUrl . '/producto/' . $product->slug,
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at?->toAtomString() ?? $today,
                ]);
            }

            return view('sitemap', ['urls' => $urls->all()])->render();
        });

        return response($xml)->header('Content-Type', 'text/xml');
    }
}
