<?php

namespace App\Http\Controllers\Gestion\Campanias;

use App\Http\Controllers\Controller;
use App\Servicios\ServicioInteligenciaComercial;
use Illuminate\View\View;

class PublicidadInteligenteController extends Controller
{
    public function __construct(private readonly ServicioInteligenciaComercial $commerce) {}

    public function ads(): View
    {
        return view('inteligente.publicidad', $this->commerce->advertisingSummary());
    }
}
