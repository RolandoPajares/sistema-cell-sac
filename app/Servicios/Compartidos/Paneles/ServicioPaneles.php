<?php

namespace App\Servicios\Compartidos\Paneles;

use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\Compras\Proveedor;
use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Compartidos\Seguridad\ServicioMenuDinamico;
use Illuminate\Support\Collection;

/**
 * Define la responsabilidad de la clase ServicioPaneles dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioPaneles
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
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
     * @return array{dashboardKind:string, dashboardTitle:string, dashboardBodyClass:string, userName:string, links:array<int, array{label:string, route:string, icon:string, module:?string}>, orders:Collection<int, Pedido>, orderCount:int, pendingCount:int, cartUnits:int|float}
     */
    public function roleDashboard(User $user, array $cart): array
    {
        $orders = $this->customerOrders($user);
        $menu = $this->menus->paraUsuario($user);
        $links = collect($menu['grupos'])->flatMap(fn (array $group): array => $group['items'])
            ->filter(fn (array $item): bool => $item['enabled'])
            ->map(fn (array $item): array => $item + ['module' => null])
            ->values()->all();

        return [
            'dashboardKind' => 'account',
            'dashboardTitle' => 'Panel de cuenta',
            'dashboardBodyClass' => 'modulo-cuenta',
            'userName' => (string) $user->nombre,
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
        if (! $this->autorizacion->allows($user, 'gestion.pedidos.consultar')) {
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
