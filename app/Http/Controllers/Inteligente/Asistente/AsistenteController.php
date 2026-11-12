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
 * Define la responsabilidad de la clase AsistenteController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class AsistenteController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioInventario $inventario,
    ) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @return View Resultado de la operación.
     */
    public function index(): View
    {
        // Devuelve la vista 'inteligente.asistente.panel' con los datos preparados por este controlador.
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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $query = Str::lower(Str::ascii($request->validated('consulta')));
        // Obtiene en $user la informacion de la solicitud o del usuario actual.
        $user = $request->user();

        // Comprueba la condicion str_contains($query, 'stock') || str_contains($query, 'inventario') antes de ejecutar este bloque.
        if (str_contains($query, 'stock') || str_contains($query, 'inventario')) {
            // Comprueba la condicion $user !== null && $this->autorizacion->allows($user, 'gestion.inventario.consultar') antes de ejecutar este bloque.
            if ($user !== null && $this->autorizacion->allows($user, 'gestion.inventario.consultar')) {
                // Guarda en $alcance el resultado necesario para continuar esta operacion.
                $alcance = $this->inventario->alcanceConsulta($user);
                $items = $this->inventario->productosParaConsulta($alcance, ['estado' => 'sin_existencias'])
                    ->orderBy('existencias_alcance')->limit(8)->get();
                // Guarda en $answer el resultado necesario para continuar esta operacion.
                $answer = $items->isEmpty()
                    ? 'No hay productos activos con stock igual o menor a cinco unidades.'
                    : 'Productos con stock bajo: '.$items->map(fn (Producto $product): string => $product->nombre.' ('.$product->existencias_alcance.')')->join(', ').'.';
            } else {
                // Guarda en $answer el resultado necesario para continuar esta operacion.
                $answer = 'Consulta la disponibilidad pública en el catálogo; el detalle interno de inventario no está habilitado para este perfil.';
            }
        } elseif (str_contains($query, 'pedido')) {
            // Comprueba la condicion $user === null antes de ejecutar este bloque.
            if ($user === null) {
                // Guarda en $answer el resultado necesario para continuar esta operacion.
                $answer = 'Inicia sesion para consultar tus pedidos.';
            } elseif ($this->autorizacion->allows($user, 'gestion.pedidos.consultar')) {
                // Prepara en $pendientes la consulta que obtiene o modifica los registros necesarios.
                $pendientes = $this->autorizacion->scopeRecordsFor(Pedido::query(), $user, 'gestion.pedidos.consultar', 'usuario_id')
                    ->where('estado', 'Pendiente')->count();
                $answer = 'Hay '.$pendientes.' '.($pendientes === 1 ? 'pedido pendiente' : 'pedidos pendientes').' en tu alcance autorizado.';
            } else {
                // Guarda en $answer el resultado necesario para continuar esta operacion.
                $answer = 'Este perfil no tiene habilitado el resumen interno de pedidos.';
            }
        } elseif (str_contains($query, 'venta')) {
            // Comprueba la condicion $user !== null && $this->autorizacion->allows($user, 'gestion.ventas.consultar') antes de ejecutar este bloque.
            if ($user !== null && $this->autorizacion->allows($user, 'gestion.ventas.consultar')) {
                // Prepara en $totales la consulta que obtiene o modifica los registros necesarios.
                $totales = $this->autorizacion->scopeRecordsFor(Venta::query(), $user, 'gestion.ventas.consultar')
                    ->where('estado', 'confirmada')->whereMonth('fecha_venta', now()->month)->whereYear('fecha_venta', now()->year)
                    ->select('moneda')->selectRaw('SUM(total) AS importe')->groupBy('moneda')->orderBy('moneda')->get();
                // Guarda en $answer el resultado necesario para continuar esta operacion.
                $answer = $totales->isEmpty() ? 'No hay ventas confirmadas en tu alcance este mes.'
                    : 'Ventas confirmadas del mes en tu alcance: '.$totales->map(fn (Venta $venta): string => $venta->moneda.' '.number_format((float) $venta->importe, 2))->join('; ').'.';
            } else {
                // Guarda en $answer el resultado necesario para continuar esta operacion.
                $answer = 'Este perfil no tiene habilitado el resumen interno de ventas.';
            }
        } elseif (str_contains($query, 'producto')) {
            // Prepara en $answer la consulta que obtiene o modifica los registros necesarios.
            $answer = 'Hay '.Producto::query()->active()->count().' productos activos registrados en el catálogo.';
        } else {
            // Guarda en $answer el resultado necesario para continuar esta operacion.
            $answer = 'Puedo consultar información registrada sobre stock, pedidos, ventas por pedidos y productos. No utilizo un servicio externo de inteligencia artificial.';
        }

        // Comprueba la condicion $request->expectsJson() antes de ejecutar este bloque.
        if ($request->expectsJson()) {
            // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
            return response()->json([
                'ok' => true,
                'respuesta' => [
                    'mensaje' => $answer,
                    'productos' => [],
                ],
            ]);
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('assistant_answer', $answer);
    }
}
