<?php

namespace App\Http\Controllers;

use App\Http\Requests\ComparisonRequest;
use App\Models\Product;
use Illuminate\View\View;

class ComparisonController extends Controller
{
    public function index(ComparisonRequest $request): View
    {
        $ids = collect(explode(',', $request->validated('seleccion', '')))
            ->map(fn (string $id): string => trim($id))
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->unique()
            ->take(3)
            ->values();
        $products = Product::with('features', 'category', 'images', 'intelligenceProfile')->active()->whereIn('id', $ids)->get();
        $availableProducts = Product::query()->active()->with('category')->orderBy('nombre')->limit(100)->get();

        return view('catalog.compare', compact('products', 'availableProducts', 'ids'));
    }
}
