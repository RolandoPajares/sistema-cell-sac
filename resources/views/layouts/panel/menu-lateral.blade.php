@php
    $isAdministrator = auth()->user()?->rol === 'administrador';
    $brandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
    $accountRoute = $isAdministrator ? 'management.account' : 'dashboard';
    $menuViews = [
        'administrador' => 'paneles.administrador.menu',
        'compras_logistica' => 'paneles.compras-logistica.menu',
        'marketing' => 'paneles.marketing.menu',
        'ventas_mayoristas' => 'paneles.ventas-mayoristas.menu',
        'ventas_minoristas' => 'paneles.ventas-minoristas.menu',
        'cliente_mayorista' => 'paneles.cliente-mayorista.menu',
        'cliente_minorista' => 'paneles.cliente-minorista.menu',
    ];
    $menuView = $menuViews[auth()->user()?->rol] ?? 'components.panel.menu-enlaces';
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
        @include($menuView, ['links' => $links ?? []])
    </nav>

    <div class="admin-sidebar-footer">
        <a class="{{ request()->routeIs($accountRoute) ? 'is-active' : '' }}" href="{{ route($accountRoute) }}" title="{{ $isAdministrator ? 'Configuración' : 'Mi cuenta' }}">
            <i class="bi bi-gear" aria-hidden="true"></i>
            <span>{{ $isAdministrator ? 'Configuración' : 'Mi cuenta' }}</span>
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
