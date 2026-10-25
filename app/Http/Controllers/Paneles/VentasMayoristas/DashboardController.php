<?php

namespace App\Http\Controllers\Paneles\VentasMayoristas;

use App\Http\Controllers\Paneles\PanelDashboardController;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'ventas_mayoristas';
    }

    protected function viewName(): string
    {
        return 'paneles.ventas-mayoristas.dashboard';
    }
}
