<section class="cuenta-fondo">
    <div class="container cuenta-migas"><a href="{{ route('dashboard') }}">Panel</a><i class="bi bi-chevron-right"></i><span>Mi cuenta</span></div>
    <div class="container cuenta-diseno">
        <main class="cuenta-contenido">
            <header class="cuenta-banner">
                <div>
                    <span class="cuenta-banner-etiqueta"><i class="bi bi-stars"></i> Cliente {{ str_replace('cliente_', '', $role) }}</span>
                    <h1>Tu espacio en CELL SAC</h1>
                    <p>Gestiona tus pedidos, consulta el catálogo y encuentra tu próximo equipo.</p>
                    <div class="cuenta-banner-botones">
                        <a class="cuenta-btn-blanco" href="{{ route('catalog.index') }}"><i class="bi bi-grid"></i> Ver catálogo</a>
                        <a class="cuenta-btn-borde" href="{{ route('recommendations') }}"><i class="bi bi-stars"></i> Probar SmartMatch</a>
                    </div>
                </div>
                <img src="{{ asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png') }}" alt="Celulares y accesorios">
            </header>

            <div class="cuenta-resumen">
                <article><i class="bi bi-receipt"></i><div><strong>{{ $orderCount }}</strong><span>Pedidos registrados</span></div></article>
                <article><i class="bi bi-clock-history"></i><div><strong>{{ $pendingCount }}</strong><span>Pedidos en proceso</span></div></article>
                <article><i class="bi bi-cart3"></i><div><strong>{{ $cartUnits }}</strong><span>Unidades en carrito</span></div></article>
                <article><i class="bi bi-person-check"></i><div><strong>Activa</strong><span>Estado de cuenta</span></div></article>
            </div>

            <section class="cuenta-panel">
                <div class="cuenta-panel-titulo"><h2>Pedidos recientes</h2><a href="{{ route('orders.index') }}">Ver todos <i class="bi bi-chevron-right"></i></a></div>
                <div class="lista-pedidos-cuenta">
                    @forelse($orders->take(5) as $order)
                        @php($firstProduct = $order->details->first()?->product)
                        <article>
                            <div class="pedido-codigo"><b>#{{ $order->id }}</b><small>{{ $order->creado_en?->format('d/m/Y') }}</small></div>
                            <div class="pedido-producto"><span><i class="bi bi-phone"></i></span><div><b>{{ $firstProduct?->nombre ?: 'Pedido CELL SAC' }}</b><small>{{ $order->details->count() }} producto(s)</small></div></div>
                            <span class="pedido-estado"><i class="bi bi-check-circle"></i>{{ $order->estado }}</span>
                            <div class="pedido-total"><b>S/ {{ number_format((float) $order->total, 2) }}</b><small>Total</small></div>
                            <a href="{{ route('orders.show', $order) }}">Ver detalle</a>
                        </article>
                    @empty
                        <div class="cuenta-vacio"><i class="bi bi-receipt"></i><p>Aún no tienes pedidos registrados.</p><a class="btn btn-primary" href="{{ route('catalog.index') }}">Explorar catálogo</a></div>
                    @endforelse
                </div>
            </section>

            <div class="cuenta-dos-columnas">
                <section class="cuenta-panel">
                    <div class="cuenta-panel-titulo"><h2>Accesos rápidos</h2></div>
                    <div class="cuenta-accesos">
                        <a href="{{ route('orders.index') }}"><i class="bi bi-box-seam"></i> Mis pedidos</a>
                        <a href="{{ route('cart.index') }}"><i class="bi bi-cart3"></i> Mi carrito</a>
                        <a href="{{ route('comparison') }}"><i class="bi bi-columns-gap"></i> Comparar equipos</a>
                        <a href="{{ route('pages.contact') }}"><i class="bi bi-headset"></i> Contactar asesor</a>
                    </div>
                </section>
                <section class="cuenta-panel cuenta-recomendado">
                    <i class="bi bi-stars"></i>
                    <div><h2>Recomendado para ti</h2><p>Encuentra equipos según tus necesidades y presupuesto.</p><a class="btn btn-primary" href="{{ route('recommendations') }}">Probar SmartMatch</a></div>
                </section>
            </div>

        </main>
    </div>
</section>
