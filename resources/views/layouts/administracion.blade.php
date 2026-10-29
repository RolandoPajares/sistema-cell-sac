<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', 'Panel CELL SAC')</title>
        @vite(['resources/css/panel.css', 'resources/js/panel.js'])
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
        @stack('styles')
    </head>
    <body class="admin-body admin-theme @yield('body_class')" data-panel-role="{{ auth()->user()?->rol }}">
        <div class="admin-layout admin-app" data-admin-app>
            @include('layouts.panel.menu-lateral', ['links' => $sidebarLinks ?? []])
            <div class="admin-shell admin-content-shell">
                @include('layouts.panel.encabezado')
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

