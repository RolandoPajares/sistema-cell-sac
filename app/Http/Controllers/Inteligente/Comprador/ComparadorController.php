<?php

namespace App\Http\Controllers\Inteligente\Comprador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inteligente\Comprador\ComparacionRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\Compartidos\Catalogo\ServicioCatalogo;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase ComparadorController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class ComparadorController extends Controller
{
    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(ComparacionRequest $request, ServicioCatalogo $catalogService): View
    {
        $ids = collect(explode(',', $request->validated('seleccion', '')))
            ->map(fn (string $id): string => trim($id))
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->unique()
            ->take(3)
            ->values();
        $products = Producto::with('features', 'category', 'images', 'intelligenceProfile')->active()->whereIn('id', $ids)->get();
        $products = $catalogService->prepararComparacion($products);
        $availableProducts = Producto::query()->active()->with('category')->orderBy('nombre')->limit(100)->get();

        return view('inteligente.comprador.comparacion', [
            'products' => $products,
            'availableProducts' => $availableProducts,
            'selectedIds' => $ids,
        ]);
    }
}
