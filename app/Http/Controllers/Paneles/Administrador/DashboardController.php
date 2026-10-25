<?php

namespace App\Http\Controllers\Paneles\Administrador;

use App\Http\Controllers\Paneles\PanelDashboardController;
use App\Models\User;
use Illuminate\Http\Request;

final class DashboardController extends PanelDashboardController
{
    protected function role(): string
    {
        return 'administrador';
    }

    protected function viewName(): string
    {
        return 'paneles.administrador.dashboard';
    }

    /**
     * @return array{products:int, lowStock:int, orders:int, suppliers:int}
     */
    protected function viewData(Request $request, User $user): array
    {
        return $this->panelService->administratorSummary();
    }
}
