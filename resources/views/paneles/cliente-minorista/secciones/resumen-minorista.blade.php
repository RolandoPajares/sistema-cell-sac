{{-- Sección principal del panel del cliente minorista --}}
{{-- Responsable: Integrante 4 - Sebastián --}}
@php
    $usuario = auth()->user();
    $pedidos = $orders ?? collect();
    $totalGastado = $pedidos->whereNotIn('estado', ['Cancelado'])->sum(fn ($pedido) => (float) $pedido->total);

    // pasos que sigue un pedido, para dibujar la barrita de progreso
    $pasos = ['Pendiente', 'En proceso', 'Enviado', 'Entregado'];

@endphp

<section class="minorista-panel" aria-labelledby="minorista-titulo">

    {{-- Banner de bienvenida --}}
    <header class="minorista-banner">
        <div class="minorista-banner-texto">
            <span class="minorista-etiqueta"><i class="bi bi-person-heart"></i> Cliente minorista</span>
            <h1 id="minorista-titulo">Hola, {{ $usuario->nombre }}</h1>
            <p>Revisa tus compras, sigue tus pedidos y encuentra tu próximo celular.</p>
            <div class="minorista-banner-botones">
                <a class="minorista-boton minorista-boton--blanco" href="{{ route('catalog.index') }}"><i class="bi bi-grid-3x3-gap"></i> Ver catálogo</a>
                <a class="minorista-boton minorista-boton--borde" href="{{ route('cart.index') }}"><i class="bi bi-cart3"></i> Mi carrito ({{ $cartUnits ?? 0 }})</a>
            </div>
        </div>
        <img src="{{ asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png') }}" alt="Celulares y accesorios">
    </header>

    {{-- Resumen en números --}}
    <div class="minorista-resumen">
        <article>
            <i class="bi bi-bag-check"></i>
            <div><strong>{{ $orderCount ?? 0 }}</strong><span>Mis pedidos</span></div>
        </article>
        <article>
            <i class="bi bi-hourglass-split"></i>
            <div><strong>{{ $pendingCount ?? 0 }}</strong><span>En camino o en proceso</span></div>
        </article>
        <article>
            <i class="bi bi-cart3"></i>
            <div><strong>{{ $cartUnits ?? 0 }}</strong><span>Productos en carrito</span></div>
        </article>
        <article>
            <i class="bi bi-wallet2"></i>
            <div><strong>S/ {{ number_format($totalGastado, 2) }}</strong><span>Total de compras</span></div>
        </article>
    </div>

    <div class="minorista-columnas">
        {{-- Pedidos recientes con seguimiento --}}
        <section class="minorista-caja minorista-pedidos" data-cliente-ancla="seguimiento">
            <div class="minorista-caja-titulo">
                <h2><i class="bi bi-truck"></i> Sigue tus pedidos</h2>
                <a href="{{ route('orders.index') }}">Ver todos <i class="bi bi-chevron-right"></i></a>
            </div>

            @forelse ($pedidos->take(4) as $pedido)
                @php
                    $primerProducto = $pedido->details->first()?->product;
                    $pasoActual = array_search($pedido->estado, $pasos);
                    $cancelado = $pedido->estado === 'Cancelado';
                @endphp
                <article class="minorista-pedido {{ $cancelado ? 'minorista-pedido--cancelado' : '' }}" data-pedido-resumen>
                    <div class="minorista-pedido-arriba">
                        <span class="minorista-pedido-icono"><i class="bi bi-phone"></i></span>
                        <div class="minorista-pedido-nombre">
                            <b>{{ $primerProducto?->nombre ?: 'Pedido CELL SAC' }}</b>
                            <small>Pedido #{{ $pedido->id }} · {{ $pedido->creado_en?->format('d/m/Y') }} · {{ $pedido->details->count() }} producto(s)</small>
                        </div>
                        <div class="minorista-pedido-total">
                            <b>S/ {{ number_format((float) $pedido->total, 2) }}</b>
                            <button type="button" class="minorista-ver" data-cliente-modal-abrir="modal-pedido-{{ $pedido->id }}">Ver detalle</button>
                        </div>
                    </div>

                    @if ($cancelado)
                        <p class="minorista-pedido-aviso"><i class="bi bi-x-circle"></i> Este pedido fue cancelado.</p>
                    @else
                        <ol class="minorista-progreso">
                            @foreach ($pasos as $indice => $paso)
                                <li class="{{ $pasoActual !== false && $indice <= $pasoActual ? 'hecho' : '' }}">
                                    <span></span>{{ $paso }}
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </article>
            @empty
                <div class="minorista-vacio">
                    <i class="bi bi-bag"></i>
                    <p>Todavía no has hecho ninguna compra.</p>
                    <a class="minorista-boton minorista-boton--azul" href="{{ route('catalog.index') }}">Explorar catálogo</a>
                </div>
            @endforelse
        </section>

        {{-- Columna derecha: accesos y recomendación --}}
        <div class="minorista-lateral">
            <section class="minorista-caja">
                <div class="minorista-caja-titulo"><h2><i class="bi bi-lightning-charge"></i> Accesos rápidos</h2></div>
                <div class="minorista-accesos">
                    <a href="{{ route('orders.index') }}"><i class="bi bi-box-seam"></i> Mis pedidos</a>
                    <a href="{{ route('cart.index') }}"><i class="bi bi-cart3"></i> Mi carrito</a>
                    <a href="{{ route('comparison') }}"><i class="bi bi-columns-gap"></i> Comparar</a>
                    <a href="{{ route('pages.contact') }}"><i class="bi bi-headset"></i> Ayuda</a>
                </div>
            </section>

            <section class="minorista-caja minorista-smart">
                <i class="bi bi-stars"></i>
                <h2>¿No sabes qué celular elegir?</h2>
                <p>Responde unas preguntas y SmartMatch te recomienda equipos según tu presupuesto.</p>
                <a class="minorista-boton minorista-boton--blanco" href="{{ route('recommendations') }}">Probar SmartMatch</a>
            </section>
        </div>
    </div>
