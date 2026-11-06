<?php

namespace App\Http\Controllers\Publico\Inteligente;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptimizadorInteligenteRequest;
use App\Servicios\ServicioInteligenciaComercial;
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
     * @param  mixed  $commerce  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioInteligenciaComercial $commerce) {}

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

        return view('publico.inteligente.optimizador', [
            'budget' => $budget,
            'goal' => $goal,
            'proposal' => $budget > 0 ? $this->commerce->optimize($budget, $goal) : null,
        ]);
    }

    /**
     * Ejecuta la operación wholesale definida por la clase InteligenciaComercialController.
     *
     * @return View Resultado de la operación.
     */
    public function wholesale(): View
    {
        return view('publico.inteligente.mayorista', [
            'products' => $this->commerce->wholesaleProducts(),
            'benefits' => [
                ['icon' => 'bi-cash-stack', 'label' => 'Precios por volumen'],
                ['icon' => 'bi-box-seam', 'label' => 'Stock consultable'],
                ['icon' => 'bi-headset', 'label' => 'AtenciÃ³n personalizada'],
                ['icon' => 'bi-truck', 'label' => 'EnvÃ­os seguros'],
            ],
        ]);
    }
}
