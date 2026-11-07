<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Celulares y accesorios originales de MD Technology Digital Cell.">
        <title>@yield('title', 'MD Technology Cell')</title>

        @vite(['resources/css/layouts/auth/principal.css', 'resources/js/layouts/auth/principal.js'])
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
        @stack('styles')
    </head>
    <body class="@yield('body_class')">
        @include('layouts.auth.encabezado')

        @unless(request()->routeIs('home'))
            @include('components.publico.publicidad')
        @endunless

        @include('layouts.auth.mensajes')

        <main>@yield('content')</main>

        @include('layouts.tienda.pie-pagina')
        <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
        @include('components.inteligencia.asistente.flotante')
        @stack('scripts')
    </body>
</html>
