@php
    $enabledItem = static fn (string $route, string $label, string $icon): array => [
        'route' => $route,
        'label' => $label,
        'icon' => $icon,
        'enabled' => \Illuminate\Support\Facades\Route::has($route),
    ];
    $itemMatchesCurrentRoute = static fn (array $item): bool => $item['enabled']
        && $item['route']
        && request()->routeIs($item['route']);

    $groups = [
        ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'items' => [
            $enabledItem('management.dashboard', 'Dashboard', 'bi-speedometer2'),
        ]],
        ['key' => 'seguridad', 'label' => 'Seguridad y Delegación', 'icon' => 'bi-shield-lock', 'items' => [
            $enabledItem('management.users', 'Gestión de usuarios', 'bi-person-gear'),
            $enabledItem('management.roles_permissions', 'Roles y permisos', 'bi-key'),
            $enabledItem('management.permissions_matrix', 'Matriz de privilegios', 'bi-grid-3x3-gap'),
            $enabledItem('management.user_assignments', 'Asignaciones globales y por sede', 'bi-person-check'),
            $enabledItem('management.menus', 'Menús y submenús dinámicos', 'bi-list-nested'),
            $enabledItem('management.modules', 'Módulos y pantallas', 'bi-window-stack'),
        ]],
        ['key' => 'operaciones', 'label' => 'Operaciones Comerciales', 'icon' => 'bi-briefcase', 'items' => [
            $enabledItem('management.sales', 'Ventas', 'bi-cart-check'),
            $enabledItem('management.orders', 'Pedidos', 'bi-receipt'),
            $enabledItem('management.quotations', 'Cotizaciones', 'bi-file-earmark-text'),
            $enabledItem('management.customers', 'Clientes', 'bi-people'),
            $enabledItem('management.suppliers', 'Proveedores', 'bi-truck'),
            $enabledItem('management.purchases', 'Compras', 'bi-cart-plus'),
            $enabledItem('management.returns', 'Devoluciones y garantías', 'bi-arrow-counterclockwise'),
        ]],
        ['key' => 'inventario', 'label' => 'Inventario y Logística', 'icon' => 'bi-boxes', 'items' => [
            $enabledItem('management.products', 'Productos', 'bi-box-seam'),
            $enabledItem('management.categories', 'Categorías', 'bi-diagram-3'),
            $enabledItem('management.inventory', 'Existencias y movimientos', 'bi-boxes'),
            $enabledItem('management.inventory.preview.transfers', 'Transferencias', 'bi-arrow-left-right'),
            $enabledItem('management.inventory.preview.shipments', 'Envíos y entregas', 'bi-truck'),
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
        ]],
        ['key' => 'analisis', 'label' => 'Análisis y Auditoría', 'icon' => 'bi-bar-chart-line', 'items' => [
            $enabledItem('management.reports.preview.global', 'Reportes globales', 'bi-bar-chart'),
            $enabledItem('management.audit.preview.operations', 'Auditoría de operaciones', 'bi-shield-check'),
        ]],
        ['key' => 'configuracion', 'label' => 'Configuración del Sistema', 'icon' => 'bi-gear', 'items' => [
            $enabledItem('management.general_settings.preview', 'Configuración general', 'bi-gear'),
            $enabledItem('management.integrations.preview', 'Integraciones', 'bi-plug'),
            $enabledItem('management.notifications.preview', 'Notificaciones del sistema', 'bi-bell'),
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
