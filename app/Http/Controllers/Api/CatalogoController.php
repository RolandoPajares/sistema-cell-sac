<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroCatalogoRequest;
use App\Http\Resources\RecursoProducto;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioCatalogo;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogoController extends Controller
{
    public function index(FiltroCatalogoRequest $request, ServicioCatalogo $catalogService): AnonymousResourceCollection
    {
        $products = $catalogService
            ->query($request->validated())
            ->paginate($request->integer('per_page', 20))
            ->withQueryString();

        return RecursoProducto::collection($products);
    }

    public function show(Producto $product): RecursoProducto
    {
        abort_unless($product->activo, 404);

        return new RecursoProducto($product->load('category', 'images'));
    }
}
