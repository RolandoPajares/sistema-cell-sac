<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Compartidos\Catalogo\FiltroCatalogoRequest;
use App\Http\Requests\Publico\Catalogo\GuardarResenaProductoRequest;
use App\Models\Catalogo\FavoritoProducto;
use App\Models\Catalogo\Producto;
use App\Models\Catalogo\ResenaProducto;
use App\Servicios\Compartidos\Catalogo\ServicioCatalogo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Presenta el catálogo público y expone las acciones de favoritos y valoraciones.
 */
class CatalogoController extends Controller
{
    /**
     * Filtra y pagina el catálogo, agregando disponibilidad y opciones de filtro a la vista.
     *
     * @param  FiltroCatalogoRequest  $request  Filtros validados del catálogo.
     * @param  ServicioCatalogo  $catalogService  Servicio que construye la consulta y disponibilidad.
     * @return View Vista pública con productos paginados y filtros disponibles.
     */
    public function index(FiltroCatalogoRequest $request, ServicioCatalogo $catalogService): View
    {
        $filters = $request->validated();

        $products = $catalogService->query($filters)->paginate(20)->withQueryString();
        $catalogService->agregarDisponibilidad($products->getCollection(), $request->user());

        $products->getCollection()->transform(function (Producto $product): Producto {
            $salePrice = (float) ($product->precio_oferta ?? $product->precio);
            $originalPrice = (float) ($product->precio_original ?? $product->precio);

            return $product->setAttribute('catalog_sale_price', $salePrice)
                ->setAttribute('catalog_original_price', $originalPrice)
                ->setAttribute('catalog_discount', $originalPrice > $salePrice && $originalPrice > 0
                    ? round((1 - ($salePrice / $originalPrice)) * 100)
                    : 0);
        });

        $selectedCategory = $filters['categoria'] ?? null;
        $selectedBrand = $filters['marca'] ?? null;
        $selectedOrder = $filters['orden'] ?? 'nuevo';
        $minimumPrice = $filters['min'] ?? null;
        $maximumPrice = $filters['max'] ?? null;
        $baseFilters = collect($filters)->except('page')->all();
        $filterOptions = $catalogService->filterOptions();

        return view('publico.catalogo.index', [
            'products' => $products,
            ...$filterOptions,
            'query' => (string) ($filters['q'] ?? ''),
            'baseFilters' => $baseFilters,
            'baseFiltersWithoutCategory' => collect($baseFilters)->except('categoria')->all(),
            'baseFiltersWithoutBrand' => collect($baseFilters)->except('marca')->all(),
            'selectedCategoryName' => $selectedCategory !== null
                ? $filterOptions['categories']->firstWhere('id', (int) $selectedCategory)?->nombre
                : null,
            'selectedCategory' => $selectedCategory,
            'selectedBrand' => $selectedBrand,
            'selectedOrder' => $selectedOrder,
            'minimumPrice' => $minimumPrice,
            'maximumPrice' => $maximumPrice,
            'canAddToCart' => $request->user() !== null,
        ]);
    }

    /**
     * Carga el detalle de un producto activo con stock, variantes, relacionados y valoraciones.
     *
     * @param  Request  $request  Solicitud que puede identificar al usuario actual.
     * @param  Producto  $product  Producto resuelto por route model binding.
     * @param  ServicioCatalogo  $catalogService  Servicio de disponibilidad y relacionados.
     * @return View Vista pública del detalle y sus indicadores de valoración/favorito.
     *
     * @throws HttpException Si el producto no está activo.
     */
    public function show(Request $request, Producto $product, ServicioCatalogo $catalogService): View
    {
        abort_unless($product->activo, 404);

        $product->load('category', 'images', 'features', 'variants.images', 'reviews');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());
        $catalogService->agregarDisponibilidadVariantes($product->variants);
        $relatedProducts = $catalogService->relacionados($product);
        $catalogService->agregarDisponibilidad($relatedProducts, $request->user());

