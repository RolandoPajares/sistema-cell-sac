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
