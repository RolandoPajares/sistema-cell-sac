<?php

namespace App\Http\Controllers\Paneles\ComprasLogistica;

use App\Http\Controllers\Paneles\PanelDashboardController;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'compras_logistica';
    }

    protected function viewName(): string
    {
        return 'paneles.compras-logistica.dashboard';
    }
}
