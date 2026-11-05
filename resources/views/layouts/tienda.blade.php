<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Celulares y accesorios originales de MD Technology Digital Cell.">
        <title>@yield('title', 'MD Technology Cell')</title>

        @vite(['resources/css/tienda.css', 'resources/js/tienda.js'])
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
        @stack('styles')
    </head>
    <body class="@yield('body_class')">
        @include('layouts.publico.encabezado')

        @unless(request()->routeIs('home'))
            @include('components.publico.publicidad')
        @endunless

        @if (session('status'))<div class="container flash-success" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="container flash-error" role="alert">{{ $errors->first() }}</div>@endif

        <main>@yield('content')</main>

        @include('layouts.publico.pie-pagina')
        <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
        @stack('scripts')
    </body>
</html>
