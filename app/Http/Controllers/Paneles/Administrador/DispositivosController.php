<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListadoDispositivosRequest;
use App\Servicios\ServicioDispositivos;
use Illuminate\View\View;

/**
 * Define la responsabilidad de la clase DispositivosController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class DispositivosController extends Controller
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $dispositivos  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioDispositivos $dispositivos) {}

    /**
     * Ejecuta la operación mostrar definida por la clase DispositivosController.
     *
     * @param  mixed  $solicitud  Parámetro recibido por la operación.
     * @return View Resultado de la operación.
     */
    public function mostrar(ListadoDispositivosRequest $solicitud): View
    {
        $datos = $this->dispositivos->listar($solicitud->user(), $solicitud->validated());

        return view('paneles.administrador.inventario-logistica.series.listar', $datos);
    }
}
