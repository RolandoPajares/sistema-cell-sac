<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssistantRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssistantController extends Controller
{
    public function index(): View
    {
        return view('assistant.index');
    }

    public function ask(AssistantRequest $request): JsonResponse|RedirectResponse
    {
        $query = Str::lower(Str::ascii($request->validated('consulta')));
        $user = $request->user();
        $role = $user?->rol;

        if (str_contains($query, 'stock') || str_contains($query, 'inventario')) {
            if (in_array($role, ['administrador', 'compras_logistica', 'ventas_minoristas'], true)) {
                $items = Product::query()->active()->where('existencias', '<=', 5)->orderBy('existencias')->limit(8)->get(['nombre', 'existencias']);
                $answer = $items->isEmpty()
                    ? 'No hay productos activos con stock igual o menor a cinco unidades.'
                    : 'Productos con stock bajo: '.$items->map(fn (Product $product): string => $product->nombre.' ('.$product->existencias.')')->join(', ').'.';
            } else {
                $answer = 'Consulta la disponibilidad pública en el catálogo; el detalle interno de inventario no está habilitado para este perfil.';
            }
        } elseif (str_contains($query, 'pedido')) {
            $ordersQuery = Order::query()->where('estado', 'Pendiente');
            if ($user && str_starts_with($role, 'cliente_')) {
                $ordersQuery->whereBelongsTo($user, 'user');
            }
            $answer = 'Hay '.$ordersQuery->count().' pedidos pendientes en el alcance de tu cuenta.';
        } elseif (str_contains($query, 'venta')) {
            $salesQuery = Order::query()->whereNotIn('estado', ['Cancelado', 'Cancelada'])->whereMonth('creado_en', now()->month)->whereYear('creado_en', now()->year);
            if ($user && str_starts_with($role, 'cliente_')) {
                $salesQuery->whereBelongsTo($user, 'user');
            }
            $answer = 'El importe de pedidos no cancelados de este mes suma S/ '.number_format((float) $salesQuery->sum('total'), 2).'. Esto representa pedidos, no pagos confirmados.';
        } elseif (str_contains($query, 'producto')) {
            $answer = 'Hay '.Product::query()->active()->count().' productos activos registrados en el catálogo.';
        } else {
            $answer = 'Puedo consultar información registrada sobre stock, pedidos, ventas por pedidos y productos. No utilizo un servicio externo de inteligencia artificial.';
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
}
