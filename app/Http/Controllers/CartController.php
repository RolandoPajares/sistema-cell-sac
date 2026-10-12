<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\ApplyCouponRequest;
use App\Http\Requests\UpdateCartRequest;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $cart = (array) $request->session()->get('cart', []);
        $products = Product::query()->with('images')->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = collect($cart)->map(function (int|string $quantity, int|string $id) use ($products): ?array {
            $product = $products->get((int) $id);

            return $product ? [
                'product' => $product,
                'quantity' => (int) $quantity,
                'subtotal' => (float) $product->effective_price * (int) $quantity,
            ] : null;
        })->filter()->values();

        return view('commerce.cart', ['lines' => $lines, 'total' => $lines->sum('subtotal')]);
    }

    public function add(AddToCartRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->activo, 404);
        $quantity = (int) ($request->validated('cantidad') ?? 1);
        abort_if($quantity > $product->existencias, 422, 'Stock insuficiente.');

        $cart = (array) $request->session()->get('cart', []);
        $cart[$product->id] = min((int) $product->existencias, (int) ($cart[$product->id] ?? 0) + $quantity);
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Producto agregado al carrito.');
    }

    public function update(UpdateCartRequest $request, Product $product): RedirectResponse
    {
        $quantity = (int) $request->validated('cantidad');
        $cart = (array) $request->session()->get('cart', []);

        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            abort_if($quantity > $product->existencias, 422, 'Stock insuficiente.');
            $cart[$product->id] = $quantity;
        }

        $request->session()->put('cart', $cart);

        return back();
    }

    public function remove(Request $request, Product $product): RedirectResponse
    {
        $cart = (array) $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Producto retirado del carrito.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(['cart', 'coupon_code']);

        return back()->with('status', 'Carrito vaciado.');
    }

    public function updateLegacy(Request $request): RedirectResponse
    {
        $submitted = (array) $request->input('cart', $request->input('items', []));
        $cart = [];

        foreach ($submitted as $id => $rawQuantity) {
            $product = Product::query()->active()->find((int) $id);
            $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);

            if (! $product || $quantity === false || $quantity < 1) {
                continue;
            }

            $cart[$product->id] = min($quantity, (int) $product->existencias);
        }

        $request->session()->put('cart', array_filter($cart));

        return back()->with('status', 'Carrito actualizado.');
    }

    public function applyCoupon(ApplyCouponRequest $request): RedirectResponse
    {
        $code = mb_strtoupper($request->string('codigo')->trim()->toString());
        $coupon = Coupon::query()->where('codigo', $code)->where('activo', true)
            ->where('inicia_en', '<=', now())->where('finaliza_en', '>=', now())->first();

        abort_unless($coupon && ($coupon->limite_usos === null || $coupon->usos < $coupon->limite_usos), 422, 'Cupón no válido o agotado.');
        $request->session()->put('coupon_code', $coupon->codigo);

        return back()->with('status', 'Cupón guardado. El descuento se validará nuevamente al confirmar el pedido.');
    }
}
