<?php

namespace App\Http\Controllers\Publico\Asistente;

use App\Http\Controllers\Controller;
use App\Http\Requests\AsistenteRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioInventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AsistenteController extends Controller
{
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioInventario $inventario,
    ) {}

    public function index(): View
    {
        return view('publico.asistente.index');
    }

    public function ask(AsistenteRequest $request): JsonResponse|RedirectResponse
    {
        $query = Str::lower(Str::ascii($request->validated('consulta')));
        $user = $request->user();

        if (str_contains($query, 'stock') || str_contains($query, 'inventario')) {
            if ($user !== null && $this->autorizacion->allows($user, 'gestion.inventario.consultar')) {
                $alcance = $this->inventario->alcanceConsulta($user);
                $items = $this->inventario->productosParaConsulta($alcance, ['estado' => 'sin_existencias'])
                    ->orderBy('existencias_alcance')->limit(8)->get();
                $answer = $items->isEmpty()
                    ? 'No hay productos activos con stock igual o menor a cinco unidades.'
                    : 'Productos con stock bajo: '.$items->map(fn (Producto $product): string => $product->nombre.' ('.$product->existencias_alcance.')')->join(', ').'.';
            } else {
                $answer = 'Consulta la disponibilidad pÃºblica en el catÃ¡logo; el detalle interno de inventario no estÃ¡ habilitado para este perfil.';
            }
        } elseif (str_contains($query, 'pedido')) {
            if ($user === null) {
                $answer = 'Inicia sesion para consultar tus pedidos.';
            } elseif ($this->autorizacion->allows($user, 'gestion.pedidos.consultar')) {
                $pendientes = $this->autorizacion->scopeRecordsFor(Pedido::query(), $user, 'gestion.pedidos.consultar', 'usuario_id')
                    ->where('estado', 'Pendiente')->count();
                $answer = 'Hay '.$pendientes.' '.($pendientes === 1 ? 'pedido pendiente' : 'pedidos pendientes').' en tu alcance autorizado.';
            } else {
                $answer = 'Este perfil no tiene habilitado el resumen interno de pedidos.';
            }
        } elseif (str_contains($query, 'venta')) {
            if ($user !== null && $this->autorizacion->allows($user, 'gestion.ventas.consultar')) {
                $totales = $this->autorizacion->scopeRecordsFor(Venta::query(), $user, 'gestion.ventas.consultar')
                    ->where('estado', 'confirmada')->whereMonth('fecha_venta', now()->month)->whereYear('fecha_venta', now()->year)
                    ->select('moneda')->selectRaw('SUM(total) AS importe')->groupBy('moneda')->orderBy('moneda')->get();
                $answer = $totales->isEmpty() ? 'No hay ventas confirmadas en tu alcance este mes.'
                    : 'Ventas confirmadas del mes en tu alcance: '.$totales->map(fn (Venta $venta): string => $venta->moneda.' '.number_format((float) $venta->importe, 2))->join('; ').'.';
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
}
