@php
    $enabledItem = static fn (string $route, string $label, string $icon): array => [
        'route' => $route,
        'label' => $label,
        'icon' => $icon,
        'enabled' => \Illuminate\Support\Facades\Route::has($route),
    ];
    $disabledItem = static fn (string $label, string $icon): array => [
        'route' => null,
        'label' => $label,
        'icon' => $icon,
        'enabled' => false,
    ];
    $itemMatchesCurrentRoute = static fn (array $item): bool => $item['enabled']
        && $item['route']
        && request()->routeIs($item['route']);

    $groups = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'items' => [
            $enabledItem('management.dashboard', 'Resumen general del negocio', 'bi-grid-1x2'),
            $enabledItem('management.dashboard.sales', 'Indicadores de ventas e ingresos', 'bi-graph-up'),
            $enabledItem('management.dashboard.orders', 'Pedidos y operaciones recientes', 'bi-clock-history'),
            $enabledItem('management.dashboard.alerts', 'Alertas y notificaciones', 'bi-bell'),
        ]],
        ['key' => 'organizacion', 'label' => 'Empresa y operación interna', 'icon' => 'bi-buildings', 'items' => [
            $enabledItem('management.company_settings.data', 'Datos de la empresa', 'bi-building'),
        ]],
        ['key' => 'seguridad', 'label' => 'Seguridad y Delegación', 'icon' => 'bi-shield-lock', 'items' => [
            $enabledItem('management.users', 'Gestión de usuarios', 'bi-person-gear'),
            $enabledItem('management.roles_permissions', 'Roles y permisos', 'bi-key'),
            $enabledItem('management.roles_permissions', 'Matriz de privilegios', 'bi-grid-3x3-gap'),
            $enabledItem('management.user_assignments', 'Asignación de roles a usuarios', 'bi-person-check'),
            $enabledItem('management.menus', 'Menús y submenús', 'bi-list-nested'),
            $enabledItem('management.modules', 'Módulos y pantallas', 'bi-window-stack'),
            $disabledItem('Políticas de aprobación y delegación', 'bi-person-lock'),
            $disabledItem('Sesiones y accesos globales', 'bi-pc-display'),
            $enabledItem('management.users', 'Bloqueo y reactivación de cuentas', 'bi-person-exclamation'),
        ]],
        ['key' => 'operaciones', 'label' => 'Operaciones Comerciales', 'icon' => 'bi-briefcase', 'items' => [
            $enabledItem('management.sales', 'Ventas', 'bi-cart-check'),
            $enabledItem('management.sales.preview.retail', 'Ventas minoristas', 'bi-shop'),
            $enabledItem('management.sales.preview.wholesale', 'Ventas mayoristas', 'bi-people'),
            $enabledItem('management.orders', 'Pedidos online', 'bi-receipt'),
            $enabledItem('management.purchases', 'Compras', 'bi-cart-plus'),
            $enabledItem('management.quotations', 'Cotizaciones', 'bi-file-earmark-text'),
            $enabledItem('management.customers', 'Clientes', 'bi-people'),
            $enabledItem('management.suppliers', 'Proveedores', 'bi-truck'),
            $enabledItem('management.returns', 'Devoluciones y garantías', 'bi-arrow-counterclockwise'),
        ]],
        ['key' => 'inventario', 'label' => 'Inventario y Logística', 'icon' => 'bi-boxes', 'items' => [
            $enabledItem('management.products', 'Productos', 'bi-box-seam'),
            $enabledItem('management.categories', 'Categorías', 'bi-diagram-3'),
            $enabledItem('management.inventory', 'Inventario', 'bi-boxes'),
            $enabledItem('management.inventory.movements', 'Movimientos de inventario', 'bi-arrow-left-right'),
            $enabledItem('management.inventory.preview.shipments', 'Envíos y entregas', 'bi-truck'),
            $enabledItem('management.inventory.preview.serials', 'Control de IMEI y series', 'bi-phone'),
        ]],
        ['key' => 'finanzas', 'label' => 'Finanzas y Facturación', 'icon' => 'bi-wallet2', 'items' => [
            $enabledItem('management.payments', 'Pagos y cobros', 'bi-credit-card'),
            $enabledItem('management.payments.preview.cash_registers', 'Cajas, aperturas y cierres', 'bi-cash-stack'),
            $enabledItem('management.payments.preview.accounts', 'Cuentas por cobrar y pagar', 'bi-journal-text'),
            $enabledItem('management.refunds', 'Reembolsos', 'bi-arrow-return-left'),
            $enabledItem('management.billing', 'Comprobantes de pago', 'bi-receipt-cutoff'),
        ]],
        ['key' => 'ecommerce', 'label' => 'Ecommerce y Marketing', 'icon' => 'bi-megaphone', 'items' => [
            $enabledItem('catalog.index', 'Catálogo público', 'bi-shop-window'),
            $enabledItem('management.products.preview.prices', 'Precios minoristas y mayoristas', 'bi-tags'),
            $enabledItem('management.campaigns', 'Campañas publicitarias', 'bi-megaphone'),
            $enabledItem('management.coupons', 'Promociones y cupones', 'bi-ticket-perforated'),
            $enabledItem('management.segments', 'Segmentación de clientes', 'bi-diagram-3'),
            $enabledItem('management.products.preview.store_settings', 'Configuración de la tienda virtual', 'bi-sliders'),
        ]],
        ['key' => 'analisis', 'label' => 'Análisis y Auditoría', 'icon' => 'bi-bar-chart-line', 'items' => [
            $enabledItem('management.reports.preview.global', 'Reportes globales', 'bi-bar-chart'),
            $enabledItem('management.audit.preview.operations', 'Auditoría de operaciones', 'bi-shield-check'),
            $enabledItem('management.audit.preview.roles', 'Historial de roles y permisos', 'bi-clock-history'),
            $enabledItem('management.audit.preview.security', 'Eventos de seguridad', 'bi-shield-exclamation'),
            $enabledItem('management.reports.preview.exports', 'Exportaciones autorizadas', 'bi-download'),
        ]],
        ['key' => 'configuracion', 'label' => 'Configuración del Sistema', 'icon' => 'bi-gear', 'items' => [
            $enabledItem('management.general_settings.preview', 'Configuración general', 'bi-gear'),
            $enabledItem('management.integrations.preview', 'Integraciones', 'bi-plug'),
            $enabledItem('management.notifications.preview', 'Notificaciones', 'bi-bell'),
            $enabledItem('management.security_settings.preview', 'Configuración de seguridad', 'bi-shield-check'),
            $enabledItem('management.backups.preview', 'Respaldos y recuperación', 'bi-database-gear'),
        ]],
    ];
