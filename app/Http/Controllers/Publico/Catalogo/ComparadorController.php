<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\ComparacionRequest;
use App\Models\Catalogo\Producto;
use Illuminate\View\View;

class ComparadorController extends Controller
{
    public function index(ComparacionRequest $request): View
    {
        $ids = collect(explode(',', $request->validated('seleccion', '')))
            ->map(fn (string $id): string => trim($id))
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->unique()
            ->take(3)
            ->values();
        $products = Producto::with('features', 'category', 'images', 'intelligenceProfile')->active()->whereIn('id', $ids)->get();
        $availableProducts = Producto::query()->active()->with('category')->orderBy('nombre')->limit(100)->get();

        return view('publico.catalogo.comparar', compact('products', 'availableProducts', 'ids'));
    }
}
