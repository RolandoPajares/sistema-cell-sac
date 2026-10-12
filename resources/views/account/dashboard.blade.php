@php($isCustomer = str_starts_with($role, 'cliente_'))
@extends($isCustomer ? 'layouts.store' : 'layouts.admin')

@section('title', 'Mi cuenta')
@section('body_class', $isCustomer ? 'modulo-cuenta' : 'modulo-cuenta-admin')

@if ($isCustomer)
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/cuenta/cuenta.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/overrides/cuenta-sidebar-blue.css') }}?v=20261011-light-blue">
    @endpush
@endif

@section('content')
    @if ($isCustomer)
        <section class="cuenta-fondo">
            <div class="container cuenta-migas"><a href="{{ route('home') }}">Inicio</a><i class="bi bi-chevron-right"></i><span>Mi cuenta</span></div>
            <div class="container cuenta-diseno">
                <aside class="cuenta-lateral">
                    <div class="cuenta-identidad"><span><i class="bi bi-person-fill"></i></span><div><b>Hola, {{ auth()->user()->nombre }}</b><small>Cliente {{ str_replace('cliente_', '', $role) }}</small><em><i></i> Cuenta activa</em></div></div>
                    <nav>
                        @foreach ($links as $link)
                            @if (\Illuminate\Support\Facades\Route::has($link['route']) && ! in_array($link['route'], ['home', 'dashboard'], true))
                                <a class="{{ request()->routeIs($link['route']) ? 'activo' : '' }}" href="{{ route($link['route']) }}"><i class="bi {{ $link['icon'] }}"></i><span>{{ $link['label'] }}</span></a>
                            @endif
                        @endforeach
                    </nav>
                    <form method="post" action="{{ route('logout') }}">@csrf<button type="submit"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button></form>
                    <a class="cuenta-anuncio" href="{{ route('catalog.index') }}"><b>Tecnología que te acompaña siempre</b><span>Ver catálogo <i class="bi bi-arrow-right"></i></span></a>
                </aside>

                <main class="cuenta-contenido">
                    <header class="cuenta-banner">
                        <div><span class="cuenta-banner-etiqueta"><i class="bi bi-stars"></i> Cliente {{ str_replace('cliente_', '', $role) }}</span><h1>Tu espacio en CELL SAC</h1><p>Gestiona tus pedidos, consulta el catálogo y encuentra tu próximo equipo.</p><div class="cuenta-banner-botones"><a class="cuenta-btn-blanco" href="{{ route('catalog.index') }}"><i class="bi bi-grid"></i> Ver catálogo</a><a class="cuenta-btn-borde" href="{{ route('recommendations') }}"><i class="bi bi-stars"></i> Probar SmartMatch</a></div></div>
                        <img src="{{ asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png') }}" alt="Celulares y accesorios">
                    </header>
                    <div class="cuenta-resumen"><article><i class="bi bi-receipt"></i><div><strong>{{ $orderCount }}</strong><span>Pedidos registrados</span></div></article><article><i class="bi bi-clock-history"></i><div><strong>{{ $pendingCount }}</strong><span>Pedidos en proceso</span></div></article><article><i class="bi bi-cart3"></i><div><strong>{{ $cartUnits }}</strong><span>Unidades en carrito</span></div></article><article><i class="bi bi-person-check"></i><div><strong>Activa</strong><span>Estado de cuenta</span></div></article></div>

                    <section class="cuenta-panel"><div class="cuenta-panel-titulo"><h2>Pedidos recientes</h2><a href="{{ route('orders.index') }}">Ver todos <i class="bi bi-chevron-right"></i></a></div><div class="lista-pedidos-cuenta">
                        @forelse($orders->take(5) as $order)
                            @php($firstProduct = $order->details->first()?->product)
                            <article><div class="pedido-codigo"><b>#{{ $order->id }}</b><small>{{ $order->creado_en?->format('d/m/Y') }}</small></div><div class="pedido-producto"><span><i class="bi bi-phone"></i></span><div><b>{{ $firstProduct?->nombre ?: 'Pedido CELL SAC' }}</b><small>{{ $order->details->count() }} producto(s)</small></div></div><span class="pedido-estado"><i class="bi bi-check-circle"></i>{{ $order->estado }}</span><div class="pedido-total"><b>S/ {{ number_format((float) $order->total, 2) }}</b><small>Total</small></div><a href="{{ route('orders.show', $order) }}">Ver detalle</a></article>
                        @empty
                            <div class="cuenta-vacio"><i class="bi bi-receipt"></i><p>Aún no tienes pedidos registrados.</p><a class="btn btn-primary" href="{{ route('catalog.index') }}">Explorar catálogo</a></div>
                        @endforelse
                    </div></section>

                    <div class="cuenta-dos-columnas"><section class="cuenta-panel"><div class="cuenta-panel-titulo"><h2>Accesos rápidos</h2></div><div class="cuenta-accesos"><a href="{{ route('orders.index') }}"><i class="bi bi-box-seam"></i> Mis pedidos</a><a href="{{ route('cart.index') }}"><i class="bi bi-cart3"></i> Mi carrito</a><a href="{{ route('comparison') }}"><i class="bi bi-columns-gap"></i> Comparar equipos</a><a href="{{ route('pages.contact') }}"><i class="bi bi-headset"></i> Contactar asesor</a></div></section><section class="cuenta-panel cuenta-recomendado"><i class="bi bi-stars"></i><div><h2>Recomendado para ti</h2><p>Encuentra equipos según tus necesidades y presupuesto.</p><a class="btn btn-primary" href="{{ route('recommendations') }}">Probar SmartMatch</a></div></section></div>

                    <div class="cuenta-dos-columnas"><form method="post" action="{{ route('account.profile') }}" class="cuenta-panel">@csrf @method('PUT')<div class="cuenta-panel-titulo"><h2>Mis datos</h2></div><label>Nombre<input name="nombre" value="{{ old('nombre', auth()->user()->nombre) }}" required maxlength="120"></label><label>Correo<input type="email" name="correo" value="{{ old('correo', auth()->user()->correo) }}" required maxlength="160"></label><button class="btn btn-primary" type="submit">Guardar datos</button></form><form method="post" action="{{ route('account.password') }}" class="cuenta-panel">@csrf @method('PUT')<div class="cuenta-panel-titulo"><h2>Seguridad</h2></div><label>Contraseña actual<input type="password" name="password_actual" required></label><label>Nueva contraseña<input type="password" name="password" required minlength="10"></label><label>Confirmar contraseña<input type="password" name="password_confirmation" required></label><button class="btn btn-primary" type="submit">Cambiar contraseña</button></form></div>
                </main>
            </div>
        </section>
    @else
        <section class="admin-page">
            <header class="admin-page-head"><div><span class="eyebrow">{{ str_replace('_', ' ', $role) }}</span><h1>Hola, {{ auth()->user()->nombre }}</h1><p>Administra tu operación desde un solo lugar.</p></div></header>
            <div class="admin-kpi-grid"><article class="admin-kpi-card"><span class="admin-kpi-icon"><i class="bi bi-person-badge"></i></span><div><small>Rol activo</small><strong>{{ str_replace('_', ' ', $role) }}</strong><span>Perfil autorizado</span></div></article><article class="admin-kpi-card admin-kpi-card--verde"><span class="admin-kpi-icon"><i class="bi bi-shield-check"></i></span><div><small>Seguridad</small><strong>Protegida</strong><span>Sesión validada</span></div></article><article class="admin-kpi-card admin-kpi-card--violeta"><span class="admin-kpi-icon"><i class="bi bi-clock"></i></span><div><small>Sesión</small><strong>Activa</strong><span>Acceso vigente</span></div></article></div>
            @if ($role === 'administrador')<div class="two-column"><form method="post" action="{{ route('management.account.profile') }}" class="form-card">@csrf @method('PUT')<h2>Perfil</h2><label>Nombre<input name="nombre" value="{{ old('nombre', auth()->user()->nombre) }}" required maxlength="120"></label><label>Correo<input type="email" name="correo" value="{{ old('correo', auth()->user()->correo) }}" required maxlength="160"></label><button class="btn btn-primary">Guardar perfil</button></form><form method="post" action="{{ route('management.account.password') }}" class="form-card">@csrf @method('PUT')<h2>Cambiar contraseña</h2><label>Contraseña actual<input type="password" name="password_actual" required></label><label>Nueva contraseña<input type="password" name="password" required minlength="10"></label><label>Confirmar contraseña<input type="password" name="password_confirmation" required></label><button class="btn btn-primary">Actualizar contraseña</button></form></div>@endif
        </section>
    @endif
@endsection
