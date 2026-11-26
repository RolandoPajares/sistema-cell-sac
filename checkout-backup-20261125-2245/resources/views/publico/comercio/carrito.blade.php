@extends('layouts.tienda.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-carrito')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Carrito')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section>
    <div class="container">
        <h1>Tu carrito</h1>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if($lines->isEmpty())
            <div class="empty panel">
                Tu carrito está vacío.<br><br>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}"><i class="bi bi-bag" aria-hidden="true"></i> Explorar productos</a>
            </div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            <div class="panel table-wrap">
                <table class="table">
                    <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                        @foreach($lines as $line)
                            <tr>
                                <td class="cart-product-cell">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                                    @if($line['product']->url_imagen_principal)<img src="{{ $line['product']->url_imagen_principal }}" alt="" loading="lazy">@else<span class="cart-product-placeholder" aria-hidden="true"><i class="bi bi-box-seam"></i></span>@endif
                                    <div><b>{{ $line['product']->marca }} {{ $line['product']->nombre }}</b>@if($line['variant'])<small>Variante: {{ $line['variant']->nombre_color }}{{ $line['variant']->codigo_sku ? ' · SKU '.$line['variant']->codigo_sku : '' }}</small>@endif</div>
                                </td>
                                <td>S/ {{ number_format((float) $line['product']->effective_price, 2) }}</td>
                                <td>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                                    <form
                                        class="qty"
                                        method="post"
                                        action="{{ route('cart.update', $line['product']) }}"
                                    >
{{-- proteccion CSRF que valida el origen del formulario. --}}
                                        @csrf
{{-- metodo HTTP que permite simular PUT, PATCH o DELETE desde el formulario. --}}
                                        @method('PATCH')
                                        <input
                                            type="number"
                                            name="cantidad"
                                            min="0"
                                            max="{{ $line['disponible'] }}"
                                            value="{{ $line['quantity'] }}"
                                        >
                                        <button class="btn btn-ghost" type="submit">Actualizar</button>
                                    </form>
                                </td>
                                <td>S/ {{ number_format($line['subtotal'], 2) }}</td>
                                <td>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                                    <form method="post" action="{{ route('cart.remove', $line['product']) }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                                        @csrf
                                        <button class="btn btn-ghost" type="submit">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                            Quitar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="cart-summary">
                    <div>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                        <form method="post" action="{{ route('cart.clear') }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                            @csrf
                            <button class="btn btn-ghost" type="submit">
                                <i class="bi bi-x-circle" aria-hidden="true"></i>
                                Vaciar
                            </button>
                        </form>

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                        <form class="inline-form" method="post" action="{{ route('cart.coupon') }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                            @csrf
                            <label>
                                Cupón
                                <input
                                    name="codigo"
                                    value="{{ session('coupon_code') }}"
                                    maxlength="50"
                                >
                            </label>
                            <button class="btn btn-ghost" type="submit">Aplicar</button>
                        </form>
                    </div>
                    <div><b>Total: <span class="price">S/ {{ number_format($total, 2) }}</span></b><a class="btn btn-primary" href="{{ route('checkout.form') }}"><i class="bi bi-arrow-right" aria-hidden="true"></i> Continuar</a></div>
                </div>
            </div>
            <section class="smart-cart panel">
                <div class="section-head"><div><span class="eyebrow">Carrito inteligente</span><h2><i class="bi bi-cart-check" aria-hidden="true"></i> Completa tu compra</h2><p>Agrega productos complementarios desde el catálogo para completar tu compra.</p></div></div>
                <div class="compatibility-note"><i class="bi bi-shield-check"></i><div><b>Validación del pedido</b><p>La disponibilidad del almacén autorizado y los precios se validan nuevamente al confirmar el pedido.</p></div></div>
            </section>
        @endif
    </div>
</section>
@endsection
