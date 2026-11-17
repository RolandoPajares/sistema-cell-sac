@extends('layouts.tienda.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Seguimiento del pedido #'.$order->id)

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="container content-page">
    <div class="admin-page-head">
        <div>
            <span class="product-category">Pedido #{{ $order->id }}</span>
            <h1>Seguimiento de tu compra</h1>
            <p>Comprador: {{ $order->cliente_invitado_nombre }}</p>
            <p>Estado actual: <strong>{{ $order->estado }}</strong></p>
        </div>
    </div>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
    @foreach($order->details as $line)
        <article class="data-row"><span>{{ $line->product?->nombre ?? 'Producto' }} × {{ $line->cantidad }}</span><strong>S/ {{ number_format($line->cantidad * $line->precio_unitario, 2) }}</strong></article>
    @endforeach
    <div class="cart-total"><span>Total</span><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></div>
    
        <p class="notice">Guarda tu enlace para consultar el avance de tu pedido.</p>
        <button type="button" class="btn btn-primary"
            onclick="navigator.clipboard.writeText(window.location.href).then(() => alert('Enlace copiado correctamente'))">
            Copiar enlace de seguimiento
        </button>

</section>
@endsection
