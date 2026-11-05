<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListadoDispositivosRequest;
use App\Servicios\ServicioDispositivos;
use Illuminate\View\View;

final class DispositivosController extends Controller
{
    public function __construct(private readonly ServicioDispositivos $dispositivos) {}

    public function mostrar(ListadoDispositivosRequest $solicitud): View
    {
        $datos = $this->dispositivos->listar($solicitud->user(), $solicitud->validated());

        return view('paneles.administrador.inventario-logistica.series.listar', $datos);
    }
}
