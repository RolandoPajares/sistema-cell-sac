<?php

namespace App\Servicios\Compartidos\Seguridad;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Resuelve permisos RBAC, roles, alcances organizacionales y permisos derivados de rutas.
 */
final class ServicioAutorizacion
{
    private const REQUIRED_TABLES = [
        'roles', 'permisos', 'roles_permisos', 'usuarios_roles', 'usuarios_sedes_roles',
        'menus', 'roles_menus', 'elementos_menu', 'modulos', 'pantallas',
    ];

    /** @var array<string, string> */
    private const ROUTE_MODULES = [
        'dashboard' => 'panel', 'products' => 'productos', 'categories' => 'categorias', 'brands' => 'productos',
        'inventory' => 'inventario', 'sales' => 'ventas', 'orders' => 'pedidos',
        'purchases' => 'compras', 'quotations' => 'cotizaciones', 'customers' => 'clientes',
        'suppliers' => 'proveedores', 'returns' => 'devoluciones', 'support' => 'soporte',
        'campaigns' => 'campanias', 'coupons' => 'cupones', 'segments' => 'segmentos',
        'reports' => 'reportes', 'audit' => 'auditoria', 'users' => 'seguridad',
        'roles_permissions' => 'seguridad', 'permissions_matrix' => 'seguridad', 'user_assignments' => 'seguridad',
        'menus' => 'seguridad', 'modules' => 'seguridad', 'security_sessions' => 'seguridad',
        'payments' => 'pagos', 'billing' => 'facturacion', 'refunds' => 'reembolsos',
        'notifications' => 'notificaciones', 'integrations' => 'integraciones',
        'backups' => 'respaldos', 'company_settings' => 'organizacion',
        'commercial_settings' => 'configuracion', 'security_settings' => 'configuracion',
        'general_settings' => 'configuracion', 'financial_reports' => 'reportes',
        'customer_analytics' => 'analitica', 'marketing_metrics' => 'analitica',
    ];

    /** @var array<string, string> */
    private const ACTIONS = [
        'store' => 'crear', 'create' => 'crear', 'update' => 'actualizar', 'edit' => 'actualizar',
        'deactivate' => 'eliminar', 'destroy' => 'eliminar', 'delete' => 'eliminar',
        'export' => 'exportar', 'adjust' => 'ajustar', 'approve' => 'aprobar', 'cancel' => 'anular',
        'assign' => 'asignar', 'revoke' => 'revocar', 'activate' => 'activar',
        'receive' => 'recibir', 'receive_partial' => 'recibir', 'transfer_approve' => 'aprobar',
        'transfer_dispatch' => 'transferir',
        'emit' => 'emitir', 'restore' => 'restaurar',
        'confirm' => 'crear', 'convert_quotation' => 'crear', 'import' => 'crear',
    ];

