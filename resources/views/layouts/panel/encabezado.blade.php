@php
    $currentUser = auth()->user();
    $isAdministrator = $currentUser?->isRole('administrador') ?? false;
    $adminBrandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
    $adminAccountRoute = $isAdministrator ? 'management.account' : 'account.profile.show';
@endphp

<header class="admin-topbar">
    <a class="admin-topbar-brand" href="{{ route($adminBrandRoute) }}" title="MD Technology Digital Cell">
        <img class="admin-topbar-logo" src="{{ asset('assets/cell-legacy/img/marca/logoadmin.png') }}" alt="TECHNOLOGY CELL">
    </a>
    <button class="icon-btn admin-menu-toggle" type="button" aria-label="Abrir menú del panel" aria-controls="adminSidebar" aria-expanded="false" data-admin-sidebar-toggle>
        <i class="bi bi-list"></i>
    </button>
    <label class="admin-global-search">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input type="search" data-admin-global-search placeholder="Buscar productos, clientes, campañas..." aria-label="Buscar productos, clientes o campañas">
    </label>
    <button class="admin-ai-button" type="button" data-admin-assistant-toggle aria-controls="adminAssistantPanel" aria-expanded="false"><i class="bi bi-stars" aria-hidden="true"></i><span>Asistente IA</span></button>
    @include('layouts.panel.notificaciones')
    <button class="admin-topbar-icon" type="button" aria-label="Ayuda"><i class="bi bi-question-circle" aria-hidden="true"></i></button>
    <details class="admin-user-menu">
        <summary class="admin-user" aria-label="Abrir menú de usuario">
            @if ($currentUser?->foto_perfil)
                <img class="admin-avatar admin-avatar--photo" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($currentUser->foto_perfil) }}" alt="">
            @else
                <span class="admin-avatar">{{ strtoupper(substr($currentUser->nombre, 0, 1)) }}</span>
            @endif
            <span class="admin-user-copy"><b>{{ $currentUser->nombre }}</b><small>{{ str_replace('_', ' ', $currentUser->rol) }}</small></span>
            <i class="bi bi-chevron-down admin-user-chevron" aria-hidden="true"></i>
        </summary>
        <div class="admin-user-menu-panel">
            <a href="{{ route($adminAccountRoute) }}"><i class="bi bi-person-circle" aria-hidden="true"></i><span>Mi perfil</span></a>
            @if ($isAdministrator)
                <a href="{{ route('management.account.preferences') }}"><i class="bi bi-sliders" aria-hidden="true"></i><span>Preferencias</span></a>
                <a href="{{ route('management.account.security') }}"><i class="bi bi-shield-lock" aria-hidden="true"></i><span>Seguridad de la cuenta</span></a>
                <a href="{{ route('management.account.sessions') }}"><i class="bi bi-pc-display-horizontal" aria-hidden="true"></i><span>Sesiones y dispositivos</span></a>
                <a href="{{ route('management.account.notifications') }}"><i class="bi bi-bell" aria-hidden="true"></i><span>Mis notificaciones</span></a>
                <a href="{{ route('management.account.activity') }}"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Mi actividad reciente</span></a>
            @endif
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Cerrar sesión</span></button>
            </form>
        </div>
    </details>
</header>
