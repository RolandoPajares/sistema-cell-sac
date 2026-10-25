<?php

namespace App\Http\Controllers\Publico\Asistente;

use App\Http\Controllers\Controller;
use App\Http\Requests\AsistenteRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AsistenteController extends Controller
{
    /** @var list<string> */
    private const INVENTORY_ROLES = ['administrador', 'compras_logistica', 'ventas_minoristas'];

    /** @var list<string> */
    private const SALES_ROLES = ['administrador', 'ventas_mayoristas', 'ventas_minoristas'];

    public function index(): View
    {
        return view('publico.asistente.index');
    }

    public function ask(AsistenteRequest $request): JsonResponse|RedirectResponse
    {
        $query = Str::lower(Str::ascii($request->validated('consulta')));
        $user = $request->user();
        $role = $user?->rol;

        if (str_contains($query, 'stock') || str_contains($query, 'inventario')) {
            if (in_array($role, self::INVENTORY_ROLES, true)) {
                $items = Producto::query()->active()->where('existencias', '<=', 5)->orderBy('existencias')->limit(8)->get(['nombre', 'existencias']);
                $answer = $items->isEmpty()
                    ? 'No hay productos activos con stock igual o menor a cinco unidades.'
                    : 'Productos con stock bajo: '.$items->map(fn (Producto $product): string => $product->nombre.' ('.$product->existencias.')')->join(', ').'.';
            } else {
                $answer = 'Consulta la disponibilidad pÃºblica en el catÃ¡logo; el detalle interno de inventario no estÃ¡ habilitado para este perfil.';
            }
        } elseif (str_contains($query, 'pedido')) {
            if (! $user) {
                $answer = 'Inicia sesion para consultar tus pedidos.';
            } elseif ($this->isCustomerRole($role)) {
                $answer = 'Hay '.Pedido::query()
                    ->where('estado', 'Pendiente')
                    ->whereBelongsTo($user, 'user')
                    ->count().' pedidos pendientes en el alcance de tu cuenta.';
            } elseif (in_array($role, self::SALES_ROLES, true)) {
                $answer = 'Hay '.Pedido::query()->where('estado', 'Pendiente')->count().' pedidos pendientes en el alcance autorizado.';
            } else {
                $answer = 'Este perfil no tiene habilitado el resumen interno de pedidos.';
            }
        } elseif (str_contains($query, 'venta')) {
            if (! $user) {
                $answer = 'Inicia sesion para consultar el resumen de tus pedidos.';
            } elseif ($this->isCustomerRole($role)) {
                $answer = 'El importe de tus pedidos no cancelados de este mes suma S/ '.number_format($this->customerSalesTotal($user), 2).'. Esto representa pedidos, no pagos confirmados.';
            } elseif (in_array($role, self::SALES_ROLES, true)) {
                $answer = 'El importe de pedidos no cancelados de este mes suma S/ '.number_format($this->internalSalesTotal(), 2).'. Esto representa pedidos, no pagos confirmados.';
            } else {
                $answer = 'Este perfil no tiene habilitado el resumen interno de ventas.';
            }
        } elseif (str_contains($query, 'producto')) {
            $answer = 'Hay '.Producto::query()->active()->count().' productos activos registrados en el catÃ¡logo.';
        } else {
            $answer = 'Puedo consultar informaciÃ³n registrada sobre stock, pedidos, ventas por pedidos y productos. No utilizo un servicio externo de inteligencia artificial.';
        }

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'respuesta' => [
                    'mensaje' => $answer,
                    'productos' => [],
                ],
            ]);
        }

        return back()->with('assistant_answer', $answer);
    }

    private function isCustomerRole(?string $role): bool
    {
        return str_starts_with((string) $role, 'cliente_');
    }

    private function customerSalesTotal(User $user): float
    {
        return (float) Pedido::query()
            ->whereBelongsTo($user, 'user')
            ->whereNotIn('estado', ['Cancelado', 'Cancelada'])
            ->whereMonth('creado_en', now()->month)
            ->whereYear('creado_en', now()->year)
            ->sum('total');
    }

    private function internalSalesTotal(): float
    {
        return (float) Pedido::query()
            ->whereNotIn('estado', ['Cancelado', 'Cancelada'])
            ->whereMonth('creado_en', now()->month)
            ->whereYear('creado_en', now()->year)
            ->sum('total');
    }
}
