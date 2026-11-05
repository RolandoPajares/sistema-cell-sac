<?php

namespace App\Http\Controllers\Paneles\ClienteMayorista;

use App\Http\Controllers\Paneles\PanelDashboardController;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'cliente_mayorista';
    }

    protected function viewName(): string
    {
        return 'paneles.cliente-mayorista.dashboard';
    }
}
