<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinalizarCompraRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioCarrito;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioPedidos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Define la responsabilidad de la clase FinalizarCompraController dentro de su mÛdulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class FinalizarCompraController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operaciÛn.
     */
    public function __construct(
        private readonly ServicioPedidos $orderService,
        private readonly ServicioInventario $inventario,
        private readonly ServicioCarrito $carritoService,
    ) {}

    /**
     * Prepara la pantalla de checkout a partir del carrito normalizado de la sesiÛn.
     *
     * Genera un token de checkout si falta, carga productos activos, calcula disponibilidad
     * y subtotales, y marca si todas las lÌneas pueden confirmarse sin modificar la base de datos.
     *
     * @param  Request  $request  Solicitud que contiene la sesiÛn y el usuario comprador.
     * @return View Vista de checkout con lÌneas, total, token y condiciÛn de confirmaciÛn.
     */
    public function index(Request $request): View
    {
        if (! is_string($request->session()->get('checkout_token'))) {
            $request->session()->put('checkout_token', bin2hex(random_bytes(32)));
        }
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        $products = Producto::query()->with('variants')->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = collect($cart)->map(function (array $linea, int $id) use ($products, $request): ?array {
            $product = $products->get((int) $id);
            $disponible = $product ? $this->inventario->disponibilidadDeLinea($request->user(), $product, $linea['variante_id']) : 0;

            if (! $product || $linea['cantidad'] < 1) {
                return null;
            }

            return [
                'product' => $product,
                'variant' => $linea['variante_id'] === null ? null : $product->variants->firstWhere('id', $linea['variante_id']),
                'quantity' => $linea['cantidad'],
                'disponible' => $disponible,
                'subtotal' => (float) $product->effective_price * $linea['cantidad'],
            ];
        })->filter()->values();
        $puedeConfirmar = $lines->isNotEmpty() && $lines->count() === count($cart)
            && $lines->every(fn (array $line): bool => $line['quantity'] <= $line['disponible']);

        return view('publico.comercio.finalizar-compra', [
            'lines' => $lines,
            'total' => $lines->sum('subtotal'),
            'checkoutToken' => $request->session()->get('checkout_token'),
            'puedeConfirmar' => $puedeConfirmar,
        ]);
    }

    /**
     * Convierte el carrito validado en un pedido mediante el servicio de pedidos.
     *
     * Rechaza un carrito vacÌo, delega la operaciÛn transaccional al servicio de checkout
     * con usuario, cupÛn, IP y token, y limpia las claves de sesiÛn solo despuÈs de recibir
     * el pedido creado.
     *
     * @param  FinalizarCompraRequest  $request  Datos validados del checkout.
     * @return RedirectResponse RedirecciÛn al detalle del pedido registrado.
     *
     * @throws HttpException Si la sesiÛn no contiene lÌneas de carrito.
     */
    public function store(FinalizarCompraRequest $request): RedirectResponse
    {
        $cart = (array) $request->session()->get('cart', []);
        abort_if($cart === [], 422, 'El carrito est√° vac√≠o.');

        $order = $this->orderService->checkout(
            $request->user(),
            $cart,
            $request->session()->get('coupon_code'),
            $request->ip(),
            $request->validated('checkout_token'),
        );

        $request->session()->forget(['cart', 'coupon_code', 'checkout_token']);

        return redirect()->route('orders.show', $order)->with('status', 'Pedido registrado. El sistema todav√≠a no procesa pagos.');
    }
}
