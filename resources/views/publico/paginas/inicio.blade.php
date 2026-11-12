@extends('layouts.tienda.principal')

@section('body_class', 'modulo-inicio publicidad-inicio')
@section('title', 'Inicio | MD Technology Cell')

@section('content')
<section class="home-hero home-hero-banner">
    <a class="home-hero-banner-link" href="{{ route('catalog.index') }}" aria-label="Ver catálogo de celulares y accesorios">
        <img class="home-hero-banner-image" src="{{ asset('assets/cell-legacy/img/publico/inicio/banners/bannerInicio.png') }}" alt="Celulares y accesorios de MD Technology Digital Cell">
    </a>
    <div class="home-hero-banner-content">
        <span class="home-hero-banner-kicker">Tecnología original en Bagua</span>
        <h1>Tu próximo celular está  <span>en MD Technology Cell</span></h1>
        <p>Celulares y accesorios originales con atención local y precios para ti.</p>
        <div class="home-hero-banner-actions">
            <a class="btn btn-primary" href="{{ route('catalog.index', $idCategoriaCelulares ? ['categoria' => $idCategoriaCelulares] : []) }}">Ver celulares <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            <a class="btn btn-ghost" href="{{ route('catalog.index', $idCategoriaAccesorios ? ['categoria' => $idCategoriaAccesorios] : []) }}"><i class="bi bi-tag" aria-hidden="true"></i> Explorar accesorios</a>
        </div>
    </div>
</section>

<section class="home-assurance" aria-label="Beneficios de compra">
    <div class="container hero-trust">
        <span><i class="bi bi-shield-check" aria-hidden="true"></i><b>Productos originales</b><small>Con garantía</small></span>
        <span><i class="bi bi-truck" aria-hidden="true"></i><b>Envíos en Bagua</b><small>Rápido y seguro</small></span>
        <span><i class="bi bi-headset" aria-hidden="true"></i><b>Asesoría para elegir</b><small>Atención local</small></span>
    </div>
</section>

<section class="brand-strip" aria-label="Marcas disponibles">
    <div class="container brands">
        <div class="brand-pill samsung">SAMSUNG</div>
        <div class="brand-pill apple"><i class="bi bi-apple" aria-hidden="true"></i> Apple</div>
        <div class="brand-pill oppo">oppo</div>
        <div class="brand-pill xiaomi"><b>mi</b> XIAOMI</div>
        <div class="brand-pill honor">HONOR</div>
        <div class="brand-pill jbl">JBL</div>
        <div class="brand-pill beats"><span aria-hidden="true">b</span> Beats</div>
    </div>
</section>

<section class="smart-home-strip">
    <div class="container smart-row">
        <div class="smart-intro">
            <h2>Compra<br>más inteligente</h2>
            <p>Herramientas con IA y filtros avanzados para encontrar exactamente lo que necesitas.</p>
        </div>
        <div class="smart-home-grid">
            <a class="smart-tile smart-purple" href="{{ route('recommendations') }}"><span><i class="bi bi-lightbulb-fill" aria-hidden="true"></i></span><div><b>SmartMatch</b><small>Encuentra el equipo ideal según tu estilo de vida.</small></div><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
            <a class="smart-tile smart-orange" href="{{ route('comparison') }}"><span><i class="bi bi-columns-gap" aria-hidden="true"></i></span><div><b>Comparador</b><small>Compara hasta 3 equipos con puntajes.</small></div><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
            <a class="smart-tile smart-gold" href="{{ route('smart.wholesale') }}"><span><i class="bi bi-cash-coin" aria-hidden="true"></i></span><div><b>Modo emprendedor</b><small>Arma una compra mayorista según tu presupuesto.</small></div><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
        </div>
    </div>
</section>

