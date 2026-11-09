<?php

namespace App\Http\Controllers\Paneles;

use App\Http\Controllers\Paneles\Administrador\DashboardController as AdministradorDashboardController;
use App\Http\Controllers\Paneles\ClienteMinorista\DashboardController as CuentaDashboardController;
use App\Models\User;
use App\Servicios\ServicioAutorizacion;
use App\Servicios\ServicioMenuDinamico;

/**
 * Resuelve el panel a partir de pantallas autorizadas, no de una lista de roles.
 */
final class ResolutorDashboard
{
    public function __construct(
        private readonly AdministradorDashboardController $administrador,
        private readonly CuentaDashboardController $cuenta,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioMenuDinamico $menus,
    ) {}

    public function for(User $user): PanelDashboardController
    {
        $menu = $this->menus->paraUsuario($user);
        $items = collect($menu['grupos'])->flatMap(static fn (array $group): array => $group['items'] ?? []);

        if ($items->contains(static fn (array $item): bool => ($item['enabled'] ?? false)
            && ($item['route'] ?? null) === 'management.dashboard')) {
            return $this->administrador;
        }

        abort_unless($this->autorizacion->hasAnyPermission($user), 403);

        return $this->cuenta;
    }
}
