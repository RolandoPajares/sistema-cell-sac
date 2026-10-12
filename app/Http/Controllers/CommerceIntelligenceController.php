<?php

namespace App\Http\Controllers;

use App\Http\Requests\SmartOptimizerRequest;
use App\Services\CommerceIntelligenceService;
use Illuminate\View\View;

class CommerceIntelligenceController extends Controller
{
    public function __construct(private readonly CommerceIntelligenceService $commerce) {}

    public function optimizer(SmartOptimizerRequest $request): View
    {
        $budget = (float) ($request->validated('presupuesto') ?? 0);
        $goal = (string) ($request->validated('objetivo') ?? 'variety');

        return view('smart.optimizer', [
            'budget' => $budget,
            'goal' => $goal,
            'proposal' => $budget > 0 ? $this->commerce->optimize($budget, $goal) : null,
        ]);
    }

    public function wholesale(): View
    {
        return view('smart.wholesale', [
            'products' => $this->commerce->wholesaleProducts(),
            'benefits' => [
                ['icon' => 'bi-cash-stack', 'label' => 'Precios por volumen'],
                ['icon' => 'bi-box-seam', 'label' => 'Stock consultable'],
                ['icon' => 'bi-headset', 'label' => 'Atención personalizada'],
                ['icon' => 'bi-truck', 'label' => 'Envíos seguros'],
            ],
        ]);
    }

    public function ads(): View
    {
        return view('smart.ads', $this->commerce->advertisingSummary());
    }
}
