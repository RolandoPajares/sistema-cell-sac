<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListadoEnviosPedidosRequest;
use App\Servicios\ServicioEnviosPedidos;
use Illuminate\View\View;

final class EnviosPedidosController extends Controller
{
    public function __construct(private readonly ServicioEnviosPedidos $envios) {}

    public function mostrar(ListadoEnviosPedidosRequest $solicitud): View
    {
        return view(
            'paneles.administrador.inventario-logistica.envios.listar',
            $this->envios->listar($solicitud->user(), $solicitud->validated()),
        );
    }
}
