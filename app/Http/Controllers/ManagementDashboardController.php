<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\View\View;

class ManagementDashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('management.dashboard', [
            'products' => Product::count(),
            'lowStock' => Product::where('existencias', '<=', 5)->count(),
            'orders' => Order::count(),
            'suppliers' => Supplier::count(),
        ]);
    }
}
