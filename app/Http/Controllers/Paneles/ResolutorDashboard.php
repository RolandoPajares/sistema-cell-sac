<?php

namespace App\Http\Controllers\Paneles;

use App\Models\User;
use App\Servicios\Compartidos\Seguridad\ServicioAutorizacion;
use App\Servicios\Compartidos\Seguridad\ServicioMenuDinamico;

/**
 * Resuelve el panel a partir de pantallas autorizadas, no de una lista de roles.
 */
final class ResolutorDashboard
{
    public function __construct(
        private readonly PanelDashboardController $dashboard,
        private readonly ServicioAutorizacion $autorizacion,
        private readonly ServicioMenuDinamico $menus,
    ) {}

    public function for(User $user): PanelDashboardController
    {
        $menu = $this->menus->paraUsuario($user);
        $items = collect($menu['grupos'])->flatMap(static fn (array $group): array => $group['items'] ?? []);

        if ($items->contains(static fn (array $item): bool => ($item['enabled'] ?? false)
            && ($item['route'] ?? null) === 'management.dashboard')) {
            return $this->dashboard;
        }

        abort_unless($this->autorizacion->hasAnyPermission($user), 403);

        return $this->dashboard;
    }
}
