<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RbacInicialSeeder extends Seeder
{
    private const TABLAS_REQUERIDAS = [
        'empresas', 'sedes', 'almacenes', 'modulos', 'pantallas', 'roles', 'menus',
        'permisos', 'roles_permisos', 'roles_menus', 'elementos_menu', 'usuarios_roles',
        'usuarios_sedes_roles', 'versiones_inicializacion_control_acceso',
    ];

    private const TABLAS_CATALOGO = [
        'permisos', 'modulos', 'pantallas', 'menus',
        'roles_permisos', 'roles_menus', 'elementos_menu',
    ];

    private const ALCANCES_ROL = [
        'administrador' => 'empresa',
        'compras_logistica' => 'sede',
        'ventas_mayoristas' => 'sede',
        'ventas_minoristas' => 'sede',
        'marketing' => 'sede',
        'cliente_mayorista' => 'propio',
        'cliente_minorista' => 'propio',
    ];

    /**
     * @return array{
     *     ya_inicializado:bool,
     *     puede_inicializar:bool,
     *     errores:array<int,string>,
     *     roles_existentes:int,
     *     catalogos:array<string,int>
     * }
     */
    public function diagnosticarPreparacion(): array
    {
        $tablasFaltantes = array_values(array_filter(
            self::TABLAS_REQUERIDAS,
            static fn (string $tabla): bool => ! Schema::hasTable($tabla),
        ));

        if ($tablasFaltantes !== []) {
            return [
                'ya_inicializado' => false,
                'puede_inicializar' => false,
                'errores' => ['Faltan tablas requeridas: '.implode(', ', $tablasFaltantes).'.'],
                'roles_existentes' => 0,
                'catalogos' => [],
            ];
        }

        $conteosCatalogo = [];
        foreach (self::TABLAS_CATALOGO as $tabla) {
            $conteosCatalogo[$tabla] = DB::table($tabla)->count();
        }

        $versionAplicada = DB::table('versiones_inicializacion_control_acceso')
            ->where('codigo', 'rbac-inicial-v1')
            ->exists();
        $errores = $this->erroresRolesExistentes();

        foreach ($conteosCatalogo as $tabla => $cantidad) {
            if ($versionAplicada && $cantidad === 0) {
                $errores[] = 'El marcador RBAC existe, pero el catálogo '.$tabla.' está vacío.';
            } elseif (! $versionAplicada && $cantidad > 0) {
                $errores[] = 'El catálogo '.$tabla.' ya contiene datos y requiere conciliación aprobada.';
            }
        }

        $yaInicializado = $versionAplicada && $errores === [];

        return [
            'ya_inicializado' => $yaInicializado,
            'puede_inicializar' => ! $versionAplicada && $errores === [],
            'errores' => $errores,
            'roles_existentes' => DB::table('roles')->count(),
            'catalogos' => $conteosCatalogo,
        ];
    }

    public function run(): void
    {
        $diagnostico = $this->diagnosticarPreparacion();
        if ($diagnostico['errores'] !== []) {
            throw new \RuntimeException(implode(' ', $diagnostico['errores']));
        }

        if ($diagnostico['ya_inicializado']) {
            return;
        }

        if (! $diagnostico['puede_inicializar']) {
            throw new \RuntimeException('El catálogo RBAC no está listo para inicializar.');
        }

        DB::transaction(function (): void {
            $diagnostico = $this->diagnosticarPreparacion();
            if ($diagnostico['errores'] !== []) {
                throw new \RuntimeException(implode(' ', $diagnostico['errores']));
            }

            if ($diagnostico['ya_inicializado']) {
                return;
            }

            if (! $diagnostico['puede_inicializar']) {
                throw new \RuntimeException('El catálogo RBAC no está listo para inicializar.');
            }

            if (DB::table('versiones_inicializacion_control_acceso')->where('codigo', 'rbac-inicial-v1')->exists()) {
                return;
            }

            foreach (['permisos', 'modulos', 'pantallas', 'menus', 'roles_permisos', 'roles_menus', 'elementos_menu'] as $table) {
                if (DB::table($table)->exists()) {
                    throw new \RuntimeException('La inicialización requiere catálogos RBAC vacíos o una conciliación aprobada: '.$table);
                }
            }

            $versionInserted = DB::table('versiones_inicializacion_control_acceso')->insertOrIgnore([
                'codigo' => 'rbac-inicial-v1',
                'aplicado_en' => now(),
            ]);

            if ($versionInserted !== 1) {
                return;
            }

            $roleIds = $this->seedRoles();
            $moduleIds = $this->seedModules();
            $screenIds = $this->seedScreens($moduleIds);
            $permissionIds = $this->seedPermissions($moduleIds, $screenIds);
            $this->seedRolePermissions($roleIds, $permissionIds);
            $this->seedMenus($roleIds, $screenIds, $permissionIds);
        });
    }

    /** @return array<string, int> */
    private function seedRoles(): array
    {
        $roles = [
            ['codigo' => 'administrador', 'nombre' => 'Administrador', 'descripcion' => 'Acceso global de la empresa.', 'alcance_permitido' => 'empresa'],
            ['codigo' => 'compras_logistica', 'nombre' => 'Compras y logística', 'descripcion' => 'Operaciones de compras e inventario por sede.', 'alcance_permitido' => 'sede'],
            ['codigo' => 'ventas_mayoristas', 'nombre' => 'Ventas mayoristas', 'descripcion' => 'Ventas y cotizaciones mayoristas por sede.', 'alcance_permitido' => 'sede'],
            ['codigo' => 'ventas_minoristas', 'nombre' => 'Ventas minoristas', 'descripcion' => 'Ventas minoristas y atención de pedidos por sede.', 'alcance_permitido' => 'sede'],
            ['codigo' => 'marketing', 'nombre' => 'Marketing', 'descripcion' => 'Campañas, promociones y analítica comercial.', 'alcance_permitido' => 'sede'],
            ['codigo' => 'cliente_mayorista', 'nombre' => 'Cliente mayorista', 'descripcion' => 'Acceso de ecommerce y cotizaciones propias.', 'alcance_permitido' => 'propio'],
            ['codigo' => 'cliente_minorista', 'nombre' => 'Cliente minorista', 'descripcion' => 'Acceso de ecommerce y pedidos propios.', 'alcance_permitido' => 'propio'],
        ];

        $rolesExistentes = DB::table('roles')
            ->get(['id', 'codigo', 'alcance_permitido', 'es_sistema', 'activo'])
            ->keyBy('codigo');

        if ($rolesExistentes->isNotEmpty()) {
            $errores = $this->erroresRolesExistentes();
            if ($errores !== []) {
                throw new \RuntimeException(implode(' ', $errores));
            }

            $identificadoresRoles = [];
            foreach ($roles as $rol) {
                $identificadoresRoles[$rol['codigo']] = (int) $rolesExistentes->get($rol['codigo'])->id;
            }

            return $identificadoresRoles;
        }

        $roleIds = [];

        foreach ($roles as $role) {
            DB::table('roles')->insert([...$role, 'es_sistema' => true, 'activo' => true, 'creado_en' => now(), 'actualizado_en' => now()]);

            $roleIds[$role['codigo']] = (int) DB::table('roles')->where('codigo', $role['codigo'])->value('id');
        }

        return $roleIds;
    }

    /** @return array<int,string> */
    private function erroresRolesExistentes(): array
    {
        if (! Schema::hasTable('roles')) {
            return [];
        }

        $rolesExistentes = DB::table('roles')
            ->get(['codigo', 'alcance_permitido', 'es_sistema', 'activo']);

        if ($rolesExistentes->isEmpty()) {
            return [];
        }

        $codigosEsperados = User::ROLES;
        $codigosExistentes = $rolesExistentes->pluck('codigo')->all();
        sort($codigosEsperados);
        sort($codigosExistentes);

        if ($codigosExistentes !== $codigosEsperados) {
            return ['Los códigos de roles existentes no coinciden exactamente con los siete roles del sistema.'];
        }

        $errores = [];
        foreach (self::ALCANCES_ROL as $codigo => $alcanceEsperado) {
            $rol = $rolesExistentes->firstWhere('codigo', $codigo);
            if ($rol === null
                || $rol->alcance_permitido !== $alcanceEsperado
                || ! (bool) $rol->es_sistema
                || ! (bool) $rol->activo) {
                $errores[] = 'El rol '.$codigo.' no coincide con el alcance y estado vigentes del catálogo.';
            }
        }

        return $errores;
    }

    /** @return array<string, int> */
    private function seedModules(): array
    {
        $modules = [
            'panel' => ['nombre' => 'Panel global', 'icono' => 'bi-speedometer2'],
            'organizacion' => ['nombre' => 'Organización y sedes', 'icono' => 'bi-buildings'],
            'seguridad' => ['nombre' => 'Seguridad y delegación', 'icono' => 'bi-shield-lock'],
            'productos' => ['nombre' => 'Productos y catálogo', 'icono' => 'bi-phone'],
            'categorias' => ['nombre' => 'Categorías', 'icono' => 'bi-diagram-3'],
            'inventario' => ['nombre' => 'Inventario y logística', 'icono' => 'bi-boxes'],
            'ventas' => ['nombre' => 'Ventas', 'icono' => 'bi-cart-check'],
            'pedidos' => ['nombre' => 'Pedidos', 'icono' => 'bi-receipt'],
            'compras' => ['nombre' => 'Compras', 'icono' => 'bi-cart-plus'],
            'cotizaciones' => ['nombre' => 'Cotizaciones', 'icono' => 'bi-file-earmark-text'],
            'clientes' => ['nombre' => 'Clientes', 'icono' => 'bi-people'],
            'proveedores' => ['nombre' => 'Proveedores', 'icono' => 'bi-truck'],
            'devoluciones' => ['nombre' => 'Devoluciones y garantías', 'icono' => 'bi-arrow-counterclockwise'],
            'soporte' => ['nombre' => 'Soporte y reclamos', 'icono' => 'bi-headset'],
            'campanias' => ['nombre' => 'Campañas', 'icono' => 'bi-megaphone'],
            'cupones' => ['nombre' => 'Promociones y cupones', 'icono' => 'bi-ticket-perforated'],
            'segmentos' => ['nombre' => 'Segmentos de clientes', 'icono' => 'bi-diagram-3'],
            'reportes' => ['nombre' => 'Reportes', 'icono' => 'bi-bar-chart'],
            'auditoria' => ['nombre' => 'Auditoría', 'icono' => 'bi-shield-check'],
            'pagos' => ['nombre' => 'Pagos y cobros', 'icono' => 'bi-credit-card'],
            'facturacion' => ['nombre' => 'Facturación electrónica', 'icono' => 'bi-receipt-cutoff'],
            'reembolsos' => ['nombre' => 'Reembolsos', 'icono' => 'bi-arrow-return-left'],
            'notificaciones' => ['nombre' => 'Notificaciones', 'icono' => 'bi-bell'],
            'integraciones' => ['nombre' => 'Integraciones', 'icono' => 'bi-plug'],
            'respaldos' => ['nombre' => 'Respaldos', 'icono' => 'bi-database-gear'],
            'configuracion' => ['nombre' => 'Configuración', 'icono' => 'bi-gear'],
            'analitica' => ['nombre' => 'Análisis', 'icono' => 'bi-graph-up-arrow'],
            'catalogo' => ['nombre' => 'Ecommerce público', 'icono' => 'bi-shop'],
            'cuenta' => ['nombre' => 'Cuenta personal', 'icono' => 'bi-person-circle'],
        ];

        $moduleIds = [];
        $order = 0;

        foreach ($modules as $code => $module) {
            DB::table('modulos')->insert(
                [
                    'codigo' => $code,
                    'nombre' => $module['nombre'],
                    'descripcion' => null,
                    'icono' => $module['icono'],
                    'orden' => $order++,
                    'habilitado' => true,
                    'actualizado_en' => now(),
                ],
            );

            $moduleIds[$code] = (int) DB::table('modulos')->where('codigo', $code)->value('id');
        }

        return $moduleIds;
    }

    /** @return array<string, array{module:string,name:string,route:?string,icon:string,active:bool}> */
    private function screens(): array
    {
        return [
            'admin.dashboard' => ['module' => 'panel', 'name' => 'Dashboard global', 'route' => 'management.dashboard', 'icon' => 'bi-speedometer2', 'active' => true],
            'admin.company' => ['module' => 'organizacion', 'name' => 'Datos de la empresa', 'route' => 'management.company_settings.data', 'icon' => 'bi-building', 'active' => true],
            'admin.users' => ['module' => 'seguridad', 'name' => 'Gestión de usuarios', 'route' => 'management.users', 'icon' => 'bi-people', 'active' => true],
            'admin.roles' => ['module' => 'seguridad', 'name' => 'Roles y permisos', 'route' => 'management.roles_permissions', 'icon' => 'bi-key', 'active' => true],
            'admin.permissions_matrix' => ['module' => 'seguridad', 'name' => 'Matriz de privilegios', 'route' => 'management.permissions_matrix', 'icon' => 'bi-grid-3x3-gap', 'active' => true],
            'admin.user_assignments' => ['module' => 'seguridad', 'name' => 'Asignación de roles a usuarios', 'route' => 'management.user_assignments', 'icon' => 'bi-person-gear', 'active' => true],
            'admin.menus' => ['module' => 'seguridad', 'name' => 'Menús y submenús', 'route' => 'management.menus', 'icon' => 'bi-list-nested', 'active' => true],
            'admin.modules' => ['module' => 'seguridad', 'name' => 'Módulos y pantallas', 'route' => 'management.modules', 'icon' => 'bi-grid-3x3-gap', 'active' => true],
            'admin.retail_sales' => ['module' => 'ventas', 'name' => 'Ventas', 'route' => 'management.sales', 'icon' => 'bi-cart-check', 'active' => true],
            'admin.wholesale_sales' => ['module' => 'ventas', 'name' => 'Ventas mayoristas', 'route' => null, 'icon' => 'bi-box-seam', 'active' => false],
            'admin.online_orders' => ['module' => 'pedidos', 'name' => 'Pedidos online', 'route' => 'management.orders', 'icon' => 'bi-bag-check', 'active' => true],
            'admin.purchases' => ['module' => 'compras', 'name' => 'Compras', 'route' => 'management.purchases', 'icon' => 'bi-cart-plus', 'active' => true],
            'admin.quotations' => ['module' => 'cotizaciones', 'name' => 'Cotizaciones', 'route' => 'management.quotations', 'icon' => 'bi-file-earmark-text', 'active' => true],
            'admin.customers' => ['module' => 'clientes', 'name' => 'Clientes', 'route' => 'management.customers', 'icon' => 'bi-people', 'active' => true],
            'admin.suppliers' => ['module' => 'proveedores', 'name' => 'Proveedores', 'route' => 'management.suppliers', 'icon' => 'bi-truck', 'active' => true],
            'admin.returns' => ['module' => 'devoluciones', 'name' => 'Devoluciones y garantías', 'route' => 'management.returns', 'icon' => 'bi-arrow-counterclockwise', 'active' => true],
            'admin.products' => ['module' => 'productos', 'name' => 'Productos y categorías', 'route' => 'management.products', 'icon' => 'bi-phone', 'active' => true],
            'admin.categories' => ['module' => 'categorias', 'name' => 'Categorías', 'route' => 'management.categories', 'icon' => 'bi-diagram-3', 'active' => true],
            'admin.inventory' => ['module' => 'inventario', 'name' => 'Inventario por almacén', 'route' => 'management.inventory', 'icon' => 'bi-boxes', 'active' => true],
            'admin.stock_movements' => ['module' => 'inventario', 'name' => 'Movimientos de inventario', 'route' => 'management.inventory', 'icon' => 'bi-arrow-left-right', 'active' => true],
            'admin.transfers' => ['module' => 'inventario', 'name' => 'Transferencias entre sedes', 'route' => 'management.inventory.preview.transfers', 'icon' => 'bi-arrow-left-right', 'active' => true],
            'admin.shipments' => ['module' => 'inventario', 'name' => 'Envíos y entregas', 'route' => 'management.inventory.preview.shipments', 'icon' => 'bi-truck', 'active' => false],
            'admin.imei' => ['module' => 'inventario', 'name' => 'IMEI y números de serie', 'route' => 'management.inventory.preview.serials', 'icon' => 'bi-upc-scan', 'active' => false],
            'admin.payments' => ['module' => 'pagos', 'name' => 'Pagos y cobros', 'route' => 'management.payments', 'icon' => 'bi-credit-card', 'active' => true],
            'admin.cash_registers' => ['module' => 'pagos', 'name' => 'Cajas, aperturas y cierres', 'route' => 'management.payments.preview.cash_registers', 'icon' => 'bi-cash-stack', 'active' => true],
            'admin.receivables' => ['module' => 'pagos', 'name' => 'Cuentas por cobrar y pagar', 'route' => 'management.payments', 'icon' => 'bi-journal-text', 'active' => false],
            'admin.refunds' => ['module' => 'reembolsos', 'name' => 'Reembolsos', 'route' => 'management.refunds', 'icon' => 'bi-arrow-return-left', 'active' => true],
            'admin.electronic_invoices' => ['module' => 'facturacion', 'name' => 'Facturas y boletas electrónicas', 'route' => 'management.billing', 'icon' => 'bi-receipt', 'active' => true],
            'admin.credit_debit_notes' => ['module' => 'facturacion', 'name' => 'Notas de crédito y débito', 'route' => 'management.billing', 'icon' => 'bi-file-earmark-minus', 'active' => false],
            'admin.series' => ['module' => 'facturacion', 'name' => 'Series y correlativos', 'route' => 'management.billing', 'icon' => 'bi-123', 'active' => false],
            'admin.sunat' => ['module' => 'facturacion', 'name' => 'Seguimiento SUNAT', 'route' => 'management.billing', 'icon' => 'bi-cloud-check', 'active' => false],
            'admin.public_catalog' => ['module' => 'catalogo', 'name' => 'Catálogo público', 'route' => 'catalog.index', 'icon' => 'bi-shop', 'active' => true],
            'admin.prices' => ['module' => 'productos', 'name' => 'Precios minoristas y mayoristas', 'route' => 'management.products', 'icon' => 'bi-tags', 'active' => true],
            'admin.campaigns' => ['module' => 'campanias', 'name' => 'Campañas publicitarias', 'route' => 'management.campaigns', 'icon' => 'bi-megaphone', 'active' => true],
            'admin.promotions' => ['module' => 'cupones', 'name' => 'Promociones y cupones', 'route' => 'management.coupons', 'icon' => 'bi-ticket-perforated', 'active' => true],
            'admin.segments' => ['module' => 'segmentos', 'name' => 'Segmentación de clientes', 'route' => 'management.segments', 'icon' => 'bi-diagram-3', 'active' => true],
            'admin.store_settings' => ['module' => 'configuracion', 'name' => 'Configuración de la tienda virtual', 'route' => 'management.commercial_settings', 'icon' => 'bi-sliders', 'active' => false],
            'admin.reports' => ['module' => 'reportes', 'name' => 'Reportes globales', 'route' => 'management.reports', 'icon' => 'bi-bar-chart', 'active' => true],
            'admin.site_reports' => ['module' => 'reportes', 'name' => 'Reportes por sede', 'route' => null, 'icon' => 'bi-buildings', 'active' => false],
            'admin.audit' => ['module' => 'auditoria', 'name' => 'Auditoría de operaciones', 'route' => 'management.audit', 'icon' => 'bi-shield-check', 'active' => true],
            'admin.role_history' => ['module' => 'auditoria', 'name' => 'Historial de roles y permisos', 'route' => null, 'icon' => 'bi-clock-history', 'active' => false],
            'admin.security_events' => ['module' => 'auditoria', 'name' => 'Eventos de seguridad', 'route' => 'management.audit', 'icon' => 'bi-shield-exclamation', 'active' => false],
            'admin.exports' => ['module' => 'reportes', 'name' => 'Exportaciones autorizadas', 'route' => 'management.reports', 'icon' => 'bi-download', 'active' => true],
            'admin.general_settings' => ['module' => 'configuracion', 'name' => 'Configuración general', 'route' => 'management.general_settings', 'icon' => 'bi-gear', 'active' => true],
            'admin.integrations' => ['module' => 'integraciones', 'name' => 'Integraciones', 'route' => 'management.integrations', 'icon' => 'bi-plug', 'active' => true],
            'admin.notifications' => ['module' => 'notificaciones', 'name' => 'Notificaciones del sistema', 'route' => 'management.notifications', 'icon' => 'bi-bell', 'active' => true],
            'admin.security_settings' => ['module' => 'configuracion', 'name' => 'Configuración de seguridad', 'route' => 'management.security_settings', 'icon' => 'bi-shield-lock', 'active' => true],
            'admin.backups' => ['module' => 'respaldos', 'name' => 'Respaldos y recuperación', 'route' => 'management.backups', 'icon' => 'bi-database-gear', 'active' => true],
            'account.profile' => ['module' => 'cuenta', 'name' => 'Mi perfil', 'route' => 'management.account', 'icon' => 'bi-person-circle', 'active' => true],
            'account.dashboard' => ['module' => 'panel', 'name' => 'Mi panel', 'route' => 'dashboard', 'icon' => 'bi-house', 'active' => true],
            'account.orders' => ['module' => 'pedidos', 'name' => 'Mis pedidos', 'route' => 'orders.index', 'icon' => 'bi-bag-check', 'active' => true],
            'account.quotations' => ['module' => 'cotizaciones', 'name' => 'Mis cotizaciones', 'route' => 'quotations.index', 'icon' => 'bi-file-earmark-text', 'active' => true],
            'account.edit' => ['module' => 'cuenta', 'name' => 'Editar datos personales', 'route' => 'management.account', 'icon' => 'bi-person', 'active' => true],
            'account.security' => ['module' => 'cuenta', 'name' => 'Seguridad de la cuenta', 'route' => 'management.account.security', 'icon' => 'bi-shield-check', 'active' => true],
            'account.sessions' => ['module' => 'cuenta', 'name' => 'Sesiones y dispositivos activos', 'route' => 'management.account.sessions', 'icon' => 'bi-pc-display-horizontal', 'active' => true],
            'account.preferences' => ['module' => 'cuenta', 'name' => 'Preferencias de visualización', 'route' => 'management.account.preferences', 'icon' => 'bi-sliders', 'active' => true],
            'account.activity' => ['module' => 'cuenta', 'name' => 'Mi actividad reciente', 'route' => 'management.account.activity', 'icon' => 'bi-clock-history', 'active' => true],
            'support.reclamos' => ['module' => 'soporte', 'name' => 'Soporte y reclamos', 'route' => 'management.support', 'icon' => 'bi-headset', 'active' => true],
        ];
    }

    /** @param array<string, int> $moduleIds
     * @return array<string, int>
     */
    private function seedScreens(array $moduleIds): array
    {
        $screenIds = [];

        foreach ($this->screens() as $code => $screen) {
            DB::table('pantallas')->insert(
                [
                    'codigo' => $code,
                    'modulo_id' => $moduleIds[$screen['module']],
                    'nombre' => $screen['name'],
                    'ruta' => $screen['route'],
                    'icono' => $screen['icon'],
                    'habilitada' => $screen['active'],
                    'actualizado_en' => now(),
                ],
            );

            $screenIds[$code] = (int) DB::table('pantallas')->where('codigo', $code)->value('id');
        }

        return $screenIds;
    }

    /** @param array<string, int> $moduleIds
     * @param  array<string, int>  $screenIds
     * @return array<string, int>
     */
    private function seedPermissions(array $moduleIds, array $screenIds): array
    {
        $actionLabels = [
            'consultar' => 'Consultar', 'crear' => 'Crear', 'actualizar' => 'Actualizar',
            'eliminar' => 'Eliminar', 'aprobar' => 'Aprobar', 'anular' => 'Anular',
            'exportar' => 'Exportar', 'ajustar' => 'Ajustar', 'transferir' => 'Transferir',
            'asignar' => 'Asignar', 'configurar' => 'Configurar', 'activar' => 'Activar',
            'revocar' => 'Revocar', 'recibir' => 'Recibir', 'emitir' => 'Emitir',
            'restaurar' => 'Restaurar',
        ];
        $permissionIds = [];

        foreach ($this->moduleActions() as $moduleCode => $actions) {
            $moduleId = $moduleIds[$moduleCode] ?? null;

            if ($moduleId === null) {
                continue;
            }

            foreach ($actions as $action) {
                $label = $actionLabels[$action];
                $code = 'gestion.'.$moduleCode.'.'.$action;
                DB::table('permisos')->insert(
                    [
                        'codigo' => $code,
                        'modulo_id' => $moduleId,
                        'pantalla_id' => null,
                        'accion' => $action,
                        'nombre' => $label.' - '.DB::table('modulos')->where('id', $moduleId)->value('nombre'),
                        'activo' => true,
                        'actualizado_en' => now(),
                    ],
                );
                $permissionIds[$code] = (int) DB::table('permisos')->where('codigo', $code)->value('id');
            }
        }

        foreach ($this->screens() as $screenCode => $screen) {
            if (! $screen['active']) {
                continue;
            }

            $code = 'pantalla.'.$screenCode.'.consultar';
            DB::table('permisos')->insert(
                [
                    'codigo' => $code,
                    'modulo_id' => $moduleIds[$screen['module']],
                    'pantalla_id' => $screenIds[$screenCode],
                    'accion' => 'consultar',
                    'nombre' => 'Consultar - '.$screen['name'],
                    'activo' => true,
                    'actualizado_en' => now(),
                ],
            );
            $permissionIds[$code] = (int) DB::table('permisos')->where('codigo', $code)->value('id');
        }

        return $permissionIds;
    }

    /** @return array<string, array<int, string>> */
    private function moduleActions(): array
    {
        return [
            'panel' => ['consultar'],
            'organizacion' => ['consultar', 'actualizar'],
            'seguridad' => ['consultar', 'crear', 'actualizar', 'eliminar', 'asignar', 'revocar', 'activar', 'exportar'],
            'productos' => ['consultar', 'crear', 'actualizar', 'eliminar', 'activar', 'exportar'],
            'categorias' => ['consultar', 'crear', 'actualizar', 'eliminar', 'activar', 'exportar'],
            'inventario' => ['consultar', 'ajustar', 'transferir', 'aprobar', 'exportar'],
            'ventas' => ['consultar', 'crear', 'actualizar', 'anular', 'exportar'],
            'pedidos' => ['consultar', 'crear', 'actualizar', 'aprobar', 'anular', 'exportar'],
            'compras' => ['consultar', 'crear', 'actualizar', 'aprobar', 'anular', 'recibir', 'exportar'],
            'cotizaciones' => ['consultar', 'crear', 'actualizar', 'aprobar', 'anular', 'exportar'],
            'clientes' => ['consultar', 'crear', 'actualizar', 'eliminar', 'exportar'],
            'proveedores' => ['consultar', 'crear', 'actualizar', 'eliminar', 'exportar'],
            'devoluciones' => ['consultar', 'crear', 'actualizar', 'eliminar', 'aprobar', 'anular', 'exportar'],
            'soporte' => ['consultar', 'crear', 'actualizar', 'aprobar', 'anular', 'exportar'],
            'campanias' => ['consultar', 'crear', 'actualizar', 'eliminar', 'activar', 'configurar', 'exportar'],
            'cupones' => ['consultar', 'crear', 'actualizar', 'eliminar', 'activar', 'exportar'],
            'segmentos' => ['consultar', 'crear', 'actualizar', 'eliminar'],
            'reportes' => ['consultar', 'exportar'],
            'auditoria' => ['consultar', 'exportar'],
            'pagos' => ['consultar', 'crear', 'actualizar', 'aprobar', 'anular', 'exportar'],
            'facturacion' => ['consultar', 'crear', 'actualizar', 'aprobar', 'anular', 'emitir', 'exportar'],
            'reembolsos' => ['consultar', 'crear', 'aprobar', 'anular', 'exportar'],
            'notificaciones' => ['consultar', 'actualizar', 'configurar'],
            'integraciones' => ['consultar', 'actualizar', 'configurar'],
            'respaldos' => ['consultar', 'crear', 'restaurar', 'exportar'],
            'configuracion' => ['consultar', 'actualizar', 'configurar'],
            'analitica' => ['consultar', 'exportar'],
            'catalogo' => ['consultar'],
            'cuenta' => ['consultar', 'actualizar'],
        ];
    }

    /** @param array<string, int> $roleIds
     * @param  array<string, int>  $permissionIds
     */
    private function seedRolePermissions(array $roleIds, array $permissionIds): void
    {
        foreach ($permissionIds as $permissionCode => $permissionId) {
            DB::table('roles_permisos')->insert([
                'rol_id' => $roleIds['administrador'],
                'permiso_id' => $permissionId,
                'alcance' => 'empresa',
                'creado_en' => now(),
            ]);
        }

        $roleModules = [
            'compras_logistica' => [
                'inventario' => ['consultar', 'ajustar', 'transferir'],
                'productos' => ['consultar'], 'proveedores' => ['consultar', 'crear', 'actualizar'],
                'compras' => ['consultar', 'crear', 'aprobar'], 'reportes' => ['consultar', 'exportar'],
            ],
            'ventas_mayoristas' => [
                'productos' => ['consultar'], 'catalogo' => ['consultar'],
                'cotizaciones' => ['consultar', 'crear', 'aprobar', 'exportar'],
                'pedidos' => ['consultar', 'actualizar'], 'clientes' => ['consultar'],
            ],
            'ventas_minoristas' => [
                'productos' => ['consultar'], 'categorias' => ['consultar'], 'inventario' => ['consultar'],
                'ventas' => ['consultar', 'crear', 'actualizar', 'anular'], 'pedidos' => ['consultar', 'actualizar'],
                'clientes' => ['consultar'], 'reportes' => ['consultar', 'exportar'],
            ],
            'marketing' => [
                'campanias' => ['consultar', 'crear', 'actualizar', 'eliminar', 'configurar'],
                'cupones' => ['consultar', 'crear', 'actualizar', 'eliminar', 'exportar'],
                'segmentos' => ['consultar', 'crear', 'actualizar', 'eliminar'],
                'reportes' => ['consultar', 'exportar'], 'analitica' => ['consultar', 'exportar'],
            ],
            'cliente_mayorista' => [
                'catalogo' => ['consultar'], 'cotizaciones' => ['consultar', 'crear'], 'pedidos' => ['consultar'],
            ],
            'cliente_minorista' => [
                'catalogo' => ['consultar'], 'pedidos' => ['consultar', 'crear'],
            ],
        ];

        foreach ($roleModules as $roleCode => $modules) {
            foreach ($modules as $moduleCode => $actions) {
                foreach ($actions as $action) {
                    $permissionCode = 'gestion.'.$moduleCode.'.'.$action;
                    $permissionId = $permissionIds[$permissionCode] ?? null;

                    if ($permissionId !== null) {
                        DB::table('roles_permisos')->insert([
                            'rol_id' => $roleIds[$roleCode],
                            'permiso_id' => $permissionId,
                            'alcance' => str_starts_with($roleCode, 'cliente_') ? 'propio' : 'sede',
                            'creado_en' => now(),
                        ]);
                    }
                }
            }
        }
    }

    /** @param array<string, int> $roleIds
     * @param  array<string, int>  $screenIds
     * @param  array<string, int>  $permissionIds
     */
    private function seedMenus(array $roleIds, array $screenIds, array $permissionIds): void
    {
        $menus = [];

        foreach (array_keys($roleIds) as $roleCode) {
            $menuCode = $roleCode.'.sidebar';
            DB::table('menus')->insert([
                'codigo' => $menuCode,
                'nombre' => 'Barra lateral - '.str_replace('_', ' ', ucfirst($roleCode)),
                'contexto' => 'sidebar',
                'habilitado' => true,
                'creado_en' => now(),
                'actualizado_en' => now(),
            ]);
            $menuId = (int) DB::table('menus')->where('codigo', $menuCode)->value('id');
            $menus[$roleCode] = $menuId;
            DB::table('roles_menus')->insert(['rol_id' => $roleIds[$roleCode], 'menu_id' => $menuId, 'creado_en' => now()]);
        }

        $adminGroups = [
            ['codigo' => 'organizacion', 'etiqueta' => 'Organización y sedes', 'icono' => 'bi-buildings', 'pantallas' => ['admin.company']],
            ['codigo' => 'seguridad', 'etiqueta' => 'Seguridad y delegación', 'icono' => 'bi-shield-lock', 'pantallas' => ['admin.users', 'admin.roles', 'admin.permissions_matrix', 'admin.user_assignments', 'admin.menus', 'admin.modules']],
            ['codigo' => 'operaciones', 'etiqueta' => 'Operaciones comerciales', 'icono' => 'bi-briefcase', 'pantallas' => ['admin.retail_sales', 'admin.online_orders', 'admin.purchases', 'admin.quotations', 'admin.customers', 'admin.suppliers', 'admin.returns']],
            ['codigo' => 'inventario', 'etiqueta' => 'Inventario y logística', 'icono' => 'bi-boxes', 'pantallas' => ['admin.products', 'admin.categories', 'admin.inventory', 'admin.stock_movements', 'admin.transfers', 'admin.shipments', 'admin.imei']],
            ['codigo' => 'finanzas', 'etiqueta' => 'Finanzas y facturación', 'icono' => 'bi-wallet2', 'pantallas' => ['admin.payments', 'admin.cash_registers', 'admin.receivables', 'admin.refunds', 'admin.electronic_invoices', 'admin.credit_debit_notes', 'admin.series', 'admin.sunat']],
            ['codigo' => 'ecommerce', 'etiqueta' => 'Ecommerce y marketing', 'icono' => 'bi-shop', 'pantallas' => ['admin.public_catalog', 'admin.prices', 'admin.campaigns', 'admin.promotions', 'admin.segments', 'admin.store_settings']],
            ['codigo' => 'analisis', 'etiqueta' => 'Análisis y auditoría', 'icono' => 'bi-graph-up-arrow', 'pantallas' => ['admin.reports', 'admin.site_reports', 'admin.audit', 'admin.role_history', 'admin.security_events', 'admin.exports']],
            ['codigo' => 'configuracion', 'etiqueta' => 'Configuración del sistema', 'icono' => 'bi-gear', 'pantallas' => ['admin.general_settings', 'admin.integrations', 'admin.notifications', 'admin.security_settings', 'admin.backups']],
        ];

        $this->createMenuElements($menus['administrador'], $adminGroups, $screenIds, $permissionIds);
        $this->createMenuElements($menus['administrador'], [['codigo' => 'dashboard', 'etiqueta' => 'Dashboard global', 'icono' => 'bi-speedometer2', 'pantallas' => ['admin.dashboard']]], $screenIds, $permissionIds, 0, false);

        $roleScreens = [
            'compras_logistica' => ['account.dashboard', 'admin.inventory', 'admin.stock_movements', 'admin.suppliers', 'admin.purchases', 'admin.products'],
            'ventas_mayoristas' => ['account.dashboard', 'admin.public_catalog', 'account.quotations', 'admin.online_orders', 'admin.customers'],
            'ventas_minoristas' => ['account.dashboard', 'admin.products', 'admin.categories', 'admin.inventory', 'admin.retail_sales', 'admin.online_orders', 'admin.customers'],
            'marketing' => ['account.dashboard', 'admin.campaigns', 'admin.promotions', 'admin.segments', 'admin.reports'],
            'cliente_mayorista' => ['account.dashboard', 'admin.public_catalog', 'account.quotations', 'account.orders'],
            'cliente_minorista' => ['account.dashboard', 'admin.public_catalog', 'account.orders'],
        ];

        foreach ($roleScreens as $roleCode => $screens) {
            $items = [];
            foreach ($screens as $index => $screenCode) {
                $items[] = [
                    'codigo' => $screenCode,
                    'etiqueta' => $this->screens()[$screenCode]['name'],
                    'icono' => $this->screens()[$screenCode]['icon'],
                    'pantallas' => [$screenCode],
                    'orden' => $index + 1,
                ];
            }
            $this->createMenuElements($menus[$roleCode], $items, $screenIds, $permissionIds, 1, false);
        }

        foreach ($roleScreens as $roleCode => $screenCodes) {
            if ($roleCode === 'administrador') {
                continue;
            }

            foreach ($screenCodes as $screenCode) {
                $permissionCode = 'pantalla.'.$screenCode.'.consultar';
                $permissionId = $permissionIds[$permissionCode] ?? null;

                if ($permissionId !== null) {
                    DB::table('roles_permisos')->insert([
                        'rol_id' => $roleIds[$roleCode],
                        'permiso_id' => $permissionId,
                        'alcance' => str_starts_with($roleCode, 'cliente_') ? 'propio' : 'sede',
                        'creado_en' => now(),
                    ]);
                }
            }
        }
    }

    /** @param array<int, array{codigo:string,etiqueta:string,icono:string,pantallas:array<int,string>,orden?:int}> $groups
     * @param  array<string, int>  $screenIds
     * @param  array<string, int>  $permissionIds
     */
    private function createMenuElements(int $menuId, array $groups, array $screenIds, array $permissionIds, int $firstOrder = 1, bool $nested = true): void
    {
        foreach ($groups as $groupIndex => $group) {
            $parentId = null;
            $groupCode = $group['codigo'];

            if ($nested) {
                DB::table('elementos_menu')->insert(
                    [
                        'menu_id' => $menuId,
                        'codigo' => 'grupo.'.$groupCode,
                        'padre_id' => null,
                        'pantalla_id' => null,
                        'permiso_id' => null,
                        'etiqueta' => $group['etiqueta'],
                        'icono' => $group['icono'],
                        'tipo' => 'grupo',
                        'orden' => $group['orden'] ?? $firstOrder + $groupIndex,
                        'habilitado' => true,
                        'actualizado_en' => now(),
                    ],
                );
                $parentId = (int) DB::table('elementos_menu')->where('menu_id', $menuId)->where('codigo', 'grupo.'.$groupCode)->value('id');
            }

            foreach ($group['pantallas'] as $itemIndex => $screenCode) {
                $screen = $this->screens()[$screenCode];
                $permissionId = $permissionIds['pantalla.'.$screenCode.'.consultar'] ?? null;
                $elementCode = $nested ? $screenCode : 'item.'.$screenCode;

                DB::table('elementos_menu')->insert(
                    [
                        'menu_id' => $menuId,
                        'codigo' => $elementCode,
                        'padre_id' => $parentId,
                        'pantalla_id' => $screenIds[$screenCode],
                        'permiso_id' => $permissionId,
                        'etiqueta' => $screen['name'],
                        'icono' => $screen['icon'],
                        'tipo' => 'pantalla',
                        'orden' => $itemIndex + 1,
                        'habilitado' => true,
                        'actualizado_en' => now(),
                    ],
                );
            }
        }
    }
}
