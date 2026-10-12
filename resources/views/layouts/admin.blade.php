<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Panel CELL SAC')</title>
        @vite(['resources/css/app.css', 'resources/css/admin-compat.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/estilos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/roles/internos/administrador.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/estructura/navbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/estructura/sitio.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comunes/panel.css') }}?v=20261011-light-blue">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/david/panel.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/responsive/adaptable.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/overrides/admin-reference.css') }}?v=20261011-reference7">
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
        @stack('styles')
    </head>
    <body class="admin-body admin-theme @yield('body_class')">
        @php
            $isAdministrator = auth()->user()?->rol === 'administrador';
            $adminBrandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
            $adminAccountRoute = $isAdministrator ? 'management.account' : 'dashboard';
        @endphp
        <div class="admin-layout admin-app" data-admin-app>
            @include('components.panel.sidebar', ['links' => $sidebarLinks ?? []])
            <div class="admin-shell admin-content-shell">
                <header class="admin-topbar">
                    <a class="admin-topbar-brand" href="{{ route($adminBrandRoute) }}" title="MD Technology Digital Cell">
                        <img class="admin-topbar-logo" src="{{ asset('assets/cell-legacy/img/marca/logoadmin.png') }}" alt="TECHNOLOGY CELL">
                    </a>
                    <button class="icon-btn admin-menu-toggle" type="button" aria-label="Abrir menú del panel" aria-controls="adminSidebar" aria-expanded="false" data-admin-sidebar-toggle><i class="bi bi-list"></i></button>
                    <label class="admin-global-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-admin-global-search placeholder="Buscar productos, clientes, campañas..." aria-label="Buscar productos, clientes o campañas"></label>
                    <a class="admin-ai-button" href="{{ route('assistant') }}"><i class="bi bi-stars" aria-hidden="true"></i><span>Asistente IA</span></a>
                    <button class="admin-topbar-icon" type="button" aria-label="Notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></button>
                    <button class="admin-topbar-icon" type="button" aria-label="Ayuda"><i class="bi bi-question-circle" aria-hidden="true"></i></button>
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
                                <button type="submit"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Cerrar sesión</span></button>
                            </form>
                        </div>
                    </details>
                </header>
                <main class="admin-main">
                    @if (session('status'))<div class="admin-alert admin-alert--success" role="status">{{ session('status') }}</div>@endif
                    @if ($errors->any())<div class="admin-alert admin-alert--error" role="alert">{{ $errors->first() }}</div>@endif
                    @yield('content')
                </main>
            </div>
        </div>
        <div class="sidebar-overlay admin-sidebar-backdrop" data-sidebar-close data-admin-sidebar-close></div>
        @stack('scripts')
    </body>
</html>
