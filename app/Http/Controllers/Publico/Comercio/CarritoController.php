<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCarritoRequest;
use App\Http\Requests\AgregarCarritoRequest;
use App\Http\Requests\AplicarCuponRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Cupon;
use App\Servicios\ServicioInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function __construct(private readonly ServicioInventario $inventario) {}

    public function index(Request $request): View
    {
        $cart = (array) $request->session()->get('cart', []);
        $disponibilidad = $cart === [] ? [] : $this->inventario->disponibilidadParaOperacion($request->user(), array_keys($cart));
        $products = Producto::query()->with('images')->active()->whereIn('id', array_keys($disponibilidad))->get()->keyBy('id');
        $lines = collect($cart)->map(function (int|string $quantity, int|string $id) use ($products, $disponibilidad): ?array {
            $product = $products->get((int) $id);

            return $product ? [
                'product' => $product,
                'quantity' => (int) $quantity,
                'disponible' => $disponibilidad[(int) $id],
                'subtotal' => (float) $product->effective_price * (int) $quantity,
            ] : null;
        })->filter()->values();

        return view('publico.comercio.carrito', ['lines' => $lines, 'total' => $lines->sum('subtotal')]);
    }

    public function add(AgregarCarritoRequest $request, Producto $product): RedirectResponse
    {
        abort_unless($product->activo, 404);
        $quantity = (int) ($request->validated('cantidad') ?? 1);
        $disponibilidad = $this->inventario->disponibilidadParaOperacion($request->user(), [$product->id]);
        abort_unless(array_key_exists($product->id, $disponibilidad), 404);

        $cart = (array) $request->session()->get('cart', []);
        $nuevaCantidad = (int) ($cart[$product->id] ?? 0) + $quantity;
        abort_if($nuevaCantidad > $disponibilidad[$product->id], 422, 'Stock insuficiente en el almacén autorizado o falta seleccionar el identificador del equipo.');
        $cart[$product->id] = $nuevaCantidad;
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Producto agregado al carrito.');
    }

    public function update(ActualizarCarritoRequest $request, Producto $product): RedirectResponse
    {
        $quantity = (int) $request->validated('cantidad');
        $cart = (array) $request->session()->get('cart', []);

        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            $disponibilidad = $this->inventario->disponibilidadParaOperacion($request->user(), [$product->id]);
            abort_unless(array_key_exists($product->id, $disponibilidad), 404);
            abort_if($quantity > $disponibilidad[$product->id], 422, 'Stock insuficiente en el almacén autorizado o falta seleccionar el identificador del equipo.');
            $cart[$product->id] = $quantity;
        }

        $request->session()->put('cart', $cart);

        return back();
    }

    public function remove(Request $request, Producto $product): RedirectResponse
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
        $disponibilidad = $submitted === [] ? [] : $this->inventario->disponibilidadParaOperacion($request->user(), array_keys($submitted));

        foreach ($submitted as $id => $rawQuantity) {
            $quantity = filter_var($rawQuantity, FILTER_VALIDATE_INT);

            if (! array_key_exists((int) $id, $disponibilidad) || $quantity === false || $quantity < 1) {
                continue;
            }

            $cart[(int) $id] = min($quantity, $disponibilidad[(int) $id]);
        }

        $request->session()->put('cart', array_filter($cart));

        return back()->with('status', 'Carrito actualizado.');
    }

    public function applyCoupon(AplicarCuponRequest $request): RedirectResponse
    {
        $code = mb_strtoupper($request->string('codigo')->trim()->toString());
        $coupon = Cupon::query()->where('codigo', $code)->where('activo', true)
            ->where('inicia_en', '<=', now())->where('finaliza_en', '>=', now())->first();

        abort_unless($coupon && ($coupon->limite_usos === null || $coupon->usos < $coupon->limite_usos), 422, 'CupÃ³n no vÃ¡lido o agotado.');
        $request->session()->put('coupon_code', $coupon->codigo);

        return back()->with('status', 'CupÃ³n guardado. El descuento se validarÃ¡ nuevamente al confirmar el pedido.');
    }
}
