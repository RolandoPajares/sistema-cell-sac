<?php

namespace App\Http\Controllers\Publico\Comercio;

use App\Http\Controllers\Controller;
use App\Http\Requests\Publico\Comercio\ActualizarCarritoRequest;
use App\Http\Requests\Publico\Comercio\AgregarCarritoRequest;
use App\Http\Requests\Publico\Comercio\AplicarCuponRequest;
use App\Models\Catalogo\Producto;
use App\Models\Comercio\Cupon;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Gestion\InventarioLogistica\ServicioInventario;
use App\Servicios\Publico\Comercio\ServicioCarrito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Define la responsabilidad de la clase CarritoController dentro de su m�dulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CarritoController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operaci�n.
     */
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioCarrito $carritoService,
    ) {}

    /**
     * Construye la vista del carrito con productos activos, variantes, stock y subtotales.
     *
     * Normaliza primero el contenido de la sesi�n y consulta solo los productos presentes
     * en ella. Las l�neas cuyo producto ya no est� disponible se excluyen de la vista.
     *
     * @param  Request  $request  Solicitud cuyo usuario y sesi�n contienen el carrito.
     * @return View Vista p�blica del carrito con l�neas y total calculado.
     */
    public function index(Request $request): View
    {
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        $products = Producto::query()->with('images', 'variants')->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        $lines = collect($cart)->map(function (array $linea, int $id) use ($products): ?array {
            $product = $products->get((int) $id);
            $varianteId = $linea['variante_id'];
            $variante = $varianteId === null ? null : $product?->variants->firstWhere('id', $varianteId);
            $disponible = $product ? $this->inventario->disponibilidadPublica($product, $varianteId) : 0;

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

    /**
     * Valida disponibilidad y agrega un producto o variante a la sesi�n del carrito.
     *
     * Exige variante cuando el producto la ofrece, valida que pertenezca al producto,
     * compara la cantidad acumulada contra el inventario disponible y redirige al checkout
     * cuando se solicita la opci�n de compra inmediata.
     *
     * @param  AgregarCarritoRequest  $request  Datos validados de cantidad, variante y compra inmediata.
     * @param  Producto  $product  Producto activo que se agregar�.
     * @return RedirectResponse Redirecci�n al checkout o a la p�gina anterior con el estado de la operaci�n.
     *
     * @throws HttpException Si el producto, variante o stock no es v�lido.
     */
    public function add(AgregarCarritoRequest $request, Producto $product): RedirectResponse
    {
        abort_unless($product->activo, 404);
        $quantity = (int) ($request->validated('cantidad') ?? 1);
        $variantId = $request->validated('variante_id');
        $variantId = $variantId === null ? null : (int) $variantId;
        $hasVariants = $product->variants()->exists();
        abort_if($hasVariants && $variantId === null, 422, 'Selecciona una variante.');
        abort_if(! $hasVariants && $variantId !== null, 422, 'Esta combinaci�n no est� disponible.');
        if ($variantId !== null) {
            abort_unless($product->variants()->whereKey($variantId)->where('estado', 'activo')->exists(), 422, 'Esta combinaci�n no est� disponible.');
        }
        $disponible = $this->inventario->disponibilidadPublica($product, $variantId);
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

    /**
     * Cambia o elimina la cantidad de un producto que ya est� en el carrito.
     *
     * La cantidad cero elimina la l�nea; una cantidad positiva exige que la l�nea exista
     * y no supere la disponibilidad calculada para su variante.
     *
     * @param  ActualizarCarritoRequest  $request  Cantidad validada por el formulario.
     * @param  Producto  $product  Producto cuya l�nea se actualiza.
     * @return RedirectResponse Redirecci�n a la p�gina anterior con la sesi�n actualizada.
     *
     * @throws HttpException Si la l�nea no existe o supera el stock.
     */
    public function update(ActualizarCarritoRequest $request, Producto $product): RedirectResponse
    {
        $quantity = (int) $request->validated('cantidad');
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        $linea = $cart[$product->id] ?? null;

        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            abort_unless($linea !== null, 404);
            $disponible = $this->inventario->disponibilidadPublica($product, $linea['variante_id']);
            abort_if($quantity > $disponible, 422, 'La cantidad supera el stock disponible.');
            $cart[$product->id]['cantidad'] = $quantity;
        }

        $request->session()->put('cart', $cart);

        return back();
    }

    /**
     * Modifica el estado o elimina el registro seg�n la operaci�n solicitada.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @param  mixed  $product  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function remove(Request $request, Producto $product): RedirectResponse
    {
        $cart = (array) $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        return back()->with('status', 'Producto retirado del carrito.');
    }

    /**
     * Ejecuta la operaci�n clear definida por la clase CarritoController.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(['cart', 'coupon_code']);

        return back()->with('status', 'Carrito vaciado.');
    }

    /**
     * Actualiza la informaci�n existente con los datos recibidos.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
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

            $disponible = $this->inventario->disponibilidadPublica($product, $linea['variante_id']);
            $cart[(int) $id]['cantidad'] = min($quantity, $disponible);
        }

        $request->session()->put('cart', array_filter($cart, fn (array $linea): bool => $linea['cantidad'] > 0));

        return back()->with('status', 'Carrito actualizado.');
    }

    /**
     * Realiza la operaci�n espec�fica definida por este m�todo y devuelve su resultado.
     *
     * @param  mixed  $request  Par�metro recibido por la operaci�n.
     * @return RedirectResponse Resultado de la operaci�n.
     */
    public function applyCoupon(AplicarCuponRequest $request): RedirectResponse
    {
        $code = mb_strtoupper($request->string('codigo')->trim()->toString());
        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones no est�n disponibles hasta configurar su empresa propietaria.');
        $ubicacion = $request->user() !== null
            ? $this->autorizacion->operationLocation($request->user())
            : ['empresa_id' => $this->autorizacion->activeCompanyId()];
        abort_unless($ubicacion['empresa_id'] !== null, 503, 'No hay una empresa autorizada para validar el cup�n.');
        $coupon = Cupon::query()->where('empresa_id', $ubicacion['empresa_id'])->where('codigo', $code)->where('activo', true)
            ->where('inicia_en', '<=', now())->where('finaliza_en', '>=', now())->first();

        abort_unless($coupon && ($coupon->limite_usos === null || $coupon->usos < $coupon->limite_usos), 422, 'Cupón no válido o agotado.');
        $request->session()->put('coupon_code', $coupon->codigo);

        return back()->with('status', 'Cupón guardado. El descuento se validará nuevamente al confirmar el pedido.');
    }
}
