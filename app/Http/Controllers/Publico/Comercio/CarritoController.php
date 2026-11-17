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
 * Define la responsabilidad de la clase CarritoController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CarritoController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly ServicioInventario $inventario,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioCarrito $carritoService,
    ) {}

    /**
     * Construye la vista del carrito con productos activos, variantes, stock y subtotales.
     *
     * Normaliza primero el contenido de la sesión y consulta solo los productos presentes
     * en ella. Las líneas cuyo producto ya no está disponible se excluyen de la vista.
     *
     * @param  Request  $request  Solicitud cuyo usuario y sesión contienen el carrito.
     * @return View Vista pública del carrito con líneas y total calculado.
     */
    public function index(Request $request): View
    {
        // Guarda en $cart el resultado necesario para continuar esta operacion.
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        // Prepara en $products la consulta que obtiene o modifica los registros necesarios.
        $products = Producto::query()->with('images', 'variants')->active()->whereIn('id', array_keys($cart))->get()->keyBy('id');
        // Guarda en $lines el resultado necesario para continuar esta operacion.
        $lines = collect($cart)->map(function (array $linea, int $id) use ($products): ?array {
            // Guarda en $product el resultado necesario para continuar esta operacion.
            $product = $products->get((int) $id);
            // Guarda en $varianteId el resultado necesario para continuar esta operacion.
            $varianteId = $linea['variante_id'];
            $variante = $varianteId === null ? null : $product?->variants->firstWhere('id', $varianteId);
            // Guarda en $disponible el resultado necesario para continuar esta operacion.
            $disponible = $product ? $this->inventario->disponibilidadPublica($product, $varianteId) : 0;

            return $product ? [
                'product' => $product,
                'variant' => $variante,
                'quantity' => $linea['cantidad'],
                'disponible' => $disponible,
                'subtotal' => (float) $product->effective_price * $linea['cantidad'],
            ] : null;
        })->filter()->values();

        // Devuelve la vista 'publico.comercio.carrito' con los datos preparados por este controlador.
        return view('publico.comercio.carrito', ['lines' => $lines, 'total' => $lines->sum('subtotal')]);
    }

    /**
     * Valida disponibilidad y agrega un producto o variante a la sesión del carrito.
     *
     * Exige variante cuando el producto la ofrece, valida que pertenezca al producto,
     * compara la cantidad acumulada contra el inventario disponible y redirige al checkout
     * cuando se solicita la opción de compra inmediata.
     *
     * @param  AgregarCarritoRequest  $request  Datos validados de cantidad, variante y compra inmediata.
     * @param  Producto  $product  Producto activo que se agregar—.
     * @return RedirectResponse Redirección al checkout o a la página anterior con el estado de la operación.
     *
     * @throws HttpException Si el producto, variante o stock no es válido.
     */
    public function add(AgregarCarritoRequest $request, Producto $product): RedirectResponse
    {
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($product->activo, 404);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $quantity = (int) ($request->validated('cantidad') ?? 1);
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $variantId = $request->validated('variante_id');
        $variantId = $variantId === null ? null : (int) $variantId;
        // Guarda en $hasVariants el resultado necesario para continuar esta operacion.
        $hasVariants = $product->variants()->exists();
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($hasVariants && $variantId === null, 422, 'Selecciona una variante.');
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if(! $hasVariants && $variantId !== null, 422, 'Esta combinación no está disponible.');
        // Comprueba la condicion $variantId !== null antes de ejecutar este bloque.
        if ($variantId !== null) {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($product->variants()->whereKey($variantId)->where('estado', 'activo')->exists(), 422, 'Esta combinación no está disponible.');
        }
        // Guarda en $disponible el resultado necesario para continuar esta operacion.
        $disponible = $this->inventario->disponibilidadPublica($product, $variantId);
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($disponible < $quantity, 422, 'La cantidad supera el stock disponible.');

        // Guarda en $cart el resultado necesario para continuar esta operacion.
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        // Guarda en $existente el resultado necesario para continuar esta operacion.
        $existente = $cart[$product->id] ?? null;
        $nuevaCantidad = $existente !== null && $existente['variante_id'] === $variantId
            ? $existente['cantidad'] + $quantity
            : $quantity;
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_if($nuevaCantidad > $disponible, 422, 'La cantidad supera el stock disponible.');
        $cart[$product->id] = ['cantidad' => $nuevaCantidad, 'variante_id' => $variantId];
        $request->session()->put('cart', $cart);

        // Comprueba la condicion $request->boolean('comprar_ahora') antes de ejecutar este bloque.
        if ($request->boolean('comprar_ahora')) {
            // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
            return redirect()->route('checkout.form');
        }

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Producto agregado al carrito.');
    }

    /**
     * Cambia o elimina la cantidad de un producto que ya está en el carrito.
     *
     * La cantidad cero elimina la línea; una cantidad positiva exige que la línea exista
     * y no supere la disponibilidad calculada para su variante.
     *
     * @param  ActualizarCarritoRequest  $request  Cantidad validada por el formulario.
     * @param  Producto  $product  Producto cuya línea se actualiza.
     * @return RedirectResponse Redirección a la página anterior con la sesión actualizada.
     *
     * @throws HttpException Si la línea no existe o supera el stock.
     */
    public function update(ActualizarCarritoRequest $request, Producto $product): RedirectResponse
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $quantity = (int) $request->validated('cantidad');
        // Guarda en $cart el resultado necesario para continuar esta operacion.
        $cart = $this->carritoService->normalizar((array) $request->session()->get('cart', []));
        // Guarda en $linea el resultado necesario para continuar esta operacion.
        $linea = $cart[$product->id] ?? null;

        // Comprueba la condicion $quantity === 0 antes de ejecutar este bloque.
        if ($quantity === 0) {
            unset($cart[$product->id]);
        } else {
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_unless($linea !== null, 404);
            // Guarda en $disponible el resultado necesario para continuar esta operacion.
            $disponible = $this->inventario->disponibilidadPublica($product, $linea['variante_id']);
            // Detiene la solicitud si se cumple la validacion de seguridad indicada.
            abort_if($quantity > $disponible, 422, 'La cantidad supera el stock disponible.');
            $cart[$product->id]['cantidad'] = $quantity;
        }

        $request->session()->put('cart', $cart);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back();
    }

    /**
     * Modifica el estado o elimina el registro según la operación solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function remove(Request $request, Producto $product): RedirectResponse
    {
        // Guarda en $cart el resultado necesario para continuar esta operacion.
        $cart = (array) $request->session()->get('cart', []);
        unset($cart[$product->id]);
        $request->session()->put('cart', $cart);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Producto retirado del carrito.');
    }

    /**
     * Ejecuta la operación clear definida por la clase CarritoController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(['cart', 'coupon_code']);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Carrito vaciado.');
    }

    /**
     * Actualiza la información existente con los datos recibidos.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function updateLegacy(Request $request): RedirectResponse
    {
        // Guarda en $submitted el resultado necesario para continuar esta operacion.
        $submitted = (array) $request->input('cart', $request->input('items', []));
        // Guarda en $cart el resultado necesario para continuar esta operacion.
        $cart = $this->carritoService->normalizar($submitted);

        // Recorre $cart as $id => $linea y procesa cada elemento de la coleccion.
        foreach ($cart as $id => $linea) {
            // Guarda en $quantity el resultado necesario para continuar esta operacion.
            $quantity = $linea['cantidad'];
            // Prepara en $product la consulta que obtiene o modifica los registros necesarios.
            $product = Producto::query()->find((int) $id);

            // Comprueba la condicion ! $product || $quantity < 1 antes de ejecutar este bloque.
            if (! $product || $quantity < 1) {
                continue;
            }

            // Guarda en $disponible el resultado necesario para continuar esta operacion.
            $disponible = $this->inventario->disponibilidadPublica($product, $linea['variante_id']);
            $cart[(int) $id]['cantidad'] = min($quantity, $disponible);
        }

        $request->session()->put('cart', array_filter($cart, fn (array $linea): bool => $linea['cantidad'] > 0));

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Carrito actualizado.');
    }

    /**
     * Realiza la operación específica definida por este método y devuelve su resultado.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return RedirectResponse Resultado de la operación.
     */
    public function applyCoupon(AplicarCuponRequest $request): RedirectResponse
    {
        // Guarda en $code el resultado necesario para continuar esta operacion.
        $code = mb_strtoupper($request->string('codigo')->trim()->toString());
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless(Schema::hasColumn('cupones', 'empresa_id'), 503,
            'Los cupones no están disponibles hasta configurar su empresa propietaria.');
        $ubicacion = $request->user() !== null
            ? $this->autorizacion->operationLocation($request->user())
            : ['empresa_id' => $this->autorizacion->activeCompanyId()];
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($ubicacion['empresa_id'] !== null, 503, 'No hay una empresa autorizada para validar el cupón.');
        // Prepara en $coupon la consulta que obtiene o modifica los registros necesarios.
        $coupon = Cupon::query()->where('empresa_id', $ubicacion['empresa_id'])->where('codigo', $code)->where('activo', true)
            ->where('inicia_en', '<=', now())->where('finaliza_en', '>=', now())->first();

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($coupon && ($coupon->limite_usos === null || $coupon->usos < $coupon->limite_usos), 422, 'Cupón no válido o agotado.');
        $request->session()->put('coupon_code', $coupon->codigo);

        // Redirige al usuario usando la respuesta indicada en la instruccion siguiente.
        return back()->with('status', 'Cupón guardado. El descuento se validará nuevamente al confirmar el pedido.');
    }
}
