@php
    $routeLinks = [];
    foreach ($links ?? [] as $link) {
        $routeLinks[$link['route']] = $link;
    }

    $enabledItem = static function (string $route, string $label, string $icon) use ($routeLinks): array {
        $existing = $routeLinks[$route] ?? [];

        return [
            'route' => $route,
            'label' => $existing['label'] ?? $label,
            'icon' => $existing['icon'] ?? $icon,
            'enabled' => \Illuminate\Support\Facades\Route::has($route),
        ];
    };
    $disabledItem = static fn (string $label, string $icon): array => [
        'route' => null,
        'label' => $label,
        'icon' => $icon,
        'enabled' => false,
    ];

    $groups = [
        [
            'key' => 'catalogo',
            'label' => 'Catálogo',
            'icon' => 'bi-grid-3x3-gap',
            'items' => [
                $enabledItem('management.products', 'Productos', 'bi-box-seam'),
                $enabledItem('management.categories', 'Categorías', 'bi-diagram-3'),
                $enabledItem('management.inventory', 'Inventario', 'bi-boxes'),
            ],
        ],
        [
            'key' => 'operaciones',
            'label' => 'Operaciones comerciales',
            'icon' => 'bi-briefcase',
            'items' => [
                $enabledItem('management.orders', 'Pedidos', 'bi-receipt'),
                $enabledItem('management.sales', 'Ventas', 'bi-cart-check'),
                $enabledItem('management.purchases', 'Compras', 'bi-cart-plus'),
                $enabledItem('management.quotations', 'Cotizaciones', 'bi-file-earmark-text'),
                $disabledItem('Envíos y entregas', 'bi-truck'),
                $enabledItem('management.returns', 'Devoluciones y garantías', 'bi-arrow-counterclockwise'),
            ],
        ],
        [
            'key' => 'relaciones',
            'label' => 'Relaciones comerciales',
            'icon' => 'bi-people',
            'items' => [
                $enabledItem('management.customers', 'Clientes', 'bi-people'),
                $enabledItem('management.suppliers', 'Proveedores', 'bi-truck'),
                $enabledItem('management.support', 'Soporte y reclamos', 'bi-headset'),
            ],
        ],
        [
            'key' => 'marketing',
            'label' => 'Marketing',
            'icon' => 'bi-megaphone',
            'items' => [
                $enabledItem('management.campaigns', 'Campañas', 'bi-megaphone'),
                $enabledItem('smart.ads', 'Publicidad', 'bi-bullseye'),
                $enabledItem('management.coupons', 'Cupones y promociones', 'bi-ticket-perforated'),
                $enabledItem('management.segments', 'Segmentos de clientes', 'bi-diagram-3'),
            ],
        ],
        [
            'key' => 'finanzas',
            'label' => 'Finanzas',
            'icon' => 'bi-wallet2',
            'items' => [
                $enabledItem('management.payments', 'Pagos', 'bi-credit-card'),
                $enabledItem('management.billing', 'Facturación', 'bi-receipt-cutoff'),
                $enabledItem('management.refunds', 'Reembolsos', 'bi-arrow-return-left'),
            ],
        ],
        [
            'key' => 'analisis',
            'label' => 'Análisis',
            'icon' => 'bi-bar-chart-line',
            'items' => [
                $enabledItem('management.reports', 'Reportes', 'bi-bar-chart'),
                $enabledItem('management.financial_reports', 'Reportes financieros', 'bi-graph-up-arrow'),
                $enabledItem('management.customer_analytics', 'Analítica de clientes', 'bi-person-lines-fill'),
                $enabledItem('management.marketing_metrics', 'Métricas de marketing', 'bi-bullseye'),
            ],
        ],
        [
            'key' => 'sistema',
            'label' => 'Administración del sistema',
            'icon' => 'bi-shield-lock',
            'items' => [
                $enabledItem('management.users', 'Usuarios', 'bi-person-gear'),
                $enabledItem('management.roles_permissions', 'Roles y permisos', 'bi-key'),
                $enabledItem('management.audit', 'Auditoría', 'bi-shield-check'),
                $enabledItem('management.notifications', 'Notificaciones', 'bi-bell'),
                $enabledItem('management.integrations', 'Integraciones', 'bi-plug'),
                $enabledItem('management.backups', 'Respaldos y mantenimiento', 'bi-database-gear'),
            ],
        ],
        [
            'key' => 'configuracion',
            'label' => 'Configuración',
            'icon' => 'bi-gear',
            'items' => [
                $enabledItem('management.company_settings', 'Datos de empresa', 'bi-building'),
                $enabledItem('management.commercial_settings', 'Configuración comercial', 'bi-sliders'),
                $enabledItem('management.security_settings', 'Seguridad', 'bi-shield-check'),
                $enabledItem('management.general_settings', 'Configuración general', 'bi-gear'),
            ],
        ],
    ];
@endphp

<div class="admin-menu-desplegable" data-admin-menu-desplegable>
    <a class="admin-menu-desplegable__dashboard {{ request()->routeIs('management.dashboard') ? 'is-active' : '' }}" href="{{ route('management.dashboard') }}" title="Dashboard" @if (request()->routeIs('management.dashboard')) aria-current="page" @endif>
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span class="admin-menu-desplegable__label">Dashboard</span>
    </a>

    <div class="admin-menu-desplegable__groups">
        @foreach ($groups as $group)
            @php
                $groupIsActive = false;
                foreach ($group['items'] as $item) {
                    if ($item['enabled'] && $item['route'] && (request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'))) {
                        $groupIsActive = true;
                        break;
                    }
                }
                $groupIsOpen = $groupIsActive;
            @endphp
            <section class="admin-menu-desplegable__group {{ $groupIsActive ? 'is-active' : '' }} {{ $groupIsOpen ? 'is-open' : '' }}" data-admin-menu-group>
                <button class="admin-menu-desplegable__toggle" type="button" aria-expanded="{{ $groupIsOpen ? 'true' : 'false' }}" aria-controls="admin-menu-submenu-{{ $group['key'] }}" data-admin-menu-toggle>
                    <span class="admin-menu-desplegable__toggle-main"><i class="bi {{ $group['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $group['label'] }}</span></span>
                    <i class="bi bi-chevron-down admin-menu-desplegable__chevron" aria-hidden="true"></i>
                </button>
                <div class="admin-menu-desplegable__submenu" id="admin-menu-submenu-{{ $group['key'] }}" data-admin-menu-submenu @if (! $groupIsOpen) hidden @endif>
                    @foreach ($group['items'] as $item)
                        @php
                            $itemIsActive = $item['enabled'] && $item['route'] && (request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*'));
                        @endphp
                        @if ($item['enabled'] && $item['route'])
                            <a class="admin-menu-desplegable__item {{ $itemIsActive ? 'is-active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @if ($itemIsActive) aria-current="page" @endif>
                                <span class="admin-menu-desplegable__label">{{ $item['label'] }}</span>
                            </a>
                        @else
                            <span class="admin-menu-desplegable__item admin-menu-desplegable__item--disabled" aria-disabled="true" aria-label="{{ $item['label'] }}. Próximamente." title="Próximamente">
                                <span class="admin-menu-desplegable__label">{{ $item['label'] }}</span>
                            </span>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