<section class="featured-products">
    <div class="container">
        <div class="section-head home-section-head">
            <div><h2>Los más populares en oferta</h2><p>Ofertas vigentes seleccionadas de todas las categorías.</p></div>
            <a href="{{ route('catalog.index') }}" class="home-view-all">Ver todos los productos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="product-grid home-product-grid" @if($productosDestacados->isEmpty()) hidden @endif>
            @foreach($productosDestacados as $producto)
                <article class="product-card home-product-card">
                    <a class="home-product-image-link" href="{{ route('catalog.show', $producto) }}" aria-label="Ver {{ $producto->marca }} {{ $producto->nombre }}">
                        <div class="product-art home-product-art">
                            <span class="badge">{{ $producto->etiqueta ?: 'Oferta' }}</span>
                            @if($producto->url_imagen_principal)
                                <img class="home-product-photo" src="{{ $producto->url_imagen_principal }}" alt="{{ $producto->marca }} {{ $producto->nombre }}" loading="lazy">
                            @else
                                <div class="home-product-placeholder" role="img" aria-label="Imagen no disponible"><i class="bi bi-phone" aria-hidden="true"></i><span>{{ $producto->marca }}</span></div>
                            @endif
                        </div>
                    </a>
                    <div class="product-body home-product-body">
                        <div class="product-meta"><span>{{ $producto->marca }}</span><span>{{ $producto->category?->nombre ?: $producto->categoria }}</span></div>
                        <h3>{{ $producto->nombre }}</h3>
                        <div class="home-product-specs">{{ $producto->display_specifications }}</div>
                        @if($producto->display_discount > 0)<div class="home-product-pricing"><del>S/ {{ number_format($producto->display_original_price, 2) }}</del><span>-{{ $producto->display_discount }}%</span></div>@endif
                        <div class="price">S/ {{ number_format((float) $producto->precio_oferta, 2) }}</div>
                        <div class="stock">{{ $producto->existencias_disponibles }} unidades disponibles</div>
                        <div class="home-product-actions">
                            @auth
                                <form
                                    class="home-product-buy-form"
                                    action="{{ route('cart.add', $producto) }}"
                                    method="post"
                                >
                                    @csrf
                                    <input type="hidden" name="cantidad" value="1">
                                    <button class="btn btn-primary home-product-action" type="submit">
                                        <i class="bi bi-cart3" aria-hidden="true"></i>
                                        Comprar
                                    </button>
                                </form>
                            @else
                                <details class="home-product-buy-choice">
                                    <summary class="btn btn-primary home-product-action"><i class="bi bi-cart3" aria-hidden="true"></i> Comprar</summary>
                                    <div class="home-product-buy-options">
                                        <a class="btn btn-ghost" href="{{ route('login') }}">Iniciar sesión</a>
                                        <form action="{{ route('cart.add', $producto) }}" method="post">
                                            @csrf
                                            <input type="hidden" name="cantidad" value="1">
                                            <button class="btn btn-primary" type="submit">Continuar como invitado</button>
                                        </form>
                                    </div>
                                </details>
                            @endauth
                            <a class="btn btn-ghost home-product-details" href="{{ route('catalog.show', $producto) }}"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalles</a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
        <p class="home-products-empty" @if($productosDestacados->isNotEmpty()) hidden @endif>No hay productos disponibles por el momento.</p>
    </div>
</section>

<section class="home-shortcuts">
    <div class="container shortcut-grid">
        <a href="{{ route('catalog.index') }}"><i class="bi bi-percent" aria-hidden="true"></i><span><b>Ofertas de la semana</b><small>Equipos seleccionados con precios especiales</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        <a href="{{ route('catalog.index') }}"><i class="bi bi-laptop" aria-hidden="true"></i><span><b>Laptops para productividad</b><small>Trabajo, estudio y más</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        <a href="{{ route('catalog.index') }}"><i class="bi bi-phone" aria-hidden="true"></i><span><b>Celulares que te conectan</b><small>Últimos lanzamientos</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        <a href="{{ route('catalog.index') }}"><i class="bi bi-headphones" aria-hidden="true"></i><span><b>Accesorios originales</b><small>Completa tu experiencia</small></span><i class="bi bi-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>

@endsection
