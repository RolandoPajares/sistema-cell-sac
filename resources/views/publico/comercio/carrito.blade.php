@extends('layouts.tienda.principal')

@section('body_class', 'modulo-carrito')
@section('title', 'Carrito de compras')

@section('content')
<section class="cart-page">
    <div class="container">
        <nav class="cart-breadcrumbs" aria-label="Ruta de navegación">
            <a href="{{ route('home') }}"><i class="bi bi-house-door" aria-hidden="true"></i> Inicio</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span aria-current="page">Carrito de compras</span>
        </nav>

        <header class="cart-page-heading">
            <div class="cart-page-heading-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></div>
            <div>
                <h1>Tu carrito de compras</h1>
                <p>Revisa tus productos, ajusta cantidades y finaliza tu compra.</p>
            </div>
        </header>

        @if($lines->isEmpty())
            <div class="cart-empty-card panel">
                <i class="bi bi-cart-x" aria-hidden="true"></i>
                <h2>Tu carrito está vacío</h2>
                <p>Explora nuestro catálogo y encuentra el equipo ideal para ti.</p>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}"><i class="bi bi-bag" aria-hidden="true"></i> Explorar productos</a>
            </div>
        @else
            <div class="cart-layout">
                <div class="cart-main-column">
                    <div class="cart-lines-card panel">
                        <div class="cart-lines-head" aria-hidden="true">
                            <span>Producto</span><span>Precio</span><span>Cantidad</span><span>Subtotal</span><span></span>
                        </div>
                        <div class="cart-lines-list" data-cart-lines>
                            @foreach($lines as $line)
                                <article class="cart-line" data-cart-line="{{ $line['product']->getKey() }}">
                                    <div class="cart-line-product">
                                        @if($line['product']->url_imagen_principal)
                                            <img src="{{ $line['product']->url_imagen_principal }}" alt="{{ $line['product']->marca }} {{ $line['product']->nombre }}" loading="lazy">
                                        @else
                                            <span class="cart-product-placeholder" aria-hidden="true"><i class="bi bi-box-seam"></i></span>
                                        @endif
                                        <div class="cart-line-copy">
                                            <strong>{{ $line['product']->nombre }}</strong>
                                            <span>{{ $line['product']->marca }} <i class="bi bi-dot" aria-hidden="true"></i> {{ $line['product']->category?->nombre ?: $line['product']->categoria }}</span>
                                            @if($line['variant'])<small>Variante: {{ $line['variant']->nombre_color }}{{ $line['variant']->codigo_sku ? ' · SKU '.$line['variant']->codigo_sku : '' }}</small>@endif
                                            <em><i class="bi bi-check-circle-fill" aria-hidden="true"></i> En stock</em>
                                        </div>
                                    </div>
                                    <div class="cart-line-price">S/ {{ number_format((float) $line['product']->effective_price, 2) }}</div>
                                    <form class="qty cart-line-quantity" data-cart-action="update" method="post" action="{{ route('cart.update', $line['product']) }}">
                                        @csrf
                                        @method('PATCH')
                                        <label class="visually-hidden" for="cantidad-{{ $line['product']->getKey() }}">Cantidad de {{ $line['product']->nombre }}</label>
                                        <button type="button" data-qty-step="-1" aria-label="Disminuir cantidad"><i class="bi bi-dash" aria-hidden="true"></i></button>
                                        <input id="cantidad-{{ $line['product']->getKey() }}" type="number" name="cantidad" min="1" max="{{ $line['disponible'] }}" value="{{ $line['quantity'] }}">
                                        <button type="button" data-qty-step="1" aria-label="Aumentar cantidad"><i class="bi bi-plus" aria-hidden="true"></i></button>
                                        <button class="cart-quantity-submit" type="submit">Actualizar</button>
                                    </form>
                                    <strong class="cart-line-subtotal" data-line-subtotal>S/ {{ number_format($line['subtotal'], 2) }}</strong>
                                    <form method="post" action="{{ route('cart.remove', $line['product']) }}" data-cart-action="remove">
                                        @csrf
                                        <button class="cart-remove-button" type="submit" aria-label="Eliminar {{ $line['product']->nombre }}"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                    </form>
                                </article>
                            @endforeach
                        </div>
                        <div class="cart-lines-actions">
                            <a href="{{ route('catalog.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i> Seguir comprando</a>
                            <form method="post" action="{{ route('cart.clear') }}">
                                @csrf
                                <button type="submit"><i class="bi bi-x-circle" aria-hidden="true"></i> Vaciar carrito</button>
                            </form>
                        </div>
                    </div>

                    <section class="cart-security-note">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        <div><strong>Compra segura y protegida</strong><span>La disponibilidad y los precios se validan nuevamente al confirmar tu pedido.</span></div>
                    </section>
                </div>

                <aside class="cart-summary-card panel" aria-labelledby="cart-summary-title">
                    <h2 id="cart-summary-title"><i class="bi bi-receipt" aria-hidden="true"></i> Resumen del pedido</h2>
                    <div class="cart-summary-row"><span>Subtotal ({{ $lines->sum('quantity') }} productos)</span><strong data-cart-total>S/ {{ number_format($total, 2) }}</strong></div>
                    <div class="cart-summary-row cart-summary-discount"><span>Descuento</span><strong>- S/ 0.00</strong></div>
                    <form class="cart-coupon-form" method="post" action="{{ route('cart.coupon') }}">
                        @csrf
                        <label class="visually-hidden" for="cart-coupon">Código de cupón</label>
                        <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                        <input id="cart-coupon" name="codigo" value="{{ old('codigo', session('coupon_code')) }}" maxlength="50" placeholder="SALE10" autocomplete="off" @error('codigo') aria-invalid="true" @enderror>
                        <button type="submit">Aplicar</button>
                    </form>
                    <div class="cart-summary-total"><span>Total a pagar</span><strong data-cart-grand-total>S/ {{ number_format($total, 2) }}</strong></div>
                    @guest
                        <a class="btn btn-primary cart-checkout-button" href="{{ route('checkout.form') }}" data-checkout-trigger data-login-url="{{ route('login', ['continuar' => 'checkout']) }}" data-register-url="{{ route('register', ['continuar' => 'checkout']) }}"><i class="bi bi-lock-fill" aria-hidden="true"></i> Continuar con la compra <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    @else
                        <a class="btn btn-primary cart-checkout-button" href="{{ route('checkout.form') }}"><i class="bi bi-lock-fill" aria-hidden="true"></i> Continuar con la compra <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    @endguest
                    <p class="cart-protection"><i class="bi bi-shield-check" aria-hidden="true"></i> Compra 100% segura y protegida</p>
                </aside>
            </div>

            <section class="cart-benefits panel" aria-label="Beneficios de compra">
                <article><span><i class="bi bi-truck" aria-hidden="true"></i></span><div><strong>Envíos a todo el Perú</strong><small>Rápidos, seguros y con seguimiento.</small></div></article>
                <article><span><i class="bi bi-patch-check" aria-hidden="true"></i></span><div><strong>Productos originales</strong><small>Con garantía oficial.</small></div></article>
                <article><span><i class="bi bi-headset" aria-hidden="true"></i></span><div><strong>Asesoría personalizada</strong><small>Atención local en Bagua.</small></div></article>
            </section>
        @endif
    </div>
</section>
@endsection
