@extends('layouts.tienda.principal')

@section('body_class', 'modulo-checkout')
@section('title', 'Finalizar pedido')

@section('content')
<section>
    <div class="container">
        <div class="auth-wrap panel">
            <h1>Finalizar pedido</h1>
            @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div><a class="btn btn-ghost" href="{{ route('orders.index') }}">Ver mis pedidos</a>@endif
            @if($lines->isEmpty())
                <p>No hay productos pendientes.</p>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}">Volver al catálogo</a>
            @else
                <p>Este proyecto registra una solicitud de compra; no procesa pagos en línea.</p>
                @foreach($lines as $line)<div class="data-row"><span>{{ $line['product']->nombre }} × {{ $line['quantity'] }}</span><strong>S/ {{ number_format($line['subtotal'], 2) }}</strong></div>@endforeach
                <div class="cart-total"><b>Total</b><strong>S/ {{ number_format($total, 2) }}</strong></div>
                @unless($puedeConfirmar)<p>La disponibilidad cambió. Revisa las cantidades en tu carrito antes de confirmar.</p>@endunless
                <form method="post" action="{{ route('checkout') }}">
                    @csrf
                    <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">
                    @guest
                        <fieldset class="checkout-guest-fields">
                            <legend>Datos de entrega</legend>
                            <label class="checkout-field">Nombre completo<input name="nombre" value="{{ old('nombre') }}" required maxlength="160" placeholder="Ingresa tu nombre completo" autocomplete="name"></label>
                            <label class="checkout-field">Correo electrónico<input type="email" name="correo" value="{{ old('correo') }}" required maxlength="160" inputmode="email" placeholder="correo@ejemplo.com" autocomplete="email"></label>
                            <label class="checkout-field">Teléfono<input name="telefono" value="{{ old('telefono') }}" maxlength="30" placeholder="Número de contacto" autocomplete="tel"></label>
                            <label class="checkout-field">Dirección de entrega<input name="direccion" value="{{ old('direccion') }}" required maxlength="255" placeholder="Av., calle o referencia principal" autocomplete="street-address"></label>
                            <label class="checkout-field checkout-field--wide">Referencia <small>(opcional)</small><textarea name="referencia" maxlength="500" placeholder="Número, piso, urbanización u otra indicación">{{ old('referencia') }}</textarea></label>
                        </fieldset>
                    @endguest
                    @if ($paymentMethods->isNotEmpty())
                        <label class="checkout-payment-label" for="medio_pago_id">Medio de pago</label>
                        <select id="medio_pago_id" name="medio_pago_id">
                            <option value="">Coordinar pago</option>
                            @foreach ($paymentMethods as $paymentMethod)
                                <option value="{{ $paymentMethod->id }}" @selected(old('medio_pago_id') == $paymentMethod->id)>{{ $paymentMethod->nombre }}</option>
                            @endforeach
                        </select>
                    @endif
                    <button class="btn btn-primary checkout-submit" @disabled(! $puedeConfirmar)>
                        Confirmar pedido
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
