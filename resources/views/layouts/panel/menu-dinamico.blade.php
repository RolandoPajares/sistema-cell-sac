@php
    $currentUser = auth()->user();
    $isAdministrator = $currentUser?->rol === 'administrador'
        || ($currentUser?->isRole('administrador') ?? false);
    $brandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
    $accountRoute = $isAdministrator ? 'management.account' : 'account.profile.show';
    $visibleRoutesPorRol = [
        'cliente_mayorista' => ['home', 'dashboard', 'recommendations', 'comparison', 'assistant', 'smart.optimizer', 'smart.wholesale', 'quotations.index'],
        'cliente_minorista' => ['catalog.index', 'dashboard', 'orders.index'],
        'compras_logistica' => ['home', 'dashboard', 'recommendations', 'comparison', 'assistant', 'smart.optimizer', 'management.inventory', 'management.suppliers', 'management.purchases'],
        'marketing' => ['home', 'dashboard', 'recommendations', 'comparison', 'assistant', 'smart.optimizer', 'management.campaigns', 'smart.ads', 'management.coupons', 'management.segments', 'management.reports', 'management.audit'],
        'ventas_mayoristas' => ['home', 'dashboard', 'recommendations', 'comparison', 'assistant', 'smart.optimizer', 'quotations.index'],
        'ventas_minoristas' => ['home', 'dashboard', 'orders.index', 'recommendations', 'comparison', 'assistant', 'smart.optimizer', 'management.inventory'],
    ];
    $usesDynamicMenu = ($links['dinamico'] ?? false) === true;
    $roleRoutes = $visibleRoutesPorRol[$currentUser?->rol] ?? null;
    $filteredLinks = [];
    if (! $usesDynamicMenu) {
        $filteredLinks = $roleRoutes === null
            ? ($links ?? [])
            : array_values(array_filter($links ?? [], static fn (array $link): bool => in_array($link['route'], $roleRoutes, true)));
    }
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <div class="admin-sidebar-brand">
        <a href="{{ route($brandRoute) }}" title="MD Technology Digital Cell">
            <span class="admin-brand-mark"><i class="bi bi-phone" aria-hidden="true"></i></span>
            <span class="admin-brand-copy">
                <strong>TECHNOLOGY <b>CELL</b></strong>
                <small>MD Technology Digital Cell S.A.C.</small>
            </span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        @if ($isAdministrator)
            @vite(['resources/css/paneles/dashboard.css'])
            {{-- El administrador usa un único menú administrativo, sin el menú legado duplicado. --}}
            @include('components.panel.menu-lateral-desplegable', ['links' => $links ?? []])
        @elseif ($usesDynamicMenu)
            @vite(['resources/css/paneles/dashboard.css'])
            <x-panel.menu-dinamico :menu="$links" />
        @else
            @include('components.panel.menu-enlaces', ['links' => $filteredLinks])
        @endif
    </nav>

    <div class="admin-sidebar-footer">
        <a class="{{ request()->routeIs($accountRoute) ? 'is-active' : '' }}" href="{{ route($accountRoute) }}" title="{{ $isAdministrator ? 'Configuración' : 'Mi cuenta' }}">
            <i class="bi bi-gear" aria-hidden="true"></i>
            <span>{{ $isAdministrator ? 'Configuración' : 'Mi perfil' }}</span>
        </a>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Cerrar sesión">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
@if ($isAdministrator)
    <div class="admin-sidebar-resizer" data-admin-sidebar-resizer role="separator" aria-orientation="vertical" tabindex="0" aria-label="Ajustar ancho de la barra lateral" aria-controls="adminSidebar" aria-valuemin="82" aria-valuemax="480" aria-valuenow="298"></div>
@endif
