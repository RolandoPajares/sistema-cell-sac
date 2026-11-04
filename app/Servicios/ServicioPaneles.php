<?php

namespace App\Servicios;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Proveedor;
use App\Models\User;
use Illuminate\Support\Collection;

final class ServicioPaneles
{
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioMenuDinamico $menus,
    ) {}

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
    public function roleDashboard(User $user, array $cart, string $role): array
    {
        $orders = $this->customerOrders($user);
        $menu = $this->menus->paraUsuario($user);
        $links = collect($menu['grupos'])->flatMap(fn (array $group): array => $group['items'])
            ->filter(fn (array $item): bool => $item['enabled'])
            ->map(fn (array $item): array => $item + ['module' => null])
            ->values()->all();

        return [
            'role' => $role,
            'links' => $links,
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
        if (! $this->autorizacion->hasRole($user, 'cliente_minorista')
            && ! $this->autorizacion->hasRole($user, 'cliente_mayorista')) {
            return collect();
        }

        return $this->autorizacion->scopeRecordsFor(
            Pedido::query()->where('usuario_id', $user->getKey()),
            $user,
            'gestion.pedidos.consultar',
            'usuario_id',
        )
            ->with('details.product')
            ->latest('creado_en')
            ->latest('id')
            ->get();
    }
}