    /**
     * Comprueba que existan las tablas mínimas necesarias para consultar RBAC.
     *
     * @return bool Verdadero cuando la fachada de esquema está disponible y todas las tablas requeridas existen.
     */
    public function schemaAvailable(): bool
    {
        if (Schema::getFacadeRoot() === null) {
            return false;
        }

        foreach (self::REQUIRED_TABLES as $table) {
            if (! Schema::hasTable($table)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica que el esquema RBAC contenga roles y permisos activos sembrados.
     *
     * @return bool Verdadero cuando el catálogo está disponible y tiene datos activos.
     */
    public function isSeeded(): bool
    {
        return $this->schemaAvailable()
            && DB::table('roles')->where('activo', true)->exists()
            && DB::table('permisos')->where('activo', true)->exists();
    }

    /**
     * Comprueba una asignación vigente de rol global o restringida a la sede activa.
     *
     * @param  User  $user  Usuario cuya asignación se consulta.
     * @param  string  $roleCode  Código del rol.
     * @return bool Verdadero si el rol existe, está activo y está vigente para el usuario.
     */
    public function hasRole(User $user, string $roleCode): bool
    {
        if (! $this->isSeeded()) {
            return false;
        }

        $role = DB::table('roles')->where('codigo', $roleCode)->where('activo', true)->first(['id', 'alcance_permitido']);

        if ($role === null) {
            return false;
        }

        $activeAssignment = $role->alcance_permitido !== 'sede' && DB::table('usuarios_roles')
            ->where('usuario_id', $user->getKey())
            ->where('rol_id', $role->id)
            ->where('activo', true)
            ->where(function ($query): void {
                $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>', now());
            })
            ->exists();

        if ($activeAssignment) {
            return true;
        }

        $siteId = $this->idSedeActivaInterna($user);
        $siteAssignment = $role->alcance_permitido === 'sede' && $siteId !== null && DB::table('usuarios_sedes_roles')
            ->where('usuario_id', $user->getKey())
            ->where('sede_id', $siteId)
            ->where('rol_id', $role->id)
            ->where('activo', true)
            ->where(function ($query): void {
                $query->whereNull('vigente_hasta')->orWhere('vigente_hasta', '>', now());
            })
            ->exists();

        if ($siteAssignment) {
            return true;
        }

        return false;
    }

    /**
     * Comprueba si el usuario posee un permiso vigente en su alcance global o de sede.
     *
     * Devuelve falso si el catálogo RBAC no está sembrado y, en caso contrario, considera
     * roles activos, asignaciones vigentes, módulos habilitados y pantallas autorizadas.
     *
     * @param  User  $user  Usuario cuya asignación RBAC se consulta.
     * @param  string  $permissionCode  Código del permiso requerido.
     * @return bool Verdadero cuando el permiso está vigente en el alcance aplicable.
     */
    public function allows(User $user, string $permissionCode): bool
    {
        if (! $this->isSeeded()) {
            return false;
        }

        return in_array($permissionCode, $this->codigosPermisos($user), true);
    }

    /**
     * Comprueba si el usuario conserva al menos un permiso efectivo vigente.
     */
    public function hasAnyPermission(User $user): bool
    {
        return $this->isSeeded() && $this->codigosPermisos($user) !== [];
    }

    /**
     * Hace cumplir un permiso RBAC y detiene la solicitud cuando no está autorizado.
     *
     * Delega la comprobación a {@see allows()} y produce HTTP 403 cuando el usuario
     * no tiene el código activo en su alcance.
     *
     * @param  User  $user  Usuario que solicita la operación.
     * @param  string  $permissionCode  Código del permiso requerido.
     * @return void No devuelve datos cuando la autorización es válida.
     *
     * @throws HttpException Si el permiso no está autorizado.
     */
    public function assertAllowed(User $user, string $permissionCode): void
    {
        abort_unless($this->allows($user, $permissionCode), 403);
    }

    /** @return array{empresa_id:?int,sede_id:?int,almacen_id:?int} */
    public function operationLocation(User $user): array
    {
        $site = $this->operationSite($user);
        $location = $site + ['almacen_id' => null];
        if ($site['sede_id'] === null) {
            return $location;
        }

        if (Schema::hasTable('almacenes')) {
            $warehouses = DB::table('almacenes')
                ->where('sede_id', $site['sede_id'])
                ->where('activo', true)
                ->orderBy('id')
                ->limit(2)
                ->pluck('id');
            abort_if($warehouses->count() > 1, 409, 'Selecciona un almacén autorizado antes de continuar.');
            $location['almacen_id'] = $warehouses->isEmpty() ? null : (int) $warehouses->first();
        }

        return $location;
    }

    /** @return array{empresa_id:?int,sede_id:?int} */
    public function operationSite(User $user): array
    {
        $siteLocation = ['empresa_id' => null, 'sede_id' => null];

        if (! Schema::hasTable('sedes') || ! Schema::hasTable('empresas')) {
            return $siteLocation;
        }

        $siteId = $this->idSedeActivaInterna($user);
        if ($siteId === null && $this->allowsGlobal($user, 'gestion.organizacion.consultar')) {
            $siteId = $this->idSedeActivaUnica();
        }
        if ($siteId === null && $this->allows($user, 'gestion.pedidos.consultar')) {
            $siteId = $this->idSedeActivaUnica();
        }

        if ($siteId === null) {
            abort_if($this->isSeeded(), 403, 'Selecciona una sede autorizada para registrar esta operación.');

            return $siteLocation;
        }

        $site = DB::table('sedes')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('sedes.id', $siteId)
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->first(['sedes.id', 'sedes.empresa_id']);

        abort_unless($site !== null, 403, 'La sede seleccionada no está habilitada.');

        return ['empresa_id' => (int) $site->empresa_id, 'sede_id' => (int) $site->id];
    }

    /**
     * Expone el identificador de la sede activa que corresponde al usuario.
     *
     * @param  User  $user  Usuario cuya sede se resuelve.
     * @return int|null Identificador de sede autorizado o null si no hay una única sede válida.
     */
    public function activeSiteIdFor(User $user): ?int
    {
        return $this->idSedeActivaInterna($user);
    }

    /**
     * Devuelve la empresa activa cuando el sistema tiene una única empresa habilitada.
     *
     * @return int|null Identificador de empresa o null si no puede determinarse uno único.
     */
    public function activeCompanyId(): ?int
    {
        return $this->idEmpresaActivaUnica();
    }

    /** @return array<string, int|null> */
    public function operationScopeAttributes(User $user, string $table): array
    {
        if (! Schema::hasTable($table)) {
            return [];
        }

        $location = $this->operationLocation($user);

        return array_filter(
            $location,
            static fn (string $column): bool => Schema::hasColumn($table, $column),
            ARRAY_FILTER_USE_KEY,
        );
    }

    /**
     * Comprueba permiso y alcance de una operación sobre empresa, sede o propietario.
     *
     * @param  User  $user  Usuario que solicita el acceso.
     * @param  string  $permissionCode  Código del permiso.
     * @param  int|null  $companyId  Empresa del registro consultado.
     * @param  int|null  $siteId  Sede del registro consultado.
     * @param  int|null  $ownerId  Propietario del registro cuando aplica.
     * @return bool Verdadero si el usuario puede operar sobre el registro indicado.
     */
    public function allowsRecord(
        User $user,
        string $permissionCode,
        ?int $companyId,
        ?int $siteId,
        ?int $ownerId = null,
    ): bool {
        if (! $this->isSeeded()) {
            return false;
        }

        if (! $this->allows($user, $permissionCode)) {
            return false;
        }

        if ($siteId !== null && $companyId !== null) {
            $siteCompanyId = DB::table('sedes')->where('id', $siteId)->value('empresa_id');

            if ($siteCompanyId === null || (int) $siteCompanyId !== $companyId) {
                return false;
            }
        }

        if ($this->allowsGlobal($user, $permissionCode)) {
            return $companyId !== null && $companyId === $this->idEmpresaActivaUnica();
        }

        if ($ownerId === (int) $user->getKey() && $this->tienePermisoEnAlcance($user, $permissionCode, 'propio')) {
            return true;
        }

        if ($siteId === null || $companyId === null) {
            return false;
        }

        $selectedSiteId = request()->hasSession() ? request()->session()->get('sede_actual_id') : null;

        if (is_numeric($selectedSiteId) && (int) $selectedSiteId !== $siteId) {
            return false;
        }

        return in_array($siteId, $this->idsSedesConPermiso($user, $permissionCode), true);
    }

    /** @return array<int, int> */
    public function authorizedSiteIds(User $user, string $permissionCode): array
    {
        if (! $this->allows($user, $permissionCode)) {
            return [];
        }

        if ($this->allowsGlobal($user, $permissionCode)) {
            $companyId = $this->idEmpresaActivaUnica();

            $siteIds = $companyId === null ? [] : DB::table('sedes')
                ->where('empresa_id', $companyId)->where('activa', true)
                ->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();
        } else {
            $siteIds = $this->idsSedesConPermiso($user, $permissionCode);
        }

        $selectedSiteId = request()->hasSession() ? request()->session()->get('sede_actual_id') : null;

        return is_numeric($selectedSiteId)
            ? array_values(array_filter($siteIds, static fn (int $siteId): bool => $siteId === (int) $selectedSiteId))
            : $siteIds;
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeRecordsFor(Builder $query, User $user, string $permissionCode, ?string $ownerColumn = null): Builder
    {
        if (! $this->isSeeded()) {
            return $query->whereRaw('1 = 0');
        }

        if (! $this->allows($user, $permissionCode)) {
            return $query->whereRaw('1 = 0');
        }

        $table = $query->getModel()->getTable();

        if ($this->allowsGlobal($user, $permissionCode)) {
            $companyId = $this->idEmpresaActivaUnica();
            if ($companyId === null || ! Schema::hasColumn($table, 'empresa_id')) {
                return $query->whereRaw('1 = 0');
            }

            $query->where($table.'.empresa_id', $companyId);
            if (Schema::hasColumn($table, 'sede_id')) {
                $query->where(function (Builder $companyQuery) use ($table): void {
                    $companyQuery->whereNull($table.'.sede_id')
                        ->orWhereExists(function ($siteQuery) use ($table): void {
                            $siteQuery->selectRaw('1')->from('sedes as scope_sedes')
                                ->whereColumn('scope_sedes.id', $table.'.sede_id')
                                ->whereColumn('scope_sedes.empresa_id', $table.'.empresa_id');
                        });
                });
            }

            return $query;
        }

        if (! Schema::hasColumn($table, 'sede_id')) {
            return $query->whereRaw('1 = 0');
        }

        $siteIds = $this->idsSedesConPermiso($user, $permissionCode);
        $hasOwnScope = $ownerColumn !== null
            && Schema::hasColumn($table, $ownerColumn)
            && $this->tienePermisoEnAlcance($user, $permissionCode, 'propio');

        if ($siteIds === [] && ! $hasOwnScope) {
            return $query->whereRaw('1 = 0');
        }

        $selectedSiteId = request()->hasSession() ? request()->session()->get('sede_actual_id') : null;
        if (is_numeric($selectedSiteId)) {
            $siteIds = array_values(array_filter($siteIds, static fn (int $siteId): bool => $siteId === (int) $selectedSiteId));
        }
        if ($siteIds === [] && ! $hasOwnScope) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $scopedQuery) use ($table, $siteIds, $hasOwnScope, $ownerColumn, $user): void {
            if ($siteIds !== []) {
                $scopedQuery->whereIn($table.'.sede_id', $siteIds);

                if (Schema::hasColumn($table, 'empresa_id')) {
                    $scopedQuery->whereExists(function ($siteQuery) use ($table): void {
                        $siteQuery->selectRaw('1')
                            ->from('sedes as scope_sedes')
                            ->whereColumn('scope_sedes.id', $table.'.sede_id')
                            ->whereColumn('scope_sedes.empresa_id', $table.'.empresa_id');
                    });
                }

            }

            if ($hasOwnScope && $ownerColumn !== null) {
                if ($siteIds !== []) {
                    $scopedQuery->orWhere($table.'.'.$ownerColumn, $user->getKey());
                } else {
                    $scopedQuery->where($table.'.'.$ownerColumn, $user->getKey());
                }
            }
        });
    }

    /**
     * Comprueba que un permiso esté asignado al usuario con alcance de empresa.
     *
     * @param  User  $user  Usuario que solicita el permiso.
     * @param  string  $permissionCode  Código del permiso.
     * @return bool Verdadero si existe una asignación global activa y vigente.
     */
    public function allowsGlobal(User $user, string $permissionCode): bool
    {
        if (! $this->isSeeded() || ! $this->allows($user, $permissionCode) || $this->idEmpresaActivaUnica() === null) {
            return false;
        }

        return DB::table('usuarios_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->where('usuarios_roles.activo', true)
            ->where('roles.activo', true)
            ->where('permisos.activo', true)
            ->where('permisos.codigo', $permissionCode)
            ->where('roles_permisos.alcance', 'empresa')
            ->where(function ($query): void {
                $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now());
            })
            ->exists();
    }

    /**
     * Resuelve el permiso de la ruta y valida módulo, pantalla y usuario actual.
     *
     * También admite rutas de cuenta explícitamente públicas para este middleware.
     *
     * @param  Request  $request  Solicitud cuya ruta y usuario se autorizarán.
     * @return bool Verdadero si la ruta queda autorizada por RBAC.
     */
    public function allowsRoute(Request $request): bool
    {
        $routeName = $request->route()?->getName();
        if (is_string($routeName) && ($routeName === 'management.account' || Str::startsWith($routeName, 'management.account.') || Str::startsWith($routeName, 'legacy.admin.account'))) {
            return true;
        }

        if (str_contains((string) $request->route()?->getActionName(), 'Illuminate\\Routing\\ViewController')) {
            return false;
        }

        $permissionCode = $this->permissionForRoute($request);

        if ($permissionCode === null || ! ($request->user() instanceof User)) {
            return false;
        }

        if (! $this->isSeeded()) {
            return false;
        }

        $canonicalRoute = $routeName;
        if (is_string($canonicalRoute) && Str::startsWith($canonicalRoute, 'legacy.admin.')) {
            $canonicalRoute = 'management.'.Str::after($canonicalRoute, 'legacy.admin.');
        }

        if (is_string($canonicalRoute)) {
            $codigoModulo = explode('.', $permissionCode)[1] ?? null;
            if ($codigoModulo === null) {
                return false;
            }

            $pantallasCoincidentes = DB::table('pantallas')
                ->join('modulos', 'modulos.id', '=', 'pantallas.modulo_id')
                ->where('pantallas.ruta', $canonicalRoute)
                ->where('modulos.codigo', $codigoModulo)
                ->get(['pantallas.codigo', 'pantallas.habilitada', 'modulos.habilitado']);

            if ($pantallasCoincidentes->isNotEmpty()) {
                $pantallasHabilitadas = $pantallasCoincidentes->filter(static fn (object $pantalla): bool => (bool) $pantalla->habilitada && (bool) $pantalla->habilitado);

                if ($pantallasHabilitadas->isEmpty()) {
                    return false;
                }

                $tienePermisoDePantalla = $pantallasHabilitadas->contains(fn (object $pantalla): bool => $this->allows($request->user(), 'pantalla.'.$pantalla->codigo.'.consultar'));

                if (! $tienePermisoDePantalla) {
                    return false;
                }
            }
        }

        return $this->allows($request->user(), $permissionCode);
    }

    /**
     * Traduce el nombre de una ruta de gestión a su código de permiso canónico.
     *
     * Normaliza prefijos legacy y acciones HTTP mediante el mapa de acciones del servicio.
     *
     * @param  Request  $request  Solicitud cuya ruta se inspeccionará.
     * @return string|null Código de permiso o null para rutas no gestionadas.
     */
    public function permissionForRoute(Request $request): ?string
    {
        $routeName = $request->route()?->getName();

        if (! is_string($routeName)) {
            return null;
        }

        if ($routeName === 'smart.ads' || $routeName === 'legacy.ads') {
            return 'gestion.campanias.consultar';
        }

        if (Str::startsWith($routeName, 'management.account.') || in_array($routeName, ['management.account', 'management.account.avatar'], true)) {
            return null;
        }

        if (Str::startsWith($routeName, 'legacy.admin.')) {
            $routeName = Str::after($routeName, 'legacy.admin.');
        } elseif (Str::startsWith($routeName, 'management.')) {
            $routeName = Str::after($routeName, 'management.');
        } else {
            return null;
        }

        $parts = explode('.', $routeName);
        $routeModule = array_shift($parts);
        $module = self::ROUTE_MODULES[$routeModule] ?? null;

        if ($module === null) {
            return null;
        }

        $action = 'consultar';
        foreach (array_reverse($parts) as $part) {
            if (isset(self::ACTIONS[$part])) {
                $action = self::ACTIONS[$part];
                break;
            }
        }

        $transferActions = [
            'inventory.transfer_request' => 'transferir',
            'inventory.transfer_approve' => 'aprobar',
            'inventory.transfer_dispatch' => 'transferir',
            'inventory.transfer_receive' => 'transferir',
            'inventory.transfer_return' => 'transferir',
            'inventory.transfer_reconcile' => 'transferir',
            'inventory.transfer_reject' => 'aprobar',
            'inventory.transfer_cancel' => 'transferir',
            'inventory.transfer_close' => 'transferir',
        ];

        $action = $transferActions[$routeName] ?? $action;

        if ($routeName === 'purchases.receive_partial') {
            $action = 'recibir';
        }

        if ($routeModule === 'company_settings'
            && (str_starts_with($routeName, 'company_settings.warehouses.') || str_starts_with($routeName, 'company_settings.sites.'))
            && in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $action = 'actualizar';
        }

        if ($routeModule === 'account') {
            $module = 'cuenta';
            $action = in_array($request->method(), ['PUT', 'PATCH', 'POST'], true) ? 'actualizar' : 'consultar';
        }

        if ($routeModule === 'user_assignments') {
            $module = 'seguridad';
            $action = Str::contains($routeName, 'revoke') ? 'revocar' : ($request->isMethod('GET') ? 'consultar' : 'asignar');
        }

        if ($routeModule === 'menus' || $routeModule === 'modules' || $routeModule === 'roles_permissions') {
            $module = 'seguridad';
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true) && $action === 'consultar') {
                $action = $routeModule === 'roles_permissions' && $request->isMethod('POST') ? 'crear' : 'actualizar';
            }
        }

        if ($routeModule === 'security_sessions') {
            $module = 'seguridad';
            $action = $request->isMethod('GET') ? 'consultar' : 'revocar';
        }

        if ($routeModule === 'integrations' && in_array($routeName, ['integrations.store', 'integrations.update'], true)) {
            $action = 'configurar';
        }

        if ($routeModule === 'backups' && $routeName === 'backups.store') {
            $action = 'crear';
        }

        if ($routeModule === 'products' && $routeName === 'products.wholesale_prices.import') {
            $action = 'actualizar';
        }

        // Segmentos no tenía permiso de exportación en el catálogo RBAC legado.
        // Exportar no amplía el alcance de lectura: conserva el permiso de consulta
        // hasta que el permiso específico sea administrado desde la matriz gráfica.
        if ($routeModule === 'segments' && $routeName === 'segments.export') {
            $action = 'consultar';
        }

        return 'gestion.'.$module.'.'.$action;
    }

