<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $productsQuery = Product::query()->with(['category', 'images'])->active();

        if ($request->filled('q')) {
            $term = $request->string('q')->trim()->toString();
            $productsQuery->where(fn ($query) => $query->where('nombre', 'like', '%'.$term.'%')->orWhere('marca', 'like', '%'.$term.'%'));
        }

        if ($request->filled('categoria')) {
            $productsQuery->where('categoria_id', $request->integer('categoria'));
        }

        if ($request->filled('marca')) {
            $productsQuery->where('marca', $request->string('marca')->toString());
        }

        if ($request->filled('min')) {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) >= ?', [$request->float('min')]);
        }

        if ($request->filled('max')) {
            $productsQuery->whereRaw('COALESCE(precio_oferta, precio) <= ?', [$request->float('max')]);
        }

        $sort = $request->query('orden');
        $field = match ($sort) {
            'precio_asc', 'precio_desc' => 'precio',
            'nuevo' => 'creado_en',
            default => 'nombre',
        };
        $direction = match ($sort) {
            'precio_asc' => 'asc',
            'precio_desc', 'nuevo' => 'desc',
            default => 'asc',
        };

        return view('catalog.index', [
            'products' => $productsQuery->orderBy($field, $direction)->orderBy('id')->paginate(20)->withQueryString(),
            'categories' => Category::query()->where('activo', true)->orderBy('nombre')->get(),
            'brands' => Product::query()->active()->whereNotNull('marca')->where('marca', '<>', '')->distinct()->orderBy('marca')->pluck('marca'),
            'query' => $request->string('q')->toString(),
        ]);
    }

    public function show(Product $product): View
    {
        abort_unless($product->activo, 404);

        return view('catalog.show', ['product' => $product->load('category', 'images', 'features', 'variants.images')]);
    }
}
