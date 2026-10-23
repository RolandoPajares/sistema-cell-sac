@extends('layouts.tienda')

@section('body_class', 'modulo-carrito')
@section('title', 'Carrito')

@section('content')
<section>
    <div class="container">
        <h1>Tu carrito</h1>
        @if($lines->isEmpty())
            <div class="empty panel">
                Tu carrito está vacío.<br><br>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}"><i class="bi bi-bag" aria-hidden="true"></i> Explorar productos</a>
            </div>
        @else
            <div class="panel table-wrap">
                <table class="table">
                    <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody>
                        @foreach($lines as $line)
                            @php($imagePath = $line['product']->images->first()?->ruta_imagen ?: $line['product']->url_imagen)
                            <tr>
                                <td class="cart-product-cell">
                                    @if($imagePath)<img src="{{ asset('assets/cell-legacy/'.ltrim(preg_replace('~^assets/~', '', $imagePath), '/')) }}" alt="" loading="lazy">@else<span class="cart-product-placeholder" aria-hidden="true"><i class="bi bi-box-seam"></i></span>@endif
                                    <b>{{ $line['product']->marca }} {{ $line['product']->nombre }}</b>
                                </td>
                                <td>S/ {{ number_format((float) $line['product']->effective_price, 2) }}</td>
                                <td>
                                    <form class="qty" method="post" action="{{ route('cart.update', $line['product']) }}">@csrf @method('PATCH')<input type="number" name="cantidad" min="0" max="{{ $line['product']->existencias }}" value="{{ $line['quantity'] }}"><button class="btn btn-ghost" type="submit">Actualizar</button></form>
                                </td>
                                <td>S/ {{ number_format($line['subtotal'], 2) }}</td>
                                <td><form method="post" action="{{ route('cart.remove', $line['product']) }}">@csrf<button class="btn btn-ghost" type="submit"><i class="bi bi-trash3" aria-hidden="true"></i> Quitar</button></form></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="cart-summary">
                    <div>
                        <form method="post" action="{{ route('cart.clear') }}">@csrf<button class="btn btn-ghost" type="submit"><i class="bi bi-x-circle" aria-hidden="true"></i> Vaciar</button></form>
                        <form class="inline-form" method="post" action="{{ route('cart.coupon') }}">@csrf<label>Cupón<input name="codigo" value="{{ session('coupon_code') }}" maxlength="50"></label><button class="btn btn-ghost" type="submit">Aplicar</button></form>
                    </div>
                    <div><b>Total: <span class="price">S/ {{ number_format($total, 2) }}</span></b><a class="btn btn-primary" href="{{ route('checkout.form') }}"><i class="bi bi-arrow-right" aria-hidden="true"></i> Continuar</a></div>
                </div>
            </div>
            <section class="smart-cart panel">
                <div class="section-head"><div><span class="eyebrow">Carrito inteligente</span><h2><i class="bi bi-cart-check" aria-hidden="true"></i> Completa tu compra</h2><p>Agrega productos complementarios desde el catálogo para completar tu compra.</p></div></div>
                <div class="compatibility-note"><i class="bi bi-shield-check"></i><div><b>Control de compatibilidad</b><p>El stock, los precios y la compatibilidad se validan nuevamente al confirmar el pedido.</p></div></div>
            </section>
        @endif
    </div>
</section>
@endsection