    /** @return array<int, int> */
    public function permissionIds(User $user): array
    {
        if (! $this->isSeeded()) {
            return [];
        }

        $permissionCodes = $this->codigosPermisos($user);

        if ($permissionCodes === []) {
            return [];
        }

        return DB::table('permisos')
            ->whereIn('codigo', $permissionCodes)
            ->pluck('id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Indica si el usuario está sujeto a asignaciones RBAC controladas.
     *
     * @param  User  $user  Usuario consultado.
     * @return bool Verdadero cuando el esquema está sembrado y el indicador está activo.
     */
    public function hasControlledAssignments(User $user): bool
    {
        return $this->isSeeded() && (bool) $user->getAttribute('acceso_controlado');
    }

    /**
     * Resuelve la sede de sesión si sigue autorizada o una única sede asignada.
     *
     * @param  User  $user  Usuario cuya sede se valida.
     * @return int|null Sede activa autorizada o null cuando hay cero o varias opciones.
     */
    private function idSedeActivaInterna(User $user): ?int
    {
        $sessionSiteId = request()->hasSession() ? request()->session()->get('sede_actual_id') : null;

        if (is_numeric($sessionSiteId)) {
            $siteId = (int) $sessionSiteId;

            if (! $this->sedeActiva($siteId)) {
                return null;
            }

            $hasGlobalAdministrator = $this->tienePermisoEnAlcance(
                $user,
                'gestion.organizacion.consultar',
                'empresa',
            );

            $hasSiteAssignment = DB::table('usuarios_sedes_roles')
                ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
                ->join('sedes', 'sedes.id', '=', 'usuarios_sedes_roles.sede_id')
                ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
                ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
                ->where('usuarios_sedes_roles.sede_id', $siteId)
                ->where('usuarios_sedes_roles.activo', true)
                ->where('roles.activo', true)
                ->where('sedes.activa', true)
                ->where('empresas.activo', true)
                ->where(function ($query): void {
                    $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
                })
                ->exists();

            return $hasGlobalAdministrator || $hasSiteAssignment ? $siteId : null;
        }

        if (! $this->isSeeded()) {
            return null;
        }

        $siteIds = DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->join('sedes', 'sedes.id', '=', 'usuarios_sedes_roles.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->where('usuarios_sedes_roles.activo', true)
            ->where('roles.activo', true)
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->where(function ($query): void {
                $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
            })
            ->distinct()
            ->limit(2)
            ->pluck('usuarios_sedes_roles.sede_id');

        return $siteIds->count() === 1 ? (int) $siteIds->first() : null;
    }

    /**
     * Comprueba que una sede y su empresa estén activas.
     *
     * @param  int  $siteId  Identificador de la sede.
     * @return bool Verdadero cuando ambas entidades existen y están habilitadas.
     */
    private function sedeActiva(int $siteId): bool
    {
        return Schema::hasTable('sedes')
            && Schema::hasTable('empresas')
            && DB::table('sedes')
                ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
                ->where('sedes.id', $siteId)
                ->where('sedes.activa', true)
                ->where('empresas.activo', true)
                ->exists();
    }

    /**
     * Busca un permiso activo en asignaciones globales o de sede para un alcance dado.
     *
     * @param  User  $user  Usuario cuya asignación se consulta.
     * @param  string  $permissionCode  Código del permiso.
     * @param  string  $scope  Alcance RBAC que debe coincidir.
     * @return bool Verdadero si existe una asignación activa y vigente.
     */
    private function tienePermisoEnAlcance(User $user, string $permissionCode, string $scope): bool
    {
        $permissionId = DB::table('permisos')
            ->where('codigo', $permissionCode)
            ->where('activo', true)
            ->value('id');

        if ($permissionId === null) {
            return false;
        }

        $globalAssignment = DB::table('usuarios_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->where('usuarios_roles.activo', true)
            ->where('roles.activo', true)
            ->where('roles_permisos.permiso_id', $permissionId)
            ->where('roles_permisos.alcance', $scope)
            ->where(function ($query): void {
                $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now());
            })
            ->exists();

        if ($globalAssignment) {
            return true;
        }

        return DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->join('sedes', 'sedes.id', '=', 'usuarios_sedes_roles.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->where('usuarios_sedes_roles.activo', true)
            ->where('roles.activo', true)
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->where('roles_permisos.permiso_id', $permissionId)
            ->where('roles_permisos.alcance', $scope)
            ->where(function ($query): void {
                $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
            })
            ->exists();
    }

    /** @return array<int, int> */
    private function idsSedesConPermiso(User $user, string $permissionCode): array
    {
        return DB::table('usuarios_sedes_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
            ->join('sedes', 'sedes.id', '=', 'usuarios_sedes_roles.sede_id')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->join('roles_permisos', 'roles_permisos.rol_id', '=', 'roles.id')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->leftJoin('pantallas', 'pantallas.id', '=', 'permisos.pantalla_id')
            ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
            ->where('usuarios_sedes_roles.activo', true)
            ->where('roles.activo', true)
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->where('roles_permisos.alcance', 'sede')
            ->where('permisos.codigo', $permissionCode)
            ->where('permisos.activo', true)
            ->where('modulos.habilitado', true)
            ->where(function ($query): void {
                $query->whereNull('permisos.pantalla_id')->orWhere('pantallas.habilitada', true);
            })
            ->where(function ($query): void {
                $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
            })
            ->distinct()
            ->pluck('usuarios_sedes_roles.sede_id')
            ->map(static fn (mixed $siteId): int => (int) $siteId)
            ->values()
            ->all();
    }

    /**
     * Obtiene la única sede activa cuando el catálogo no ofrece más de una.
     *
     * @return int|null Identificador único o null si hay cero o varias sedes.
     */
    private function idSedeActivaUnica(): ?int
    {
        if (! Schema::hasTable('sedes')) {
            return null;
        }

        $activeSiteIds = DB::table('sedes')
            ->join('empresas', 'empresas.id', '=', 'sedes.empresa_id')
            ->where('sedes.activa', true)
            ->where('empresas.activo', true)
            ->orderBy('sedes.id')
            ->limit(2)
            ->pluck('sedes.id');

        return $activeSiteIds->count() === 1 ? (int) $activeSiteIds->first() : null;
    }

    /**
     * Obtiene la única empresa activa cuando el catálogo no ofrece más de una.
     *
     * @return int|null Identificador único o null si hay cero o varias empresas.
     */
    private function idEmpresaActivaUnica(): ?int
    {
        if (! Schema::hasTable('empresas')) {
            return null;
        }

        $companies = DB::table('empresas')->where('activo', true)->orderBy('id')->limit(2)->pluck('id');

        return $companies->count() === 1 ? (int) $companies->first() : null;
    }

    /** @return array<int, string> */
    private function codigosPermisos(User $user): array
    {
        $siteId = $this->idSedeActivaInterna($user);

        $globalRoleIds = DB::table('usuarios_roles')
            ->join('roles', 'roles.id', '=', 'usuarios_roles.rol_id')
            ->where('usuarios_roles.usuario_id', $user->getKey())
            ->where('usuarios_roles.activo', true)
            ->where('roles.activo', true)
            ->when($this->idEmpresaActivaUnica() === null, fn ($query) => $query->where('roles.alcance_permitido', '!=', 'empresa'))
            ->where(function ($query): void {
                $query->whereNull('usuarios_roles.vigente_hasta')->orWhere('usuarios_roles.vigente_hasta', '>', now());
            })
            ->pluck('roles.id');

        $siteRoleIds = collect();

        if ($siteId !== null) {
            $siteRoleIds = DB::table('usuarios_sedes_roles')
                ->join('roles', 'roles.id', '=', 'usuarios_sedes_roles.rol_id')
                ->where('usuarios_sedes_roles.usuario_id', $user->getKey())
                ->where('usuarios_sedes_roles.sede_id', $siteId)
                ->where('usuarios_sedes_roles.activo', true)
                ->where('roles.activo', true)
                ->where(function ($query): void {
                    $query->whereNull('usuarios_sedes_roles.vigente_hasta')->orWhere('usuarios_sedes_roles.vigente_hasta', '>', now());
                })
                ->pluck('roles.id');
        }

        if ($globalRoleIds->isEmpty() && $siteRoleIds->isEmpty()) {
            return [];
        }

        $permissions = DB::table('roles_permisos')
            ->join('permisos', 'permisos.id', '=', 'roles_permisos.permiso_id')
            ->join('modulos', 'modulos.id', '=', 'permisos.modulo_id')
            ->leftJoin('pantallas', 'pantallas.id', '=', 'permisos.pantalla_id')
            ->where('permisos.activo', true)
            ->where('modulos.habilitado', true)
            ->where(function ($query): void {
                $query->whereNull('permisos.pantalla_id')->orWhere('pantallas.habilitada', true);
            })
            ->where(function ($query) use ($globalRoleIds, $siteRoleIds): void {
                if ($globalRoleIds->isNotEmpty()) {
                    $query->where(function ($globalQuery) use ($globalRoleIds): void {
                        $globalQuery->whereIn('roles_permisos.rol_id', $globalRoleIds)
                            ->whereIn('roles_permisos.alcance', ['empresa', 'propio']);
                    });
                }

                if ($siteRoleIds->isNotEmpty()) {
                    $query->orWhere(function ($siteQuery) use ($siteRoleIds): void {
                        $siteQuery->whereIn('roles_permisos.rol_id', $siteRoleIds)
                            ->where('roles_permisos.alcance', 'sede');
                    });
                }
            })
            ->distinct()
            ->pluck('permisos.codigo')
            ->all();

        return $permissions;
    }
}
