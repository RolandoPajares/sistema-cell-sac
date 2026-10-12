<?php

namespace App\Http\Controllers;

use App\Support\RolePermissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $isCustomer = str_starts_with($user->rol, 'cliente_');
        $orders = $isCustomer
            ? $user->orders()->with('details.product')->latest('creado_en')->latest('id')->get()
            : collect();

        return view('account.dashboard', [
            'role' => $user->rol,
            'links' => RolePermissions::navigation($user),
            'orders' => $orders,
            'orderCount' => $orders->count(),
            'pendingCount' => $orders->whereIn('estado', ['Pendiente', 'En proceso'])->count(),
            'cartUnits' => collect((array) $request->session()->get('cart', []))->sum(),
        ]);
    }
}
