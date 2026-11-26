@extends('layouts.tienda.principal')

@section('body_class', 'modulo-checkout')
@section('title', 'Finalizar pedido')

@section('content')
<section>
    <div class="container">
        <div class="auth-wrap panel">
            @if (session('status'))
                <a class="btn btn-ghost" href="{{ route('orders.index') }}">Ver mis pedidos</a>
            @endif

            @if ($lines->isEmpty())
                <p>No hay productos pendientes.</p>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}">Volver al catálogo</a>
            @else
                <div class="checkout-layout">
                    <div class="checkout-main-column">
                        <a class="checkout-back-to-cart" href="{{ route('cart.index') }}">
                            <i class="bi bi-arrow-left" aria-hidden="true"></i> Volver al carrito
                        </a>
                        <h1>Finalizar pedido</h1>

                        <ol class="checkout-progress" aria-label="Progreso de compra" data-checkout-progress>
                            <li class="is-active"><span>1</span> Identificación</li>
                            <li><span>2</span> Entrega</li>
                            <li><span>3</span> Pago</li>
                            <li><span>4</span> Confirmación</li>
                        </ol>

                        <p>Completa tu identificación, registra la dirección de entrega y selecciona cómo deseas pagar.</p>

                        @unless ($puedeConfirmar)
                            <p>La disponibilidad cambió. Revisa las cantidades en tu carrito antes de confirmar.</p>
                        @endunless

                        <form method="post" action="{{ route('checkout') }}" data-checkout-form data-checkout-wizard>
                            @csrf
                            <input type="hidden" name="checkout_token" value="{{ $checkoutToken }}">

                            @guest
                                <fieldset class="checkout-step checkout-identification-fields" data-checkout-step="identification">
                                    <legend>1. Identificación</legend>
                                    <label class="checkout-field">Nombre completo<input name="nombre" value="{{ old('nombre') }}" required maxlength="160" placeholder="Ingresa tu nombre completo" autocomplete="name"></label>
                                    <label class="checkout-field">Correo electrónico<input type="email" name="correo" value="{{ old('correo') }}" required maxlength="160" inputmode="email" placeholder="correo@ejemplo.com" autocomplete="email"></label>
                                    <label class="checkout-field checkout-field--wide">Teléfono<input name="telefono" value="{{ old('telefono') }}" maxlength="30" placeholder="Número de contacto" autocomplete="tel"></label>
                                </fieldset>
                            @else
                                <fieldset class="checkout-step checkout-identification-fields" data-checkout-step="identification">
                                    <legend>1. Identificación</legend>
                                    <div class="checkout-identity-summary">
                                        <strong>{{ auth()->user()->nombre }}</strong>
                                        <span>{{ auth()->user()->correo }}</span>
                                        <small>Compra asociada a tu cuenta activa.</small>
                                    </div>
                                </fieldset>
                            @endguest

                            <div class="checkout-step-actions" data-checkout-actions="identification">
                                <button class="btn btn-primary" type="button" data-checkout-next>Siguiente: Entrega <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                            </div>

                            <fieldset class="checkout-step checkout-delivery-fields" data-checkout-step="delivery" hidden>
                                <legend>2. Entrega</legend>
                                <label class="checkout-field checkout-field--wide">Dirección de entrega<input name="direccion" value="{{ old('direccion') }}" required maxlength="255" placeholder="Av., calle o referencia principal" autocomplete="street-address"></label>
                                <label class="checkout-field checkout-field--wide">Referencia <small>(opcional)</small><textarea name="referencia" maxlength="500" placeholder="Número, piso, urbanización u otra indicación">{{ old('referencia') }}</textarea></label>
                            </fieldset>

                            <div class="checkout-step-actions" data-checkout-actions="delivery" hidden>
                                <button class="btn btn-ghost" type="button" data-checkout-previous><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</button>
                                <button class="btn btn-primary" type="button" data-checkout-next>Siguiente: Pago <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                            </div>

                            <fieldset class="checkout-step checkout-payment-fields" data-checkout-step="payment" hidden>
                                <legend>3. Pago</legend>
                                <p class="checkout-payment-help">Selecciona el medio con el que deseas pagar este pedido.</p>
                                @if ($paymentMethods->isNotEmpty())
                                    <label class="checkout-payment-label" for="medio_pago_id">Medio de pago</label>
                                    <select id="medio_pago_id" name="medio_pago_id" required data-checkout-payment>
                                        <option value="">Selecciona un medio de pago</option>
                                        @foreach ($paymentMethods as $paymentMethod)
                                            <option value="{{ $paymentMethod->id }}" @selected(old('medio_pago_id') == $paymentMethod->id)>{{ $paymentMethod->nombre }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <p class="checkout-payment-empty">No hay medios de pago activos configurados. El pedido quedará pendiente de coordinación.</p>
                                @endif
                            </fieldset>

                            <div class="checkout-step-actions" data-checkout-actions="payment" hidden>
                                <button class="btn btn-ghost" type="button" data-checkout-previous><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</button>
                                <button class="btn btn-primary" type="button" data-checkout-next>Revisar pedido <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                            </div>

                            <fieldset class="checkout-step checkout-confirmation-fields" data-checkout-step="confirmation" hidden>
                                <legend>4. Confirmación</legend>
                                <div class="checkout-confirmation-summary" data-checkout-summary></div>
                            </fieldset>

                            <div class="checkout-step-actions checkout-final-actions" data-checkout-actions="confirmation" hidden>
                                <button class="btn btn-ghost" type="button" data-checkout-previous><i class="bi bi-arrow-left" aria-hidden="true"></i> Anterior</button>
                                <button class="btn btn-primary checkout-submit" type="submit" data-checkout-confirm @disabled(! $puedeConfirmar)>Confirmar y registrar pedido</button>
                            </div>
                        </form>
                    </div>

                    <aside class="checkout-order-sidebar" aria-labelledby="checkout-summary-title">
                        <h2 id="checkout-summary-title"><i class="bi bi-receipt" aria-hidden="true"></i> Resumen de la compra</h2>
                        <div class="checkout-order-summary">
                            @foreach ($lines as $line)
                                <article class="checkout-order-item">
                                    <div class="checkout-order-item-image">
                                        @if ($line['product']->url_imagen_principal)
                                            <img src="{{ $line['product']->url_imagen_principal }}" alt="{{ $line['product']->marca }} {{ $line['product']->nombre }}" loading="lazy">
                                        @else
                                            <i class="bi bi-box-seam" aria-hidden="true"></i>
                                        @endif
                                    </div>
                                    <div class="checkout-order-item-info">
                                        <strong>{{ $line['product']->nombre }}</strong>
                                        <small>{{ $line['product']->marca }} · {{ $line['product']->category?->nombre ?: $line['product']->categoria }}</small>
                                        <span>Cantidad: {{ $line['quantity'] }}</span>
                                    </div>
                                    <strong class="checkout-order-item-price">S/ {{ number_format($line['subtotal'], 2) }}</strong>
                                </article>
                            @endforeach
                        </div>
                        @php
                            $subtotal = (float) $total;
                            $discount = 0.0;
                            $payable = $subtotal - $discount;
                        @endphp
                        <div class="checkout-totals" aria-label="Totales de la compra">
                            <div class="checkout-total-row">
                                <span>Subtotal</span>
                                <strong>S/ {{ number_format($subtotal, 2) }}</strong>
                            </div>
                            <div class="checkout-total-row checkout-total-row--discount">
                                <span>Descuentos</span>
                                <strong>- S/ {{ number_format($discount, 2) }}</strong>
                            </div>
                            <div class="checkout-total-row checkout-total-row--total">
                                <span>Total</span>
                                <strong>S/ {{ number_format($payable, 2) }}</strong>
                            </div>
                            <div class="checkout-total-row checkout-total-row--final">
                                <span>Total con <b class="checkout-payment-badge">sip</b></span>
                                <strong>S/ {{ number_format($payable, 2) }}</strong>
                            </div>
                        </div>
                    </aside>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
