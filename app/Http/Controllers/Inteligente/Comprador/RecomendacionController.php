<?php

namespace App\Http\Controllers\Inteligente\Comprador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inteligente\Comprador\RecomendacionRequest;
use App\Models\Catalogo\Producto;
use App\Servicios\Inteligente\Comprador\ServicioRecomendaciones;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase RecomendacionController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class RecomendacionController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $servicioRecomendaciones  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioRecomendaciones $servicioRecomendaciones) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(RecomendacionRequest $request): View
    {
        // Obtiene los datos validados de la solicitud antes de utilizarlos en esta operacion.
        $products = $this->servicioRecomendaciones->recomendar($request->validated());
        $products->each(function ($product): void {
            // Guarda en $path el resultado necesario para continuar esta operacion.
            $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
            $product->recommendation_image_url = Producto::urlPublicaDeImagen($path)
                ?: asset('assets/cell-legacy/img/marca/logo.jpeg');
        });

        // Devuelve la vista 'inteligente.comprador.recomendaciones' con los datos preparados por este controlador.
        return view('inteligente.comprador.recomendaciones', [
            'products' => $products,
        ]);
    }
}
