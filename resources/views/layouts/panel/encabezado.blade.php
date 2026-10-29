@php
    $isAdministrator = auth()->user()?->rol === 'administrador';
    $adminBrandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
    $adminAccountRoute = $isAdministrator ? 'management.account' : 'dashboard';
@endphp

<header class="admin-topbar">
    <a class="admin-topbar-brand" href="{{ route($adminBrandRoute) }}" title="MD Technology Digital Cell">
        <img class="admin-topbar-logo" src="{{ asset('assets/cell-legacy/img/marca/logoadmin.png') }}" alt="TECHNOLOGY CELL">
    </a>
    <button
        class="icon-btn admin-menu-toggle"
        type="button"
        aria-label="Abrir menú del panel"
        aria-controls="adminSidebar"
        aria-expanded="false"
        data-admin-sidebar-toggle
    >
        <i class="bi bi-list"></i>
    </button>
    <label class="admin-global-search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input
            type="search"
            data-admin-global-search
            placeholder="Buscar productos, clientes, campañas..."
            aria-label="Buscar productos, clientes o campañas"
        >
    </label>
    <a class="admin-ai-button" href="{{ route('assistant') }}"><i class="bi bi-stars" aria-hidden="true"></i><span>Asistente IA</span></a>
    <button class="admin-topbar-icon" type="button" aria-label="Notificaciones">
        <i class="bi bi-bell" aria-hidden="true"></i>
    </button>
    <button class="admin-topbar-icon" type="button" aria-label="Ayuda">
        <i class="bi bi-question-circle" aria-hidden="true"></i>
    </button>
    <details class="admin-user-menu">
        <summary class="admin-user" aria-label="Abrir menú de usuario">
            <span class="admin-avatar">{{ strtoupper(substr(auth()->user()->nombre, 0, 1)) }}</span>
            <span class="admin-user-copy"><b>{{ auth()->user()->nombre }}</b><small>{{ str_replace('_', ' ', auth()->user()->rol) }}</small></span>
            <i class="bi bi-chevron-down admin-user-chevron" aria-hidden="true"></i>
        </summary>
        <div class="admin-user-menu-panel">
            <a href="{{ route($adminAccountRoute) }}"><i class="bi bi-gear" aria-hidden="true"></i><span>{{ $isAdministrator ? 'Configuración' : 'Mi cuenta' }}</span></a>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit">
                    <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </details>
</header>
