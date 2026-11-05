<?php

namespace App\Http\Controllers\Paneles\ClienteMinorista;

use App\Http\Controllers\Paneles\PanelDashboardController;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'cliente_minorista';
    }

    protected function viewName(): string
    {
        return 'paneles.cliente-minorista.dashboard';
    }
}
