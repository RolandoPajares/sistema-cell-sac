<?php

namespace App\Http\Controllers\Inteligente\Asistente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inteligente\Asistente\AsistenteRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Pedido;
use App\Models\Comercio\Venta;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase AsistenteController dentro de su m�dulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AsistenteController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operaci�n.
     */
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioInventario $inventario,
    ) {}

    /**
     * Obtiene y prepara la informaci�n solicitada para la operaci�n.
     *
     * @return View Resultado de la operaci�n.
     */
    public function index(): View
    {
        return view('inteligente.asistente.panel');
    }

    /**
     * Ejecuta la operación ask definida por la clase AsistenteController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return JsonResponse|RedirectResponse Resultado de la operación.
     */
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
                $answer = 'Consulta la disponibilidad pública en el catálogo; el detalle interno de inventario no está habilitado para este perfil.';
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
            $answer = 'Hay '.Producto::query()->active()->count().' productos activos registrados en el catálogo.';
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
