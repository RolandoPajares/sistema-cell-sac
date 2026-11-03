{{-- Sección "Promociones" del cliente minorista (se abre desde el menú con #promociones) --}}
{{-- Muestra cupones, productos en oferta y campañas activas de la base de datos --}}
@php
    $ahora = now();

    // cupones activos y vigentes
    $cupones = \App\Models\Comercio\Cupon::where('activo', true)
        ->where('inicia_en', '<=', $ahora)
        ->where('finaliza_en', '>=', $ahora)
        ->orderBy('finaliza_en')
        ->get();

    // productos con precio de oferta
    $ofertas = \App\Models\Catalogo\Producto::where('activo', true)
        ->whereNotNull('precio_oferta')
        ->whereColumn('precio_oferta', '<', 'precio_original')
        ->orderByDesc('precio_original')
        ->take(6)
        ->get();

    // campañas publicitarias activas
    $campanias = \App\Models\Marketing\Campania::where('activo', true)
        ->where('inicia_en', '<=', $ahora)
        ->where('finaliza_en', '>=', $ahora)
        ->get();
@endphp

<section class="promo-cliente" aria-labelledby="promo-titulo">
    <nav class="cliente-migas" aria-label="Ruta de navegación">
        <span>Panel</span><i class="bi bi-chevron-right"></i><span>Ofertas y beneficios</span><i class="bi bi-chevron-right"></i><strong>Promociones</strong>
    </nav>

    <header class="promo-cliente-banner">
        <div>
            <span class="promo-cliente-etiqueta"><i class="bi bi-tag"></i> Promociones</span>
            <h1 id="promo-titulo">Ofertas pensadas para ti</h1>
            <p>Usa tus cupones al pagar y aprovecha los precios especiales antes de que terminen.</p>
        </div>
        <div class="promo-cliente-contador">
            <strong>{{ $cupones->count() + $ofertas->count() }}</strong>
            <span>promociones activas</span>
        </div>
    </header>

    {{-- cupones --}}
    <article class="promo-cliente-caja" data-cliente-ancla="cupones">
        <h2><i class="bi bi-ticket-perforated"></i> Cupones disponibles</h2>
        <div class="promo-cliente-cupones">
            @forelse ($cupones as $cupon)
                <div class="promo-cliente-cupon">
                    <div class="promo-cliente-cupon-valor">
                        {{ $cupon->tipo_descuento === 'porcentaje' ? number_format((float) $cupon->valor, 0).'%' : 'S/ '.number_format((float) $cupon->valor, 0) }}
                        <small>de descuento</small>
                    </div>
                    <div class="promo-cliente-cupon-info">
                        <code>{{ $cupon->codigo }}</code>
                        <small>
                            @if ((float) $cupon->minimo_compra > 0)
                                Compra mínima S/ {{ number_format((float) $cupon->minimo_compra, 2) }} ·
                            @endif
                            Vence el {{ $cupon->finaliza_en->format('d/m/Y') }}
                        </small>
                    </div>
                    <button type="button" class="promo-cliente-copiar" data-copiar-cupon="{{ $cupon->codigo }}">
                        <i class="bi bi-clipboard"></i> Copiar
                    </button>
                </div>
            @empty
                <p class="promo-cliente-vacio"><i class="bi bi-ticket"></i> Por ahora no hay cupones activos. ¡Vuelve pronto!</p>
            @endforelse
        </div>
    </article>

    {{-- productos en oferta --}}
    <article class="promo-cliente-caja" data-cliente-ancla="ofertas">
        <div class="promo-cliente-titulo">
            <h2><i class="bi bi-fire"></i> Productos en oferta</h2>
            <a href="{{ route('catalog.index') }}">Ver catálogo <i class="bi bi-chevron-right"></i></a>
        </div>
        <div class="promo-cliente-productos">
            @forelse ($ofertas as $producto)
                @php
                    // misma ruta de imagen que usa el catálogo
                    $imagen = $producto->url_imagen ? ltrim(preg_replace('~^assets/~', '', $producto->url_imagen), '/') : null;
                @endphp
                <a class="promo-cliente-producto" href="{{ route('catalog.show', $producto->id) }}">
                    @if ($producto->descuento)
                        <span class="promo-cliente-descuento">{{ $producto->descuento }}</span>
                    @endif
                    @if ($imagen)
                        <img src="{{ asset('assets/cell-legacy/'.$imagen) }}" alt="{{ $producto->nombre }}" loading="lazy">
                    @else
                        <span class="promo-cliente-sin-imagen"><i class="bi bi-phone"></i></span>
                    @endif
                    <small>{{ $producto->marca }}</small>
                    <b>{{ $producto->nombre }}</b>
                    <div class="promo-cliente-precios">
                        <del>S/ {{ number_format((float) $producto->precio_original, 2) }}</del>
                        <strong>S/ {{ number_format((float) $producto->precio_oferta, 2) }}</strong>
                    </div>
                </a>
            @empty
                <p class="promo-cliente-vacio"><i class="bi bi-bag"></i> No hay productos en oferta en este momento.</p>
            @endforelse
        </div>
    </article>

    {{-- campañas --}}
    @if ($campanias->isNotEmpty())
        <div class="promo-cliente-campanias" data-cliente-ancla="campanias">
            @foreach ($campanias as $campania)
                <article class="promo-cliente-campania">
                    <div>
                        <h3>{{ $campania->titulo }}</h3>
                        <p>{{ $campania->descripcion }}</p>
                        <small>Hasta el {{ $campania->finaliza_en->format('d/m/Y') }}</small>
                    </div>
                    <a href="{{ route('catalog.index') }}">{{ $campania->texto_boton }} <i class="bi bi-arrow-right"></i></a>
                </article>
            @endforeach
        </div>
    @endif
</section>
