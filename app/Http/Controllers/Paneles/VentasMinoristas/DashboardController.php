<?php

namespace App\Http\Controllers\Paneles\VentasMinoristas;

use App\Http\Controllers\Paneles\PanelDashboardController;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'ventas_minoristas';
    }

    protected function viewName(): string
    {
        return 'paneles.ventas-minoristas.dashboard';
    }
}
