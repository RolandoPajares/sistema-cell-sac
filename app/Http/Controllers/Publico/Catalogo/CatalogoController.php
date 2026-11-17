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
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $filters = $request->validated();

        // Prepara en $products la consulta que obtiene o modifica los registros necesarios.
        $products = $catalogService->query($filters)->paginate(20)->withQueryString();
        $catalogService->agregarDisponibilidad($products->getCollection(), $request->user());

        $products->getCollection()->transform(function (Producto $product): Producto {
            // Guarda en $salePrice el resultado necesario para continuar esta operacion.
            $salePrice = (float) ($product->precio_oferta ?? $product->precio);
            // Guarda en $originalPrice el resultado necesario para continuar esta operacion.
            $originalPrice = (float) ($product->precio_original ?? $product->precio);

            return $product->setAttribute('catalog_sale_price', $salePrice)
                ->setAttribute('catalog_original_price', $originalPrice)
                ->setAttribute('catalog_discount', $originalPrice > $salePrice && $originalPrice > 0
                    ? round((1 - ($salePrice / $originalPrice)) * 100)
                    : 0);
        });

        // Guarda en $selectedCategory el resultado necesario para continuar esta operacion.
        $selectedCategory = $filters['categoria'] ?? null;
        // Guarda en $selectedBrand el resultado necesario para continuar esta operacion.
        $selectedBrand = $filters['marca'] ?? null;
        // Guarda en $selectedOrder el resultado necesario para continuar esta operacion.
        $selectedOrder = $filters['orden'] ?? 'nuevo';
        // Guarda en $minimumPrice el resultado necesario para continuar esta operacion.
        $minimumPrice = $filters['min'] ?? null;
        // Guarda en $maximumPrice el resultado necesario para continuar esta operacion.
        $maximumPrice = $filters['max'] ?? null;
        // Guarda en $baseFilters el resultado necesario para continuar esta operacion.
        $baseFilters = collect($filters)->except('page')->all();
        // Guarda en $filterOptions el resultado necesario para continuar esta operacion.
        $filterOptions = $catalogService->filterOptions();

        // Devuelve la vista 'publico.catalogo.index' con los datos preparados por este controlador.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($product->activo, 404);

        $product->load('category', 'images', 'features', 'variants.images', 'reviews');
        $catalogService->agregarDisponibilidad($product->newCollection([$product]), $request->user());
        $catalogService->agregarDisponibilidadVariantes($product->variants);
        // Guarda en $relatedProducts el resultado necesario para continuar esta operacion.
        $relatedProducts = $catalogService->relacionados($product);
        $catalogService->agregarDisponibilidad($relatedProducts, $request->user());

        // Guarda en $categoryName el resultado necesario para continuar esta operacion.
        $categoryName = $product->category?->nombre ?: $product->categoria;
        // Guarda en $imageItems el resultado necesario para continuar esta operacion.
        $imageItems = $product->images->pluck('ruta_imagen');
        // Comprueba la condicion $imageItems->isEmpty() && $product->url_imagen antes de ejecutar este bloque.
        if ($imageItems->isEmpty() && $product->url_imagen) {
            // Guarda en $imageItems el resultado necesario para continuar esta operacion.
            $imageItems = collect([$product->url_imagen]);
        }
        $imageItems = $imageItems->map(static fn (string $route): array => [
            'url' => Producto::urlPublicaDeImagen($route),
        ])->values();
        // Guarda en $firstVariant el resultado necesario para continuar esta operacion.
        $firstVariant = $product->variants->first();
        // Guarda en $salePrice el resultado necesario para continuar esta operacion.
        $salePrice = (float) ($product->precio_oferta ?? $product->precio);
        // Guarda en $originalPrice el resultado necesario para continuar esta operacion.
        $originalPrice = (float) ($product->precio_original ?? $product->precio);
        $isAuthenticated = $request->user() !== null;
        // Guarda en $hasStock el resultado necesario para continuar esta operacion.
        $hasStock = (int) $product->existencias_disponibles > 0;
        // Obtiene en $userRating la informacion de la solicitud o del usuario actual.
        $userRating = (int) ($request->user()
            ? ResenaProducto::query()->where('producto_id', $product->getKey())->where('usuario_id', $request->user()->getKey())->value('valoracion')
            : 0);
        // Obtiene en $isFavorite la informacion de la solicitud o del usuario actual.
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
        // Guarda en $variantOptions el resultado necesario para continuar esta operacion.
        $variantOptions = $product->variants->values()->map(static function ($variant, int $index): array {
            // Guarda en $stock el resultado necesario para continuar esta operacion.
            $stock = (int) ($variant->existencias_disponibles ?? $variant->existencias);

            return [
                'id' => (int) $variant->id,
                'name' => $variant->nombre_color,
                'selected' => $index === 0,
                'disabled' => $stock <= 0,
                'class' => $stock <= 0 ? 'is-disabled' : '',
            ];
        });

        // Devuelve la vista 'publico.catalogo.detalle' con los datos preparados por este controlador.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($product->activo, 404);

        // Prepara en $favorite la consulta que obtiene o modifica los registros necesarios.
        $favorite = FavoritoProducto::query()
            ->where('producto_id', $product->getKey())
            ->where('usuario_id', $request->user()->getKey())
            ->first();

        // Comprueba la condicion $favorite antes de ejecutar este bloque.
        if ($favorite) {
            $favorite->delete();
        } else {
            FavoritoProducto::create([
                'producto_id' => $product->getKey(),
                'usuario_id' => $request->user()->getKey(),
            ]);
        }

        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
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
        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($product->activo, 404);

        // Guarda en $review el resultado necesario para continuar esta operacion.
        $review = $product->reviews()->updateOrCreate(
            ['usuario_id' => $request->user()->getKey()],
            // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
            $request->validated(),
        );

        // Guarda en $reviews el resultado necesario para continuar esta operacion.
        $reviews = $product->reviews()->get(['valoracion']);

        // Devuelve al navegador el resultado HTTP producido por la instruccion siguiente.
        return response()->json([
            'valoracion' => (int) $review->valoracion,
            'promedio' => round((float) ($reviews->avg('valoracion') ?? 0), 1),
            'cantidad' => $reviews->count(),
        ]);
    }
}
