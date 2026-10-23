<?php

namespace App\Http\Controllers;

use App\Http\Requests\FiltroCatalogoRequest;
use App\Models\Producto;
use App\Servicios\ServicioCatalogo;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function index(FiltroCatalogoRequest $request, ServicioCatalogo $catalogService): View
    {
        $filters = $request->validated();

        return view('publico.catalogo.index', [
            'products' => $catalogService->query($filters)->paginate(20)->withQueryString(),
            ...$catalogService->filterOptions(),
            'query' => (string) ($filters['q'] ?? ''),
        ]);
    }

    public function show(Producto $product): View
    {
        abort_unless($product->activo, 404);

        return view('publico.catalogo.detalle', ['product' => $product->load('category', 'images', 'features', 'variants.images')]);
    }
}
