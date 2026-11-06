<?php

namespace App\Http\Controllers\Gestion\Campanias;

use App\Http\Controllers\Controller;
use App\Servicios\ServicioInteligenciaComercial;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase PublicidadInteligenteController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class PublicidadInteligenteController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $commerce  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioInteligenciaComercial $commerce) {}

    /**
     * Ejecuta la operación ads definida por la clase PublicidadInteligenteController.
     *
     * @return View Resultado de la operación.
     */
    public function ads(): View
    {
        return view('inteligente.publicidad', $this->commerce->advertisingSummary());
    }
}
