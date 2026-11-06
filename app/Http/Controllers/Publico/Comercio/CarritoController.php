<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCarritoRequest;
use App\Http\Requests\AgregarCarritoRequest;
use App\Http\Requests\AplicarCuponRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Cupon;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioCarrito;
use App\Servicios\ServicioInventario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CarritoController extends Controller
{
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioCarrito $carritoService,
    ) {}

    public function index(Request $request): View
    {
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        $products = Producto::query()->with('images', 'variants')->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = collect($cart)->map(function (array $linea, int $id) use ($products, $request): ?array {
            $product = $products->get((int) $id);
            $varianteId = $linea['variante_id'];
            $variante = $varianteId === null ? null : $product?->variants->firstWhere('id', $varianteId);
            $disponible = $product ? $this->inventario->disponibilidadDeLinea($request->user(), $product, $varianteId) : 0;

            return $product ? [
                'product' => $product,
                'variant' => $variante,
                'quantity' => $linea['cantidad'],
                'disponible' => $disponible,
                'subtotal' => (float) $product->effective_price * $linea['cantidad'],
            ] : null;
        })->filter()->values();

        return view('publico.comercio.carrito', ['lines' => $lines, 'total' => $lines->sum('subtotal')]);
    }

    public function add(AgregarCarritoRequest $request, Producto $product): RedirectResponse
    {
        abort_unless($product->activo, 404);
        $quantity = (int) ($request->validated('cantidad') ?? 1);
        $variantId = $request->validated('variante_id');
        $variantId = $variantId === null ? null : (int) $variantId;
        $hasVariants = $product->variants()->exists();
        abort_if($hasVariants && $variantId === null, 422, 'Selecciona una variante.');
        abort_if(! $hasVariants && $variantId !== null, 422, 'Esta combinación no está disponible.');
        if ($variantId !== null) {
            abort_unless($product->variants()->whereKey($variantId)->where('estado', 'activo')->exists(), 422, 'Esta combinación no está disponible.');
        }
        $disponible = $this->inventario->disponibilidadDeLinea($request->user(), $product, $variantId);
        abort_if($disponible < $quantity, 422, 'La cantidad supera el stock disponible.');

        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        $existente = $cart[$product->id] ?? null;
        $nuevaCantidad = $existente !== null && $existente['variante_id'] === $variantId
            ? $existente['cantidad'] + $quantity
            : $quantity;
        abort_if($nuevaCantidad > $disponible, 422, 'La cantidad supera el stock disponible.');
        $cart[$product->id] = ['cantidad' => $nuevaCantidad, 'variante_id' => $variantId];
        $request->session()->put('cart', $cart);

        if ($request->boolean('comprar_ahora')) {
            return redirect()->route('checkout.form');
        }

        return back()->with('status', 'Producto agregado al carrito.');
    }

    public function update(ActualizarCarritoRequest $request, Producto $product): RedirectResponse
    {
        $quantity = (int) $request->validated('cantidad');
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        $linea = $cart[$product->id] ?? null;

        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            abort_unless($linea !== null, 404);
            $disponible = $this->inventario->disponibilidadDeLinea($request->user(), $product, $linea['variante_id']);
            abort_if($quantity > $disponible, 422, 'La cantidad supera el stock disponible.');
            $cart[$product->id]['cantidad'] = $quantity;
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
        $cart = $this->carritoService->normalizar($submitted);

        foreach ($cart as $id => $linea) {
            $quantity = $linea['cantidad'];
            $product = Producto::query()->find((int) $id);

            if (! $product || $quantity < 1) {
                continue;
            }

            $disponible = $this->inventario->disponibilidadDeLinea($request->user(), $product, $linea['variante_id']);
            $cart[(int) $id]['cantidad'] = min($quantity, $disponible);
        }

        $request->session()->put('cart', array_filter($cart, fn (array $linea): bool => $linea['cantidad'] > 0));

        return back()->with('status', 'Carrito actualizado.');
    }

    public function applyCoupon(AplicarCuponRequest $request): RedirectResponse
    {
        $code = mb_strtoupper($request->string('codigo')->trim()->toString());
        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones no están disponibles hasta configurar su empresa propietaria.');
        $ubicacion = $this->autorizacion->operationLocation($request->user());
        abort_unless($ubicacion['empresa_id'] !== null, 503, 'No hay una empresa autorizada para validar el cupón.');
        $coupon = Cupon::query()->where('empresa_id', $ubicacion['empresa_id'])->where('codigo', $code)->where('activo', true)
            ->where('inicia_en', '<=', now())->where('finaliza_en', '>=', now())->first();

        abort_unless($coupon && ($coupon->limite_usos === null || $coupon->usos < $coupon->limite_usos), 422, 'CupÃ³n no vÃ¡lido o agotado.');
        $request->session()->put('coupon_code', $coupon->codigo);

        return back()->with('status', 'CupÃ³n guardado. El descuento se validarÃ¡ nuevamente al confirmar el pedido.');
    }
}
