<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroCatalogoRequest;
use App\Http\Resources\RecursoProducto;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioCatalogo;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogoController extends Controller
{
    public function index(FiltroCatalogoRequest $request, ServicioCatalogo $catalogService): AnonymousResourceCollection
    {
        $products = $catalogService
            ->query($request->validated())
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();
        $catalogService->agregarDisponibilidad($products->getCollection(), $request->user());

        return RecursoProducto::collection($products);
    }

    public function show(Request $request, Producto $product, ServicioCatalogo $catalogService): RecursoProducto
    {
        abort_unless($product->activo, 404);

        $product->load('category', 'images');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());

        return new RecursoProducto($product);
    }
}
