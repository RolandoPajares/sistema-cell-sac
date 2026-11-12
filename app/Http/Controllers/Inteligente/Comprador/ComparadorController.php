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
        // Obtiene los identificadores enviados por el formulario, elimina valores invalidos o
        // repetidos y limita la comparacion a un maximo de tres productos.
        $ids = collect(explode(',', $request->validated('seleccion', '')))
            ->map(fn (string $id): string => trim($id))
            ->filter(fn (string $id): bool => ctype_digit($id))
            ->unique()
            ->take(3)
            ->values();

        // Consulta los productos activos seleccionados y carga sus caracteristicas, categoria,
        // imagenes y perfil de inteligencia para mostrar una comparacion completa.
        $products = Producto::with('features', 'category', 'images', 'perfilInteligencia')->active()->whereIn('id', $ids)->get();

        // Prepara los productos con el formato y los datos calculados que necesita la vista.
        $products = $catalogService->prepararComparacion($products);

        // Obtiene hasta cien productos activos ordenados por nombre para llenar los selectores
        // que permiten agregar otros equipos a la comparacion.
        $availableProducts = Producto::query()->active()->with('category')->orderBy('nombre')->limit(100)->get();

        // Envia a la vista los productos comparados, las opciones disponibles y la seleccion actual.
        return view('inteligente.comprador.comparacion', [
            'products' => $products,
            'availableProducts' => $availableProducts,
            'selectedIds' => $ids,
        ]);
    }
}
