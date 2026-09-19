<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\MaintenanceAppointment;
use App\Models\Product;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $byAvailability = Product::selectRaw('availability, count(*) as count')
            ->groupBy('availability')
            ->pluck('count', 'availability');

        $byCategory = Category::withCount('products')
            ->orderByDesc('products_count')
            ->get(['id', 'name'])
            ->map(fn($c) => ['name' => $c->name, 'count' => $c->products_count]);

        $recentProducts = Product::with('category')
            ->latest()
            ->take(5)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'category' => $p->category->name,
                'price' => $p->price,
                'is_active' => $p->is_active,
                'created_at' => $p->created_at->diffForHumans(),
                'thumb' => $p->getFirstMediaUrl('images', 'thumb') ?: null,
            ]);

        // Solo productos con stock rastreado (importados/gestionados por inventario).
        $lowStockProducts = Product::whereNotNull('stock')
            ->where('stock', '>', 0)
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->take(5)
            ->get(['id', 'name', 'sku', 'stock'])
            ->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'stock' => $p->stock]);

        $today = Carbon::today();
        $weekAhead = $today->copy()->addDays(6);

        $appointmentsByDay = MaintenanceAppointment::where('status', '!=', 'cancelled')
            ->whereBetween('starts_at', [$today, $weekAhead->copy()->endOfDay()])
            ->selectRaw('DATE(starts_at) as day, count(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');

        $appointmentsNext7Days = collect(range(0, 6))->map(function ($i) use ($today, $appointmentsByDay) {
            $date = $today->copy()->addDays($i);
            return [
                'label' => $date->locale('es')->isoFormat('ddd D'),
                'count' => $appointmentsByDay[$date->toDateString()] ?? 0,
            ];
        });

        $nextAppointments = MaintenanceAppointment::with('service')
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at')
            ->take(5)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'customer_name' => $a->customer_name,
                'service' => $a->service->name,
                'starts_at' => $a->starts_at->locale('es')->isoFormat('ddd D MMM, h:mm a'),
                'status' => $a->status,
            ]);

        return response()->json([
            'total_products' => Product::count(),
            'total_categories' => Category::count(),
            'total_brands' => Brand::count(),
            'featured_products' => Product::where('is_featured', true)->count(),
            'active_products' => Product::where('is_active', true)->count(),
            'inactive_products' => Product::where('is_active', false)->count(),
            'products_with_offers' => Product::whereNotNull('sale_price')->count(),
            'products_without_images' => Product::doesntHave('media')->count(),
            'availability_breakdown' => [
                'in_stock' => $byAvailability['in_stock'] ?? 0,
                'out_of_stock' => $byAvailability['out_of_stock'] ?? 0,
                'on_request' => $byAvailability['on_request'] ?? 0,
            ],
            'products_by_category' => $byCategory,
            'recent_products' => $recentProducts,
            'low_stock_products' => $lowStockProducts,
            'appointments_next_7_days' => $appointmentsNext7Days,
            'upcoming_appointments_count' => MaintenanceAppointment::where('status', '!=', 'cancelled')
                ->where('starts_at', '>=', now())
                ->count(),
            'next_appointments' => $nextAppointments,
        ]);
    }
}
