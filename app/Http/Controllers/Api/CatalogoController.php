<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compartidos\Catalogo\FiltroCatalogoRequest;
use App\Http\Resources\RecursoProducto;
use App\Models\Catalogo\Producto;
use App\Servicios\Compartidos\Catalogo\ServicioCatalogo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Define la responsabilidad de la clase CatalogoController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class CatalogoController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $catalogService  Parámetro recibido por la operación.
     * @return AnonymousResourceCollection Resultado de la operación.
     */
    public function index(FiltroCatalogoRequest $request, ServicioCatalogo $catalogService): AnonymousResourceCollection
    {
        $products = $catalogService
            ->query($request->validated())
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();
        $catalogService->agregarDisponibilidad($products->getCollection(), $request->user());

        return RecursoProducto::collection($products);
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $catalogService  Parámetro recibido por la operación.
     * @return RecursoProducto Resultado de la operación.
     */
    public function show(Request $request, Producto $product, ServicioCatalogo $catalogService): RecursoProducto
    {
        abort_unless($product->activo, 404);

        $product->load('category', 'images');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());

        return new RecursoProducto($product);
    }
}
