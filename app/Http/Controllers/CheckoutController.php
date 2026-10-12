<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private readonly OrderService $orderService) {}

    public function index(Request $request): View
    {
        $cart = (array) $request->session()->get('cart', []);
        $products = Product::query()->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = collect($cart)->map(function (int|string $quantity, int|string $id) use ($products): ?array {
            $product = $products->get((int) $id);

            if (! $product || (int) $quantity < 1) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => (int) $quantity,
                'subtotal' => (float) $product->effective_price * (int) $quantity,
            ];
        })->filter()->values();

        return view('commerce.checkout', [
            'lines' => $lines,
            'total' => $lines->sum('subtotal'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $cart = (array) $request->session()->get('cart', []);
        abort_if($cart === [], 422, 'El carrito está vacío.');

        $order = $this->orderService->checkout(
            $request->user(),
            $cart,
            $request->session()->get('coupon_code'),
            $request->ip(),
        );

        $request->session()->forget(['cart', 'coupon_code']);

        return redirect()->route('orders.show', $order)->with('status', 'Pedido registrado. El sistema todavía no procesa pagos.');
    }
}
