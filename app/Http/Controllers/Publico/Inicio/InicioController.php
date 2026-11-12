<?php

namespace App\Http\Controllers\Publico\Inicio;

use App\Http\Controllers\Controller;
use App\Models\Catalogo\Categoria;
use App\Models\Catalogo\Producto;
use App\Servicios\Compartidos\Catalogo\ServicioCatalogo;
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
        // Prepara en $productosDestacados la consulta que obtiene o modifica los registros necesarios.
        $productosDestacados = $catalogo->conExistenciasPotenciales(Producto::query()
            ->with(['category', 'images'])
            ->active())
            ->whereNotNull('precio_original')
            ->whereNotNull('precio_oferta')
            ->whereColumn('precio_oferta', '<', 'precio_original')
            ->orderByDesc('descuento')
            ->orderByDesc('id')
            ->limit(60)
            ->get();
        $catalogo->agregarDisponibilidad($productosDestacados, auth()->user());
        $productosDestacados = $productosDestacados->filter(fn (Producto $producto): bool => $producto->existencias_disponibles > 0)
            ->take(20)->map(function (Producto $producto): Producto {
                // Guarda en $precioOriginal el resultado necesario para continuar esta operacion.
                $precioOriginal = (float) $producto->precio_original;
                // Guarda en $precioOferta el resultado necesario para continuar esta operacion.
                $precioOferta = (float) $producto->precio_oferta;

                $producto->setAttribute('display_original_price', $precioOriginal);
                $producto->setAttribute('display_discount', $precioOriginal > 0 ? round((1 - ($precioOferta / $precioOriginal)) * 100) : 0);
                $producto->setAttribute('display_specifications', trim(($producto->almacenamiento ? $producto->almacenamiento.' · ' : '').Str::limit(strip_tags((string) $producto->descripcion), 38)));

                return $producto;
            });

        // Prepara en $categorias la consulta que obtiene o modifica los registros necesarios.
        $categorias = Categoria::query()->where('activo', true)->get();

        // Devuelve la vista 'publico.paginas.inicio' con los datos preparados por este controlador.
        return view('publico.paginas.inicio', [
            'productosDestacados' => $productosDestacados,
            'idCategoriaCelulares' => $this->idCategoria($categorias, 'celular'),
            'idCategoriaAccesorios' => $this->idCategoria($categorias, 'accesorio'),
            'puedeAgregarCarrito' => $request->user() !== null,
        ]);
    }

    /**
     * Ejecuta la operación categoryId definida por la clase InicioController.
     *
     * @param  mixed  $categories  Parámetro recibido por la operación.
     * @param  mixed  $needle  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    private function idCategoria(Collection $categorias, string $busqueda): ?int
    {
        return $categorias
            ->first(fn (Categoria $categoria): bool => str_contains(mb_strtolower($categoria->nombre), $busqueda))
            ?->id;
    }
}
