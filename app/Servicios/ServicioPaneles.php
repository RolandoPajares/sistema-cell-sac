<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Proveedor;
use App\Models\User;
use App\Soporte\PermisosRoles;
use Illuminate\Support\Collection;

final class ServicioPaneles
{
    /**
     * @return array{products:int, lowStock:int, orders:int, suppliers:int}
     */
    public function administratorSummary(): array
    {
        return [
            'products' => Producto::count(),
            'lowStock' => Producto::where('existencias', '<=', 5)->count(),
            'orders' => Pedido::count(),
            'suppliers' => Proveedor::count(),
        ];
    }

    /**
     * @param  array<int|string, int|string>  $cart
     * @return array{role:string, links:array<int, array{label:string, route:string, icon:string, module:?string}>, orders:Collection<int, Pedido>, orderCount:int, pendingCount:int, cartUnits:int|float}
     */
    public function roleDashboard(User $user, array $cart): array
    {
        $orders = $this->customerOrders($user);

        return [
            'role' => $user->rol,
            'links' => PermisosRoles::navigation($user),
            'orders' => $orders,
            'orderCount' => $orders->count(),
            'pendingCount' => $orders->whereIn('estado', ['Pendiente', 'En proceso'])->count(),
            'cartUnits' => collect($cart)->sum(),
        ];
    }

    /**
     * @return Collection<int, Pedido>
     */
    private function customerOrders(User $user): Collection
    {
        if (! str_starts_with($user->rol, 'cliente_')) {
            return collect();
        }

        return $user->orders()
            ->with('details.product')
            ->latest('creado_en')
            ->latest('id')
            ->get();
    }
}
