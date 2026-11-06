<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroCatalogoRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioCatalogo;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
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

    public function show(Request $request, Producto $product, ServicioCatalogo $catalogService): View
    {
        abort_unless($product->activo, 404);

        $product->load('category', 'images', 'features', 'variants.images');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());
        $catalogService->agregarDisponibilidadVariantes($product->variants);
        $relatedProducts = $catalogService->relacionados($product);
        $catalogService->agregarDisponibilidad($relatedProducts, $request->user());

        return view('publico.catalogo.detalle', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }
}
