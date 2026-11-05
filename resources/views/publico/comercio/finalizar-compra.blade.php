@extends('layouts.tienda')

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
                    <button class="btn btn-primary" style="width:100%" @disabled(! $puedeConfirmar)>
                        Confirmar pedido
                    </button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection
