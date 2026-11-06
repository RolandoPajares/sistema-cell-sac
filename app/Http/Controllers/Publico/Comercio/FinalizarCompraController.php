<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinalizarCompraRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioCarrito;
use App\Servicios\ServicioPedidos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinalizarCompraController extends Controller
{
    public function __construct(
        private readonly ServicioPedidos $orderService,
        private readonly ServicioInventario $inventario,
        private readonly ServicioCarrito $carritoService,
    ) {}

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

    public function store(FinalizarCompraRequest $request): RedirectResponse
    {
        $cart = (array) $request->session()->get('cart', []);
        abort_if($cart === [], 422, 'El carrito estÃ¡ vacÃ­o.');

        $order = $this->orderService->checkout(
            $request->user(),
            $cart,
            $request->session()->get('coupon_code'),
            $request->ip(),
            $request->validated('checkout_token'),
        );

        $request->session()->forget(['cart', 'coupon_code', 'checkout_token']);

        return redirect()->route('orders.show', $order)->with('status', 'Pedido registrado. El sistema todavÃ­a no procesa pagos.');
    }
}
