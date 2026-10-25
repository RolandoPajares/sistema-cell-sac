<?php

namespace App\Http\Controllers\Paneles;

use App\Http\Controllers\Paneles\Administrador\DashboardController as AdministradorDashboardController;
use App\Http\Controllers\Paneles\ClienteMayorista\DashboardController as ClienteMayoristaDashboardController;
use App\Http\Controllers\Paneles\ClienteMinorista\DashboardController as ClienteMinoristaDashboardController;
use App\Http\Controllers\Paneles\ComprasLogistica\DashboardController as ComprasLogisticaDashboardController;
use App\Http\Controllers\Paneles\Marketing\DashboardController as MarketingDashboardController;
use App\Http\Controllers\Paneles\VentasMayoristas\DashboardController as VentasMayoristasDashboardController;
use App\Http\Controllers\Paneles\VentasMinoristas\DashboardController as VentasMinoristasDashboardController;
use App\Models\User;

final class ResolutorDashboard
{
    public function __construct(
        private readonly AdministradorDashboardController $administrador,
        private readonly MarketingDashboardController $marketing,
        private readonly ComprasLogisticaDashboardController $comprasLogistica,
        private readonly VentasMayoristasDashboardController $ventasMayoristas,
        private readonly VentasMinoristasDashboardController $ventasMinoristas,
        private readonly ClienteMayoristaDashboardController $clienteMayorista,
        private readonly ClienteMinoristaDashboardController $clienteMinorista,
    ) {}

    public function for(User $user): PanelDashboardController
    {
        return match ($user->rol) {
            'administrador' => $this->administrador,
            'marketing' => $this->marketing,
            'compras_logistica' => $this->comprasLogistica,
            'ventas_mayoristas' => $this->ventasMayoristas,
            'ventas_minoristas' => $this->ventasMinoristas,
            'cliente_mayorista' => $this->clienteMayorista,
            'cliente_minorista' => $this->clienteMinorista,
            default => abort(403),
        };
    }
}
