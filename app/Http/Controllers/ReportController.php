<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportFilterRequest;
use App\Models\Campaign;
use App\Models\InventoryMovement;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(ReportFilterRequest $request): View
    {
        $campaigns = Campaign::query()->selectRaw('COALESCE(SUM(vistas), 0) as views, COALESCE(SUM(clics), 0) as clicks')->first();
        $filters = $request->validated();
        $ordersQuery = Order::query()->whereNotIn('estado', ['Cancelado', 'Cancelada']);
        $movementsQuery = InventoryMovement::query()->with('product')->latest('creado_en');

        foreach (['desde' => '>=', 'hasta' => '<='] as $field => $operator) {
            if (! isset($filters[$field])) {
                continue;
            }

            $ordersQuery->whereDate('creado_en', $operator, $filters[$field]);
            $movementsQuery->whereDate('creado_en', $operator, $filters[$field]);
        }

        return view('management.reports', [
            'sales' => $ordersQuery->sum('total'),
            'orders' => (clone $ordersQuery)->count(),
            'products' => Product::count(),
            'lowStock' => Product::where('existencias', '<=', 5)->count(),
            'campaigns' => $campaigns ?? (object) ['views' => 0, 'clicks' => 0],
            'movements' => $movementsQuery->limit(20)->get(),
        ]);
    }
}
