<?php

namespace App\Http\Controllers\Publico\Inteligente;

use App\Http\Controllers\Controller;
use App\Http\Requests\OptimizadorInteligenteRequest;
use App\Servicios\ServicioInteligenciaComercial;
use Illuminate\View\View;

class InteligenciaComercialController extends Controller
{
    public function __construct(private readonly ServicioInteligenciaComercial $commerce) {}

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
