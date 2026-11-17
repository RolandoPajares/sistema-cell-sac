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
        // Guarda en $menu el resultado necesario para continuar esta operacion.
        $menu = $this->menus->paraUsuario($user);
        $items = collect($menu['grupos'])->flatMap(static fn (array $group): array => $group['items'] ?? []);

        // Comprueba la condicion $items->contains(static fn (array $item): bool => ($item['enabled'] ?? false antes de ejecutar este bloque.
        if ($items->contains(static fn (array $item): bool => ($item['enabled'] ?? false)
            && ($item['route'] ?? null) === 'management.dashboard')) {
            return $this->dashboard;
        }

        // Detiene la solicitud si se cumple la validacion de seguridad indicada.
        abort_unless($this->autorizacion->hasAnyPermission($user), 403);

        return $this->dashboard;
    }
}
