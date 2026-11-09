<header class="site-header">
    <nav class="navbar container">
        <a class="brand" href="<?php echo e(route('home')); ?>">
            <img src="<?php echo e(asset('assets/cell-legacy/img/marca/logo.jpeg')); ?>" alt="MD Technology Digital Cell">
        </a>
        <button
            class="menu-toggle"
            type="button"
            aria-label="Abrir menú"
            aria-controls="navegacionPrincipal"
            aria-expanded="false"
        >
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <div class="navlinks" id="navegacionPrincipal">
            <a class="<?php echo e(request()->routeIs('home') ? 'active' : ''); ?>" href="<?php echo e(route('home')); ?>">Inicio</a>
            <a class="<?php echo e(request()->routeIs('catalog.*') ? 'active' : ''); ?>" href="<?php echo e(route('catalog.index')); ?>">Catálogo</a>
            <a class="<?php echo e(request()->routeIs('recommendations') ? 'active' : ''); ?>" href="<?php echo e(route('recommendations')); ?>">SmartMatch</a>
            <a class="<?php echo e(request()->routeIs('comparison') ? 'active' : ''); ?>" href="<?php echo e(route('comparison')); ?>">Comparador</a>
            <a class="<?php echo e(request()->routeIs('pages.about') ? 'active' : ''); ?>" href="<?php echo e(route('pages.about')); ?>">Nosotros</a>
            <a class="<?php echo e(request()->routeIs('pages.contact*') ? 'active' : ''); ?>" href="<?php echo e(route('pages.contact')); ?>">Contacto</a>
        </div>
        <form class="header-search" method="get" action="<?php echo e(route('catalog.index')); ?>" role="search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input
                type="search"
                name="q"
                value="<?php echo e(request('q')); ?>"
                placeholder="Buscar productos, marcas..."
                aria-label="Buscar productos o marcas"
            >
        </form>
        <div class="nav-actions">
            <a class="icon-btn" href="<?php echo e(route('cart.index')); ?>" title="Carrito" aria-label="Ver carrito">
                <i class="bi bi-cart3" aria-hidden="true"></i>
                <sup><?php echo e(collect((array) session('cart', []))->sum(fn (mixed $linea): int => is_array($linea) ? (int) ($linea['cantidad'] ?? 0) : (int) $linea)); ?></sup>
            </a>
            <?php if(auth()->guard()->check()): ?>
                <a class="header-account" href="<?php echo e(route('dashboard')); ?>">
                    <i class="bi bi-person-circle" aria-hidden="true"></i>
                    <span><b>Mi cuenta</b><small><?php echo e(auth()->user()->nombre); ?></small></span>
                </a>
                <form method="post" action="<?php echo e(route('logout')); ?>">
                    <?php echo csrf_field(); ?>
                    <button
                        class="icon-btn"
                        type="submit"
                        title="Salir"
                        aria-label="Cerrar sesión"
                    >
                        <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>
            <?php else: ?>
                <a class="btn btn-ghost" href="<?php echo e(route('login')); ?>">Ingresar</a>
                <a class="btn btn-primary" href="<?php echo e(route('register')); ?>">Registrarme</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