@endphp

<div class="admin-menu-desplegable" data-admin-menu-desplegable>
    <div class="admin-menu-desplegable__groups">
        @foreach ($groups as $group)
            @php
                $groupIsActive = false;
                foreach ($group['items'] as $item) {
                    if ($itemMatchesCurrentRoute($item)) {
                        $groupIsActive = true;
                        break;
                    }
                }
                $groupIsOpen = $groupIsActive;
                $activeItemShown = false;
            @endphp
            <section class="admin-menu-desplegable__group {{ $groupIsActive ? 'is-active' : '' }} {{ $groupIsOpen ? 'is-open' : '' }}" data-admin-menu-group>
                <button class="admin-menu-desplegable__toggle" type="button" aria-expanded="{{ $groupIsOpen ? 'true' : 'false' }}" aria-controls="admin-menu-submenu-{{ $group['key'] }}" data-admin-menu-toggle>
                    <span class="admin-menu-desplegable__toggle-main"><i class="bi {{ $group['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $group['label'] }}</span></span>
                    <i class="bi bi-chevron-down admin-menu-desplegable__chevron" aria-hidden="true"></i>
                </button>
                <div class="admin-menu-desplegable__submenu" id="admin-menu-submenu-{{ $group['key'] }}" data-admin-menu-submenu @if (! $groupIsOpen) hidden @endif>
                    @foreach ($group['items'] as $item)
                        @php
                            $itemIsActive = ! $activeItemShown && $itemMatchesCurrentRoute($item);
                            $activeItemShown = $activeItemShown || $itemIsActive;
                        @endphp
                        @if ($item['enabled'] && $item['route'])
                            <a class="admin-menu-desplegable__item {{ $itemIsActive ? 'is-active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @if ($itemIsActive) aria-current="page" @endif>
                                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                <span class="admin-menu-desplegable__label">{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span class="admin-menu-desplegable__item admin-menu-desplegable__item--disabled" aria-disabled="true" aria-label="{{ $item['label'] }}. Próximamente." title="Próximamente">
                                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                <span class="admin-menu-desplegable__label">{{ $item['label'] }}</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