</section>

{{-- Modales: detalle rápido de cada pedido --}}
@foreach ($pedidos->take(4) as $pedido)
    <dialog class="cliente-modal" id="modal-pedido-{{ $pedido->id }}" aria-labelledby="modal-pedido-titulo-{{ $pedido->id }}">
        <div class="cliente-modal__contenido">
            <header class="cliente-modal__cabecera">
                <span class="cliente-modal__icono"><i class="bi bi-receipt"></i></span>
                <div class="cliente-modal__titulos">
                    <h2 id="modal-pedido-titulo-{{ $pedido->id }}">Pedido #{{ $pedido->id }}</h2>
                    <p>{{ $pedido->creado_en?->format('d/m/Y H:i') }} · Estado: {{ $pedido->estado }}</p>
                </div>
                <button type="button" class="cliente-modal__cerrar" data-cliente-modal-cerrar aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </header>
            <div class="cliente-modal__cuerpo">
                <ul class="cliente-modal__lista">
                    @foreach ($pedido->details as $detalle)
                        <li>
                            <i class="bi bi-phone"></i>
                            <div>
                                <b>{{ $detalle->product?->nombre ?? 'Producto' }}</b>
                                <small>{{ $detalle->cantidad }} × S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</small>
                            </div>
                            <b>S/ {{ number_format($detalle->cantidad * (float) $detalle->precio_unitario, 2) }}</b>
                        </li>
                    @endforeach
                </ul>
                <div class="cliente-modal__totales">
                    @if ((float) ($pedido->descuento ?? 0) > 0)
                        <div><span>Descuento {{ $pedido->cupon_codigo ? '('.$pedido->cupon_codigo.')' : '' }}</span><span>- S/ {{ number_format((float) $pedido->descuento, 2) }}</span></div>
                    @endif
                    <div><span>Total pagado</span><strong>S/ {{ number_format((float) $pedido->total, 2) }}</strong></div>
                </div>
            </div>
            <footer class="cliente-modal__pie">
                <button type="button" class="cliente-modal__boton cliente-modal__boton--suave" data-cliente-modal-cerrar>Cerrar</button>
                <a class="cliente-modal__boton" href="{{ route('orders.show', $pedido) }}"><i class="bi bi-box-arrow-up-right"></i> Ver pedido completo</a>
            </footer>
        </div>
    </dialog>
@endforeach
