<?php

namespace App\Http\Controllers\Paneles\ComprasLogistica;

use App\Http\Controllers\Paneles\PanelDashboardController;

/**
 * Define la responsabilidad de la clase DashboardController dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class DashboardController extends PanelDashboardController
{
    /**
     * Ejecuta la operación role definida por la clase DashboardController.
     *
     * @return string Resultado de la operación.
     */
    protected function role(): string
    {
        return 'compras_logistica';
    }

    /**
     * Recupera y presenta el detalle de la información solicitada.
     *
     * @return string Resultado de la operación.
     */
    protected function viewName(): string
    {
        return 'paneles.compras-logistica.dashboard';
    }
}
