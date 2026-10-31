{{-- Sección principal del panel del cliente minorista --}}
{{-- Responsable: Integrante 4 - Sebastián --}}
@php
    $usuario = auth()->user();
    $pedidos = $orders ?? collect();
    $totalGastado = $pedidos->whereNotIn('estado', ['Cancelado'])->sum(fn ($pedido) => (float) $pedido->total);

    // pasos que sigue un pedido, para dibujar la barrita de progreso
    $pasos = ['Pendiente', 'En proceso', 'Enviado', 'Entregado'];

    // si hubo error con la contraseña se abre directo la pestaña de seguridad
    $abrirSeguridad = $errors->hasAny(['password', 'password_actual']);
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
        <section class="minorista-caja minorista-pedidos">
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
                <article class="minorista-pedido {{ $cancelado ? 'minorista-pedido--cancelado' : '' }}">
                    <div class="minorista-pedido-arriba">
                        <span class="minorista-pedido-icono"><i class="bi bi-phone"></i></span>
                        <div class="minorista-pedido-nombre">
                            <b>{{ $primerProducto?->nombre ?: 'Pedido CELL SAC' }}</b>
                            <small>Pedido #{{ $pedido->id }} · {{ $pedido->creado_en?->format('d/m/Y') }} · {{ $pedido->details->count() }} producto(s)</small>
                        </div>
                        <div class="minorista-pedido-total">
                            <b>S/ {{ number_format((float) $pedido->total, 2) }}</b>
                            <a href="{{ route('orders.show', $pedido) }}">Ver detalle</a>
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

    {{-- Cuenta: se cambia entre pestañas con JS --}}
    <section class="minorista-caja minorista-cuenta">
        <div class="minorista-pestanas" role="tablist">
            <button type="button" class="{{ $abrirSeguridad ? '' : 'activo' }}" data-pestana-minorista="datos" role="tab"><i class="bi bi-person"></i> Mis datos</button>
            <button type="button" class="{{ $abrirSeguridad ? 'activo' : '' }}" data-pestana-minorista="seguridad" role="tab"><i class="bi bi-shield-lock"></i> Seguridad</button>
        </div>

        <form method="post" action="{{ route('account.profile') }}" class="minorista-formulario" data-contenido-minorista="datos" @if ($abrirSeguridad) hidden @endif>
            @csrf
            @method('PUT')
            <label>
                Nombre
                <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required maxlength="120">
            </label>
            <label>
                Correo
                <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required maxlength="160">
            </label>
            <button class="minorista-boton minorista-boton--azul" type="submit"><i class="bi bi-save"></i> Guardar datos</button>
        </form>

        <form method="post" action="{{ route('account.password') }}" class="minorista-formulario" data-contenido-minorista="seguridad" @if (! $abrirSeguridad) hidden @endif>
            @csrf
            @method('PUT')
            <label>
                Contraseña actual
                <span class="admin-password-input">
                    <input type="password" name="password_actual" required>
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <label>
                Nueva contraseña
                <span class="admin-password-input">
                    <input type="password" name="password" required minlength="10">
                    <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                </span>
            </label>
            <label>
                Confirmar contraseña
                <input type="password" name="password_confirmation" required>
            </label>
            <button class="minorista-boton minorista-boton--negro" type="submit"><i class="bi bi-key"></i> Cambiar contraseña</button>
        </form>
    </section>
</section>
