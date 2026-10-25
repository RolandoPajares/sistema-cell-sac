<?php

namespace App\Http\Controllers\Paneles\Marketing;

use App\Http\Controllers\Paneles\PanelDashboardController;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'marketing';
    }

    protected function viewName(): string
    {
        return 'paneles.marketing.dashboard';
    }
}
