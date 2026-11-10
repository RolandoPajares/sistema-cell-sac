<?php

namespace App\Http\Controllers\Publico\Inicio;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Servicios\ServicioCatalogo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase InicioController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class InicioController extends Controller
{
    /**
     * Ejecuta la operación __invoke definida por la clase InicioController.
     *
     * @param  mixed  $catalogo  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function __invoke(Request $request, ServicioCatalogo $catalogo): View
    {
        $featuredProducts = $catalogo->conExistenciasPotenciales(Producto::query()
            ->with(['category', 'images'])
            ->active())
            ->whereNotNull('precio_original')
            ->whereNotNull('precio_oferta')
            ->whereColumn('precio_oferta', '<', 'precio_original')
            ->orderByDesc('descuento')
            ->orderByDesc('id')
            ->limit(60)
            ->get();
        $catalogo->agregarDisponibilidad($featuredProducts, auth()->user());
        $featuredProducts = $featuredProducts->filter(fn (Producto $producto): bool => $producto->existencias_disponibles > 0)
            ->take(20)->map(function (Producto $product): Producto {
                $originalPrice = (float) $product->precio_original;
                $salePrice = (float) $product->precio_oferta;

                $product->setAttribute('display_original_price', $originalPrice);
                $product->setAttribute('display_discount', $originalPrice > 0 ? round((1 - ($salePrice / $originalPrice)) * 100) : 0);
                $product->setAttribute('display_specifications', trim(($product->almacenamiento ? $product->almacenamiento.' Â· ' : '').Str::limit(strip_tags((string) $product->descripcion), 38)));

                return $product;
            });

        $categories = Categoria::query()->where('activo', true)->get();

        return view('publico.paginas.inicio', [
            'featuredProducts' => $featuredProducts,
            'cellCategoryId' => $this->categoryId($categories, 'celular'),
            'accessoryCategoryId' => $this->categoryId($categories, 'accesorio'),
            'canAddToCart' => $request->user() !== null,
        ]);
    }

    /**
     * Ejecuta la operación categoryId definida por la clase InicioController.
     *
     * @param  mixed  $categories  Parámetro recibido por la operación.
     * @param  mixed  $needle  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function categoryId(Collection $categories, string $needle): ?int
    {
        return $categories
            ->first(fn (Categoria $category): bool => str_contains(mb_strtolower($category->nombre), $needle))
            ?->id;
    }
}
