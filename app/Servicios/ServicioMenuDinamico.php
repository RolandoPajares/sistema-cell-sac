<?php

namespace App\Servicios;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

final class ServicioMenuDinamico
{
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /** @return array{dinamico:bool,grupos:array<int,array<string,mixed>>} */
    public function paraUsuario(?User $user): array
    {
        if (! $user instanceof User) {
            return ['dinamico' => false, 'grupos' => []];
        }

        if (! $this->schemaAvailable() || ! $this->autorizacion->isSeeded()) {
            return ['dinamico' => true, 'grupos' => []];
        }

        $roleIds = $this->roleIdsFor($user);

        if ($roleIds === []) {
            return ['dinamico' => true, 'grupos' => []];
        }

        $menus = DB::table('menus')
            ->leftJoin('roles_menus', function ($join) use ($roleIds): void {
                $join->on('roles_menus.menu_id', '=', 'menus.id')->whereIn('roles_menus.rol_id', $roleIds);
            })
            ->where(function ($query) use ($roleIds): void {
                $query->whereNotNull('roles_menus.rol_id')
                    ->orWhereExists(function ($subquery) use ($roleIds): void {
                        $subquery->selectRaw('1')
                            ->from('elementos_menu as menu_permissions')
                            ->join('roles_permisos as effective_grants', 'effective_grants.permiso_id', '=', 'menu_permissions.permiso_id')
                            ->whereColumn('menu_permissions.menu_id', 'menus.id')
                            ->whereIn('effective_grants.rol_id', $roleIds);
                    });
            })
            ->where('menus.habilitado', true)
            ->where('menus.contexto', 'sidebar')
            ->select('menus.id', 'menus.codigo', 'menus.nombre')
            ->orderBy('menus.id')
            ->get();

        if ($menus->isEmpty()) {
            return ['dinamico' => true, 'grupos' => []];
        }

        $elementos = DB::table('elementos_menu')
            ->leftJoin('pantallas', 'pantallas.id', '=', 'elementos_menu.pantalla_id')
            ->leftJoin('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
            ->leftJoin('permisos', 'permisos.id', '=', 'elementos_menu.permiso_id')
            ->whereIn('elementos_menu.menu_id', $menus->pluck('id'))
            ->where('elementos_menu.habilitado', true)
            ->select(
                'elementos_menu.id', 'elementos_menu.menu_id', 'elementos_menu.padre_id',
                'elementos_menu.pantalla_id', 'elementos_menu.etiqueta', 'elementos_menu.icono',
                'elementos_menu.tipo', 'elementos_menu.orden', 'pantallas.ruta',
                'pantallas.habilitada as pantalla_habilitada', 'modulos.codigo as modulo_codigo',
                'modulos.habilitado as modulo_habilitado', 'permisos.codigo as permiso_codigo',
            )
            ->orderBy('elementos_menu.menu_id')
            ->orderBy('elementos_menu.orden')
            ->get()
            ->groupBy('menu_id');

        $groups = [];

        foreach ($menus as $menu) {
            $rows = $elementos->get($menu->id, collect())->keyBy('id');
            $children = $rows->groupBy('padre_id');

            foreach ($children->get(null, collect()) as $root) {
                if ($root->tipo === 'grupo') {
                    $groups[] = $this->makeGroup($root, $children, $user);
                } else {
                    $item = $this->makeItem($root, $user);
                    if ($item !== null) {
                        $groups[] = [
                            'id' => 'item-'.$root->id,
                            'label' => $item['label'],
                            'icon' => $item['icon'],
                            'items' => [$item],
                            'direct' => true,
                        ];
                    }
                }
            }
        }

        $groups = $this->mergeGroups($groups);

        return ['dinamico' => true, 'grupos' => $groups];
    }

    private function schemaAvailable(): bool
    {
        foreach (['roles', 'usuarios_roles', 'usuarios_sedes_roles', 'roles_menus', 'menus', 'elementos_menu', 'pantallas', 'permisos'] as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int,int> */
    private function roleIdsFor(User $user): array
    {
        $globalIds = DB::table('usuarios_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->where('usuarios_roles.activo', true)
            ->where('roles.activo', true)
            ->where(function ($query): void {
                $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now());
            })
            ->pluck('roles.id');

        $siteId = $this->autorizacion->activeSiteIdFor($user);
        $siteIds = $siteId === null ? collect() : DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->where('usuarios_sedes_roles.sede_id', $siteId)
            ->where('usuarios_sedes_roles.activo', true)
            ->where('roles.activo', true)
            ->where(function ($query): void {
                $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
            })
            ->pluck('roles.id');

        $ids = $globalIds->merge($siteIds)->unique()->map(static fn (mixed $id): int => (int) $id)->values()->all();

        return $ids;
    }

    /** @param array<int,array<string,mixed>> $groups
     * @return array<int,array<string,mixed>>
     */
    private function mergeGroups(array $groups): array
    {
        $merged = [];

        foreach ($groups as $group) {
            if ($group['items'] === []) {
                continue;
            }

            $key = mb_strtolower(trim((string) $group['label']));
            if (! isset($merged[$key])) {
                $merged[$key] = $group;

                continue;
            }

            foreach ($group['items'] as $item) {
                $duplicate = collect($merged[$key]['items'])->contains(static fn (array $existing): bool => $existing['label'] === $item['label'] && $existing['route'] === $item['route']);
                if (! $duplicate) {
                    $merged[$key]['items'][] = $item;
                }
            }
        }

        return array_values($merged);
    }

    /** @param Collection<int,Collection<int,object>> $children
     * @param  Collection<int,object>  $rows
     * @return array<string,mixed>
     */
    private function makeGroup(object $root, $children, User $user): array
    {
        $items = [];
        foreach ($children->get($root->id, collect()) as $child) {
            $item = $this->makeItem($child, $user);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        return [
            'id' => 'group-'.$root->id,
            'label' => (string) $root->etiqueta,
            'icon' => (string) ($root->icono ?: 'bi-grid'),
            'items' => $items,
        ];
    }

    /** @return array<string,mixed>|null */
    private function makeItem(object $row, User $user): ?array
    {
        if (is_string($row->permiso_codigo) && ! $this->autorizacion->allows($user, $row->permiso_codigo)) {
            return null;
        }

        if (is_string($row->ruta)
            && $row->ruta !== 'dashboard'
            && ! str_starts_with($row->ruta, 'management.account')
            && (! is_string($row->modulo_codigo) || ! (bool) $row->modulo_habilitado)) {
            return null;
        }

        if (is_string($row->ruta)
            && $row->ruta !== 'dashboard'
            && ! str_starts_with($row->ruta, 'management.account')
            && ! is_string($row->permiso_codigo)
            && (! is_string($row->modulo_codigo) || ! $this->autorizacion->allows($user, 'gestion.'.$row->modulo_codigo.'.consultar'))) {
            return null;
        }

        $route = is_string($row->ruta) ? $row->ruta : null;
        $enabled = (bool) $row->pantalla_habilitada && $route !== null && Route::has($route);

        return [
            'label' => (string) $row->etiqueta,
            'icon' => (string) ($row->icono ?: 'bi-circle'),
            'route' => $enabled ? $route : null,
            'enabled' => $enabled,
            'pending' => ! $enabled,
            'active' => $enabled && $this->activeRoute($route),
        ];
    }

    private function activeRoute(string $route): bool
    {
        if ($route === 'management.inventory') {
            return request()->routeIs('management.inventory', 'management.inventory.movements');
        }

        return request()->routeIs($route);
    }
}
