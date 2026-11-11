<?php

namespace App\Http\Controllers\Inteligente\Cotizador;

use App\Http\Controllers\Controller;
use App\Http\Requests\Inteligente\Cotizador\OptimizadorInteligenteRequest;
use App\Servicios\Inteligente\ServicioInteligenciaComercial;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase InteligenciaComercialController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class InteligenciaComercialController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $servicioInteligencia  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioInteligenciaComercial $servicioInteligencia) {}

    /**
     * Ejecuta la operación optimizer definida por la clase InteligenciaComercialController.
     *
     * @param  mixed  $request  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function optimizer(OptimizadorInteligenteRequest $request): View
    {
        $budget = (float) ($request->validated('presupuesto') ?? 0);
        $goal = (string) ($request->validated('objetivo') ?? 'variety');

        return view('inteligente.cotizador.formulario', [
            'budget' => $budget,
            'goal' => $goal,
            'proposal' => $budget > 0 ? $this->servicioInteligencia->optimizar($budget, $goal) : null,
        ]);
    }

    /**
     * Ejecuta la operación wholesale definida por la clase InteligenciaComercialController.
     *
     * @return View Resultado de la operación.
     */
    public function wholesale(): View
    {
        return view('inteligente.cotizador.resumen', [
            'products' => $this->servicioInteligencia->productosMayoristas(),
            'brands' => ['SAMSUNG', 'Apple', 'oppo', 'XIAOMI', 'HONOR', 'JBL', 'Beats'],
            'benefits' => [
                ['icon' => 'bi-cash-stack', 'label' => 'Precios por volumen'],
                ['icon' => 'bi-box-seam', 'label' => 'Stock consultable'],
                ['icon' => 'bi-headset', 'label' => 'Atención personalizada'],
                ['icon' => 'bi-truck', 'label' => 'Envíos seguros'],
            ],
        ]);
    }
}
