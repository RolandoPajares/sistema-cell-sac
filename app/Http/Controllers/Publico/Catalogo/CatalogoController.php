<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\FiltroCatalogoRequest;
use App\Http\Requests\GuardarResenaProductoRequest;
use App\Models\Catalogo\FavoritoProducto;
use App\Models\Catalogo\Producto;
use App\Models\Catalogo\ResenaProducto;
use App\Servicios\ServicioCatalogo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Presenta el catálogo público y expone las acciones de favoritos y valoraciones.
 */
class CatalogoController extends Controller
{
    /**
     * Filtra y pagina el catálogo, agregando disponibilidad y opciones de filtro a la vista.
     *
     * @param FiltroCatalogoRequest $request Filtros validados del catálogo.
     * @param ServicioCatalogo $catalogService Servicio que construye la consulta y disponibilidad.
     * @return View Vista pública con productos paginados y filtros disponibles.
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
     * Carga el detalle de un producto activo con stock, variantes, relacionados y valoraciones.
     *
     * @param Request $request Solicitud que puede identificar al usuario actual.
     * @param Producto $product Producto resuelto por route model binding.
     * @param ServicioCatalogo $catalogService Servicio de disponibilidad y relacionados.
     * @return View Vista pública del detalle y sus indicadores de valoración/favorito.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si el producto no está activo.
     */
    public function show(Request $request, Producto $product, ServicioCatalogo $catalogService): View
    {
        abort_unless($product->activo, 404);

        $product->load('category', 'images', 'features', 'variants.images', 'reviews');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());
        $catalogService->agregarDisponibilidadVariantes($product->variants);
        $relatedProducts = $catalogService->relacionados($product);
        $catalogService->agregarDisponibilidad($relatedProducts, $request->user());

        return view('publico.catalogo.detalle', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'averageRating' => round((float) ($product->reviews->avg('valoracion') ?? 0), 1),
            'reviewCount' => $product->reviews->count(),
            'userRating' => (int) ($request->user()
                ? ResenaProducto::query()->where('producto_id', $product->getKey())->where('usuario_id', $request->user()->getKey())->value('valoracion')
                : 0),
            'isFavorite' => $request->user()
                ? $product->favorites()->where('usuario_id', $request->user()->getKey())->exists()
                : false,
        ]);
    }

    /**
     * Alterna el favorito del usuario autenticado para un producto activo.
     *
     * Busca la relación existente; si existe la elimina y, si no existe, crea
     * una nueva relación antes de devolver el estado resultante.
     *
     * @param Request $request Solicitud del usuario autenticado.
     * @param Producto $product Producto cuyo favorito se alternará.
     * @return JsonResponse Estado booleano del favorito después de la operación.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si el producto no está activo.
     */
    public function toggleFavorite(Request $request, Producto $product): JsonResponse
    {
        abort_unless($product->activo, 404);

        $favorite = FavoritoProducto::query()
            ->where('producto_id', $product->getKey())
            ->where('usuario_id', $request->user()->getKey())
            ->first();

        if ($favorite) {
            $favorite->delete();
        } else {
            FavoritoProducto::create([
                'producto_id' => $product->getKey(),
                'usuario_id' => $request->user()->getKey(),
            ]);
        }

        return response()->json(['favorito' => ! $favorite]);
    }

    /**
     * Crea o actualiza la valoración del usuario para un producto activo.
     *
     * @param GuardarResenaProductoRequest $request Valoración validada del usuario autenticado.
     * @param Producto $product Producto cuya valoración se guarda.
     * @return JsonResponse Valoración guardada, promedio y cantidad recalculados.
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException Si el producto no está activo.
     */
    public function storeReview(GuardarResenaProductoRequest $request, Producto $product): JsonResponse
    {
        abort_unless($product->activo, 404);

        $review = $product->reviews()->updateOrCreate(
            ['usuario_id' => $request->user()->getKey()],
            $request->validated(),
        );

        $reviews = $product->reviews()->get(['valoracion']);

        return response()->json([
            'valoracion' => (int) $review->valoracion,
            'promedio' => round((float) ($reviews->avg('valoracion') ?? 0), 1),
            'cantidad' => $reviews->count(),
        ]);
    }
}
