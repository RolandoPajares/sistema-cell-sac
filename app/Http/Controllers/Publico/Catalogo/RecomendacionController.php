<?php

namespace App\Http\Controllers\Publico\Catalogo;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecomendacionRequest;
use App\Servicios\ServicioRecomendaciones;
use Illuminate\View\View;

class RecomendacionController extends Controller
{
    public function __construct(private readonly ServicioRecomendaciones $recommendationService) {}

    public function index(RecomendacionRequest $request): View
    {
        return view('publico.catalogo.recomendaciones', [
            'products' => $this->recommendationService->recommend($request->validated()),
        ]);
    }
}
