<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinalizarCompraRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioInventario;
use App\Servicios\ServicioPedidos;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinalizarCompraController extends Controller
{
    public function __construct(
        private readonly ServicioPedidos $orderService,
        private readonly ServicioInventario $inventario,
    ) {}

    public function index(Request $request): View
    {
        if (! is_string($request->session()->get('checkout_token'))) {
            $request->session()->put('checkout_token', bin2hex(random_bytes(32)));
        }
        $cart = (array) $request->session()->get('cart', []);
        $disponibilidad = $cart === [] ? [] : $this->inventario->disponibilidadParaOperacion($request->user(), array_keys($cart));
        $products = Producto::query()->active()->whereIn('id', array_keys($disponibilidad))->get()->keyBy('id');
        $lines = collect($cart)->map(function (int|string $quantity, int|string $id) use ($products, $disponibilidad): ?array {
            $product = $products->get((int) $id);

            if (! $product || (int) $quantity < 1) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => (int) $quantity,
                'disponible' => $disponibilidad[(int) $id],
                'subtotal' => (float) $product->effective_price * (int) $quantity,
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
