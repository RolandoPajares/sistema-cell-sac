<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InicioController extends Controller
{
    public function __invoke(): View
    {
        $featuredProducts = Producto::query()
            ->with(['category', 'images'])
            ->active()
            ->where('existencias', '>', 0)
            ->whereNotNull('precio_original')
            ->whereNotNull('precio_oferta')
            ->whereColumn('precio_oferta', '<', 'precio_original')
            ->orderByDesc('descuento')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->map(function (Producto $product): Producto {
                $imagePath = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
                $originalPrice = (float) $product->precio_original;
                $salePrice = (float) $product->precio_oferta;

                $product->setAttribute('display_image_path', $imagePath ? ltrim((string) preg_replace('~^assets/~', '', $imagePath), '/') : null);
                $product->setAttribute('display_original_price', $originalPrice);
                $product->setAttribute('display_discount', $originalPrice > 0 ? round((1 - ($salePrice / $originalPrice)) * 100) : 0);
                $product->setAttribute('display_specifications', trim(($product->almacenamiento ? $product->almacenamiento.' · ' : '').Str::limit(strip_tags((string) $product->descripcion), 38)));

                return $product;
            });

        $categories = Categoria::query()->where('activo', true)->get();

        return view('publico.inicio.index', [
            'featuredProducts' => $featuredProducts,
            'cellCategoryId' => $this->categoryId($categories, 'celular'),
            'accessoryCategoryId' => $this->categoryId($categories, 'accesorio'),
        ]);
    }

    private function categoryId(Collection $categories, string $needle): ?int
    {
        return $categories
            ->first(fn (Categoria $category): bool => str_contains(mb_strtolower($category->nombre), $needle))
            ?->id;
    }
}
