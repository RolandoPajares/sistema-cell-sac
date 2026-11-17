<?php

namespace App\Servicios\Compartidos\Seguridad;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

/**
 * Define la responsabilidad de la clase ServicioMenuDinamico dentro de su módulo.
 *
 * La clase mantiene la responsabilidad que le corresponde dentro de la arquitectura actual.
 */
final class ServicioMenuDinamico
{
    /** @var array<int, string> Rutas administrativas retiradas de la navegación visible. */
    private const RUTAS_NO_VISIBLES = [
        'management.company_settings.sites',
    ];

    /**
     * Inicializa la instancia con las dependencias y el estado requeridos.
     *
     * @param  mixed  $autorizacion  Parámetro recibido por la operación.
     * @return mixed Resultado de la operación.
     */
    public function __construct(private readonly ServicioAutorizacion $autorizacion) {}

    /** @return array{dinamico:bool,grupos:array<int,array<string,mixed>>} */
    public function paraUsuario(?User $user): array
    {
        // ORM: consulta menus, elementos y roles mediante el constructor de consultas de Laravel.
        // Devuelve solo los menus y opciones permitidos para el usuario actual.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $user instanceof User) {
            return ['dinamico' => false, 'grupos' => []];
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (! $this->esquemaDisponible() || ! $this->autorizacion->isSeeded()) {
            return ['dinamico' => true, 'grupos' => []];
        }

        // Guarda en $roleIds el resultado necesario para continuar la operacion.
        $roleIds = $this->idsRolesPara($user);

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($roleIds === []) {
            return ['dinamico' => true, 'grupos' => []];
        }

        // Prepara en $menus la consulta que obtiene o modifica los datos necesarios.
        $menus = DB::table('menus')
            ->leftJoin('roles_menus', function ($join) use ($roleIds): void {
                $join->on('roles_menus.menu_id', '=', 'menus.id')->whereIn('roles_menus.rol_id', $roleIds);
            })
            ->whereNotNull('roles_menus.rol_id')
            ->where('menus.habilitado', true)
            ->where('menus.contexto', 'sidebar')
            ->select('menus.id', 'menus.codigo', 'menus.nombre')
            ->orderBy('menus.id')
            ->get();

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($menus->isEmpty()) {
            return ['dinamico' => true, 'grupos' => []];
        }

        // Prepara en $elementos la consulta que obtiene o modifica los datos necesarios.
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

        // Agrupa en $groups los datos que se utilizaran en el siguiente paso.
        $groups = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($menus as $menu) {
            // Guarda en $rows el resultado de la consulta ejecutada.
            $rows = $elementos->get($menu->id, collect())->keyBy('id');
            // Guarda en $children el resultado necesario para continuar la operacion.
            $children = $rows->groupBy('padre_id');

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($children->get(null, collect()) as $root) {
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
                if ($root->tipo === 'grupo') {
                    $groups[] = $this->crearGrupo($root, $children, $user);
                } else {
                    // Guarda en $item el resultado necesario para continuar la operacion.
                    $item = $this->crearElemento($root, $user);
                    // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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

        // Guarda en $groups el resultado necesario para continuar la operacion.
        $groups = $this->fusionarGrupos($groups);

        return ['dinamico' => true, 'grupos' => $groups];
    }

    /**
     * Ejecuta la operación schemaAvailable definida por la clase ServicioMenuDinamico.
     *
     * @return bool Resultado de la operación.
     */
    private function esquemaDisponible(): bool
    {
        // Comprueba que las tablas necesarias existan antes de consultar el menu.
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach (['roles', 'usuarios_roles', 'usuarios_sedes_roles', 'roles_menus', 'menus', 'elementos_menu', 'pantallas', 'permisos'] as $table) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    /** @return array<int,int> */
    private function idsRolesPara(User $user): array
    {
        // Reune los roles globales y los roles asignados por sede del usuario.
        // Prepara en $globalIds la consulta que obtiene o modifica los datos necesarios.
        $globalIds = DB::table('usuarios_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->where('usuarios_roles.activo', true)
            ->where('roles.activo', true)
            ->where(function ($query): void {
                $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now());
            })
            ->pluck('roles.id');

        // Guarda en $siteId el resultado necesario para continuar la operacion.
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
    private function fusionarGrupos(array $groups): array
    {
        // Combina grupos repetidos para que cada opcion aparezca una sola vez.
        // Agrupa en $merged los datos que se utilizaran en el siguiente paso.
        $merged = [];

        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($groups as $group) {
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if ($group['items'] === []) {
                continue;
            }

            // Guarda en $key el resultado necesario para continuar la operacion.
            $key = mb_strtolower(trim((string) $group['label']));
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
            if (! isset($merged[$key])) {
                $merged[$key] = $group;

                continue;
            }

            // Recorre los elementos recibidos para validar o procesar cada registro.
            foreach ($group['items'] as $item) {
                $duplicate = collect($merged[$key]['items'])->contains(static fn (array $existing): bool => $existing['label'] === $item['label'] && $existing['route'] === $item['route']);
                // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
    private function crearGrupo(object $root, $children, User $user): array
    {
        // Construye un grupo del menu y sus elementos hijos visibles.
        // Agrupa en $items los datos que se utilizaran en el siguiente paso.
        $items = [];
        // Recorre los elementos recibidos para validar o procesar cada registro.
        foreach ($children->get($root->id, collect()) as $child) {
            // Guarda en $item el resultado necesario para continuar la operacion.
            $item = $this->crearElemento($child, $user);
            // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
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
    private function crearElemento(object $row, User $user): ?array
    {
        // Convierte un registro de menu en la estructura que consume Blade.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_string($row->ruta) && in_array($row->ruta, self::RUTAS_NO_VISIBLES, true)) {
            return null;
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_string($row->ruta)
            && $row->ruta !== 'dashboard'
            && ! str_starts_with($row->ruta, 'management.account')
            && is_string($row->modulo_codigo)
            && ! $this->autorizacion->allows($user, 'gestion.'.$row->modulo_codigo.'.consultar')) {
            return null;
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_string($row->permiso_codigo) && ! $this->autorizacion->allows($user, $row->permiso_codigo)) {
            return null;
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_string($row->ruta)
            && $row->ruta !== 'dashboard'
            && ! str_starts_with($row->ruta, 'management.account')
            && (! is_string($row->modulo_codigo) || ! (bool) $row->modulo_habilitado)) {
            return null;
        }

        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if (is_string($row->ruta)
            && $row->ruta !== 'dashboard'
            && ! str_starts_with($row->ruta, 'management.account')
            && ! is_string($row->permiso_codigo)
            && (! is_string($row->modulo_codigo) || ! $this->autorizacion->allows($user, 'gestion.'.$row->modulo_codigo.'.consultar'))) {
            return null;
        }

        // Guarda en $route el resultado necesario para continuar la operacion.
        $route = is_string($row->ruta) ? $row->ruta : null;
        $enabled = (bool) $row->pantalla_habilitada && $route !== null && Route::has($route);

        return [
            'label' => (string) $row->etiqueta,
            'icon' => (string) ($row->icono ?: 'bi-circle'),
            'route' => $enabled ? $route : null,
            'enabled' => $enabled,
            'pending' => ! $enabled,
            'active' => $enabled && $this->rutaActiva($route),
        ];
    }

    /**
     * Ejecuta la operación activeRoute definida por la clase ServicioMenuDinamico.
     *
     * @param  mixed  $route  Parámetro recibido por la operación.
     * @return bool Resultado de la operación.
     */
    private function rutaActiva(string $route): bool
    {
        // Indica si la ruta del elemento coincide con la pagina actual.
        // Comprueba la condicion indicada antes de ejecutar este bloque del servicio.
        if ($route === 'management.inventory') {
            return request()->routeIs('management.inventory', 'management.inventory.movements');
        }

        return request()->routeIs($route);
    }
}