        $categoryName = $product->category?->nombre ?: $product->categoria;
        $imageItems = $product->images->pluck('ruta_imagen');
        if ($imageItems->isEmpty() && $product->url_imagen) {
            $imageItems = collect([$product->url_imagen]);
        }
        $imageItems = $imageItems->map(static fn (string $route): array => [
            'url' => Producto::urlPublicaDeImagen($route),
        ])->values();
        $firstVariant = $product->variants->first();
        $salePrice = (float) ($product->precio_oferta ?? $product->precio);
        $originalPrice = (float) ($product->precio_original ?? $product->precio);
        $isAuthenticated = $request->user() !== null;
        $hasStock = (int) $product->existencias_disponibles > 0;
        $userRating = (int) ($request->user()
            ? ResenaProducto::query()->where('producto_id', $product->getKey())->where('usuario_id', $request->user()->getKey())->value('valoracion')
            : 0);
        $isFavorite = $request->user()
            ? $product->favorites()->where('usuario_id', $request->user()->getKey())->exists()
            : false;
        $ratingOptions = collect(range(1, 5))->map(static fn (int $rating): array => [
            'value' => $rating,
            'selected' => $rating === $userRating,
        ]);
        $variantData = $product->variants->map(static fn ($variant): array => [
            'id' => (int) $variant->id,
            'name' => $variant->nombre_color,
            'sku' => $variant->codigo_sku,
            'stock' => (int) ($variant->existencias_disponibles ?? $variant->existencias),
            'image' => $variant->images->first()?->ruta_imagen
                ? Producto::urlPublicaDeImagen($variant->images->first()->ruta_imagen)
                : null,
        ])->values();
        $variantOptions = $product->variants->values()->map(static function ($variant, int $index): array {
            $stock = (int) ($variant->existencias_disponibles ?? $variant->existencias);

            return [
                'id' => (int) $variant->id,
                'name' => $variant->nombre_color,
                'selected' => $index === 0,
                'disabled' => $stock <= 0,
                'class' => $stock <= 0 ? 'is-disabled' : '',
            ];
        });

        return view('publico.catalogo.detalle', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'categoryName' => $categoryName,
            'imageItems' => $imageItems,
            'primaryImage' => $imageItems->first()['url'] ?? null,
            'salePrice' => $salePrice,
            'originalPrice' => $originalPrice,
            'discount' => $originalPrice > $salePrice && $originalPrice > 0
                ? round((1 - ($salePrice / $originalPrice)) * 100)
                : 0,
            'firstVariant' => $firstVariant,
            'primarySku' => $firstVariant?->codigo_sku ?: 'No registrado',
            'productColor' => $product->color ?: ($firstVariant?->nombre_color ?: 'No registrado'),
            'hasStock' => $hasStock,
            'stockClass' => $hasStock ? 'is-available' : 'is-unavailable',
            'stockIcon' => $hasStock ? 'bi-check-circle' : 'bi-x-circle',
            'stockLabel' => $hasStock ? $product->existencias_disponibles.' unidades disponibles' : 'Sin stock',
            'isAuthenticated' => $isAuthenticated,
            'favoriteIcon' => $isFavorite ? 'bi-heart-fill' : 'bi-heart',
            'favoriteLabel' => $isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos',
            'favoriteStatus' => $isAuthenticated ? 'Se guarda en tu cuenta' : 'Inicia sesión para guardar',
            'reviewMessage' => $isAuthenticated
                ? ($userRating ? 'Tu valoración actual se guarda en tu cuenta.' : 'Selecciona una estrella para guardar tu valoración.')
                : 'Inicia sesión para guardar tu valoración en la base de datos.',
            'ratingOptions' => $ratingOptions,
            'visualType' => str_contains(mb_strtolower((string) $categoryName), 'audio')
                ? 'audio'
                : (str_contains(mb_strtolower((string) $categoryName), 'celular') ? 'celular' : 'accesorio'),
            'variantData' => $variantData,
            'variantOptions' => $variantOptions,
            'averageRating' => round((float) ($product->reviews->avg('valoracion') ?? 0), 1),
            'reviewCount' => $product->reviews->count(),
            'userRating' => $userRating,
            'isFavorite' => $isFavorite,
        ]);
    }

    /**
     * Alterna el favorito del usuario autenticado para un producto activo.
     *
     * Busca la relación existente; si existe la elimina y, si no existe, crea
     * una nueva relación antes de devolver el estado resultante.
     *
     * @param  Request  $request  Solicitud del usuario autenticado.
     * @param  Producto  $product  Producto cuyo favorito se alternará.
     * @return JsonResponse Estado booleano del favorito después de la operación.
     *
     * @throws HttpException Si el producto no está activo.
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
     * @param  GuardarResenaProductoRequest  $request  Valoración validada del usuario autenticado.
     * @param  Producto  $product  Producto cuya valoración se guarda.
     * @return JsonResponse Valoración guardada, promedio y cantidad recalculados.
     *
     * @throws HttpException Si el producto no está activo.
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
