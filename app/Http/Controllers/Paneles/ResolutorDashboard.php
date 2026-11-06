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
use App\Servicios\ServicioAutorizacion;

/**
 * Define la responsabilidad de la clase ResolutorDashboard dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ResolutorDashboard
{
    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @return mixed Resultado de la operación.
     */
    public function __construct(
        private readonly AdministradorDashboardController $administrador,
        private readonly MarketingDashboardController $marketing,
        private readonly ComprasLogisticaDashboardController $comprasLogistica,
        private readonly VentasMayoristasDashboardController $ventasMayoristas,
        private readonly VentasMinoristasDashboardController $ventasMinoristas,
        private readonly ClienteMayoristaDashboardController $clienteMayorista,
        private readonly ClienteMinoristaDashboardController $clienteMinorista,
        private readonly ServicioAutorizacion $autorizacion,
    ) {}

    /**
     * Ejecuta la operación for definida por la clase ResolutorDashboard.
     *
     * @param  mixed  $user  Parámetro recibido por la operación.
     * @return PanelDashboardController Resultado de la operación.
     */
    public function for(User $user): PanelDashboardController
    {
        foreach ([
            'administrador' => $this->administrador,
            'marketing' => $this->marketing,
            'compras_logistica' => $this->comprasLogistica,
            'ventas_mayoristas' => $this->ventasMayoristas,
            'ventas_minoristas' => $this->ventasMinoristas,
            'cliente_mayorista' => $this->clienteMayorista,
            'cliente_minorista' => $this->clienteMinorista,
        ] as $code => $dashboard) {
            if ($this->autorizacion->hasRole($user, $code)) {
                return $dashboard;
            }
        }

        abort(403);
    }
}
