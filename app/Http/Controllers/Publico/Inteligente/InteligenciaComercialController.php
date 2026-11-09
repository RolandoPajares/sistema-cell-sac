<?php

namespace App\Http\Controllers\Publico\Inteligente;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptimizadorInteligenteRequest;
use App\Servicios\ServicioInteligenciaComercial;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase InteligenciaComercialController dentro de su mÛdulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
class InteligenciaComercialController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $commerce  Par·metro recibido por la operaciÛn.
     * @return mixed Resultado de la operaciÛn.
     */
    public function __construct(private readonly ServicioInteligenciaComercial $commerce) {}

    /**
     * Ejecuta la operaciÛn optimizer definida por la clase InteligenciaComercialController.
     *
     * @param  mixed  $request  Par·metro recibido por la operaciÛn.
     * @return View Resultado de la operaciÛn.
     */
    public function optimizer(OptimizadorInteligenteRequest $request): View
    {
        $budget = (float) ($request->validated('presupuesto') ?? 0);
        $goal = (string) ($request->validated('objetivo') ?? 'variety');

        return view('inteligente.cotizador.formulario', [
            'budget' => $budget,
            'goal' => $goal,
            'proposal' => $budget > 0 ? $this->commerce->optimize($budget, $goal) : null,
        ]);
    }

    /**
     * Ejecuta la operaciÛn wholesale definida por la clase InteligenciaComercialController.
     *
     * @return View Resultado de la operaciÛn.
     */
    public function wholesale(): View
    {
        return view('inteligente.cotizador.resumen', [
            'products' => $this->commerce->wholesaleProducts(),
            'brands' => ['SAMSUNG', 'Apple', 'oppo', 'XIAOMI', 'HONOR', 'JBL', 'Beats'],
            'benefits' => [
                ['icon' => 'bi-cash-stack', 'label' => 'Precios por volumen'],
                ['icon' => 'bi-box-seam', 'label' => 'Stock consultable'],
                ['icon' => 'bi-headset', 'label' => 'Atenci√≥n personalizada'],
                ['icon' => 'bi-truck', 'label' => 'Env√≠os seguros'],
            ],
        ]);
    }
}
