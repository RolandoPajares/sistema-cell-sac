<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroCatalogoRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioCatalogo;
use Illuminate\Http\Request;
use Illuminate\View\View;

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
     * @return View Resultado de la operación.
     */
    public function index(FiltroCatalogoRequest $request, ServicioCatalogo $catalogService): View
    {
        $filters = $request->validated();

        $products = $catalogService->query($filters)->paginate(20)->withQueryString();
        $catalogService->agregarDisponibilidad($products->getCollection(), $request->user());

        return view('publico.catalogo.index', [
            'products' => $products,
            ...$catalogService->filterOptions(),
            'query' => (string) ($filters['q'] ?? ''),
        ]);
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @param  mixed  $product  Parámetro recibido por la operación.
     * @param  mixed  $catalogService  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function show(Request $request, Producto $product, ServicioCatalogo $catalogService): View
    {
        abort_unless($product->activo, 404);

        $product->load('category', 'images', 'features', 'variants.images');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());

        return view('publico.catalogo.detalle', ['product' => $product]);
    }
}
