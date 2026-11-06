<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecomendacionRequest;
use App\Servicios\ServicioRecomendaciones;
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
     * @param  mixed  $recommendationService  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioRecomendaciones $recommendationService) {}

    /**
     * Obtiene y prepara la información solicitada para la operación.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function index(RecomendacionRequest $request): View
    {
        return view('publico.catalogo.recomendaciones', [
            'products' => $this->recommendationService->recommend($request->validated()),
        ]);
    }
}
