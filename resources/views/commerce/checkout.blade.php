@extends('layouts.store')

@section('body_class', 'modulo-checkout')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/publico/carrito.css') }}">@endpush
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
                <form method="post" action="{{ route('checkout') }}">@csrf<button class="btn btn-primary" style="width:100%">Confirmar pedido</button></form>
            @endif
        </div>
    </div>
</section>
@endsection
