<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Celulares y accesorios originales de MD Technology Digital Cell.">
        <title>@yield('title', 'MD Technology Cell')</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/estilos.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/estructura/navbar.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/estructura/sitio.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/responsive/adaptable.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/componentes/publicidad_dinamica.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/overrides/footer.css') }}?v=20261010-compact4">
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
        @stack('styles')
    </head>
    <body class="@yield('body_class')">
        <header class="site-header">
            <nav class="navbar container">
                <a class="brand" href="{{ route('home') }}">
                    <img src="{{ asset('assets/cell-legacy/img/marca/logo.jpeg') }}" alt="MD Technology Digital Cell">
                </a>
                <button class="menu-toggle" type="button" aria-label="Abrir menú" aria-controls="navegacionPrincipal" aria-expanded="false">
                    <i class="bi bi-list" aria-hidden="true"></i>
                </button>
                <div class="navlinks" id="navegacionPrincipal">
                    <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Inicio</a>
                    <a class="{{ request()->routeIs('catalog.*') ? 'active' : '' }}" href="{{ route('catalog.index') }}">Catálogo</a>
                    <a class="{{ request()->routeIs('recommendations') ? 'active' : '' }}" href="{{ route('recommendations') }}">SmartMatch</a>
                    <a class="{{ request()->routeIs('comparison') ? 'active' : '' }}" href="{{ route('comparison') }}">Comparador</a>
                    <a class="{{ request()->routeIs('pages.about') ? 'active' : '' }}" href="{{ route('pages.about') }}">Nosotros</a>
                    <a class="{{ request()->routeIs('pages.contact*') ? 'active' : '' }}" href="{{ route('pages.contact') }}">Contacto</a>
                </div>
                <form class="header-search" method="get" action="{{ route('catalog.index') }}" role="search">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <input type="search" name="q" value="{{ request('q') }}" placeholder="Buscar productos, marcas..." aria-label="Buscar productos o marcas">
                </form>
                <div class="nav-actions">
                    <a class="icon-btn" href="{{ route('cart.index') }}" title="Carrito" aria-label="Ver carrito">
                        <i class="bi bi-cart3" aria-hidden="true"></i>
                        <sup>{{ collect((array) session('cart', []))->sum() }}</sup>
                    </a>
                    @auth
                        <a class="header-account" href="{{ route('dashboard') }}">
                            <i class="bi bi-person-circle" aria-hidden="true"></i>
                            <span><b>Mi cuenta</b><small>{{ auth()->user()->nombre }}</small></span>
                        </a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="icon-btn" type="submit" title="Salir" aria-label="Cerrar sesión"><i class="bi bi-box-arrow-right" aria-hidden="true"></i></button>
                        </form>
                    @else
                        <a class="btn btn-ghost" href="{{ route('login') }}">Ingresar</a>
                        <a class="btn btn-primary" href="{{ route('register') }}">Registrarme</a>
                    @endauth
                </div>
            </nav>
        </header>

        @unless(request()->routeIs('home'))
            @include('components.public-advertising')
        @endunless

        @if (session('status'))<div class="container flash-success" role="status">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="container flash-error" role="alert">{{ $errors->first() }}</div>@endif

        <main>@yield('content')</main>

        <footer class="site-footer">
            <div class="container footer-grid">
                <section class="footer-brand">
                    <a class="footer-brand-link" href="{{ route('home') }}" aria-label="MD Technology Digital Cell, inicio">
                        <span class="footer-brand-md" aria-hidden="true">M<span>D</span></span>
                        <span class="footer-wordmark"><strong>Technology</strong><small>DIGITAL CELL</small></span>
                    </a>
                    <p>Celulares y accesorios originales con atención cercana en Bagua.</p>
                    <div class="footer-social-links" aria-label="Redes sociales">
                        <a class="footer-social-link" href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                        <a class="footer-social-link" href="https://www.tiktok.com/" target="_blank" rel="noopener noreferrer" aria-label="TikTok"><i class="bi bi-tiktok" aria-hidden="true"></i></a>
                        <a class="footer-social-link" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                    </div>
                </section>
                <nav class="footer-column" aria-label="Explora">
                    <h2>Explora</h2>
                    <a href="{{ route('catalog.index') }}"><i class="bi bi-bag" aria-hidden="true"></i> Catálogo</a>
                    <a href="{{ route('recommendations') }}"><i class="bi bi-stars" aria-hidden="true"></i> SmartMatch</a>
                    <a href="{{ route('comparison') }}"><i class="bi bi-sliders" aria-hidden="true"></i> Comparador</a>
                    <a href="{{ route('pages.about') }}"><i class="bi bi-people" aria-hidden="true"></i> Nosotros</a>
                </nav>
                <nav class="footer-column" aria-label="Ayuda">
                    <h2>Ayuda</h2>
                    <a href="{{ route('pages.contact') }}"><i class="bi bi-envelope" aria-hidden="true"></i> Contacto</a>
                    <a href="{{ route('pages.contact') }}#contact-name"><i class="bi bi-journal-text" aria-hidden="true"></i> Libro de reclamaciones</a>
                    <a href="{{ route('assistant') }}"><i class="bi bi-robot" aria-hidden="true"></i> Asistente IA</a>
                    <a href="{{ route('smart.wholesale') }}"><i class="bi bi-truck" aria-hidden="true"></i> Compras mayoristas</a>
                </nav>
                <section class="footer-column footer-visit">
                    <h2>Visítanos</h2>
                    <p><i class="bi bi-geo-alt" aria-hidden="true"></i><span>Bagua, Amazonas<br>Cerca de Plásticos Jireh</span></p>
                    <p><i class="bi bi-clock" aria-hidden="true"></i><span>Lun-Sáb<br>Horario comercial</span></p>
                    <a class="footer-map-link" href="https://www.google.com/maps/search/?api=1&amp;query=Bagua%2C%20Amazonas" target="_blank" rel="noopener noreferrer"><i class="bi bi-geo-alt" aria-hidden="true"></i> Cómo llegar <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                </section>
            </div>
            <div class="footer-bottom"><div class="container footer-bottom-inner"><span>&copy; 2026 MD Technology Digital Cell S.A.C.</span><span>Bagua, Amazonas</span></div></div>
        </footer>
        <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
        @stack('scripts')
    </body>
</html>
