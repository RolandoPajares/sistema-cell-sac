@extends(($isPanel ?? false) ? 'layouts.panel.principal' : 'layouts.tienda.principal')
@section('title', 'Pedido #'.$order->id)
@section('content')
<section class="{{ ($isPanel ?? false) ? 'admin-page' : 'container content-page' }}">
    <div class="admin-page-head"><div><span class="product-category">Pedido #{{ $order->id }}</span><h1>{{ $order->user->nombre }}</h1><p>Estado actual: <strong>{{ $order->estado }}</strong></p></div></div>
    @if ($isPanel ?? false)
        <form
            method="post"
            action="{{ route('orders.status', $order) }}"
            class="inline-form form-card compact-form"
        >
            @csrf
            @method('PATCH')
            <label>
                Cambiar estado
                <select name="estado">
                    @foreach(['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado'] as $state)
                        <option value="{{ $state }}" @selected($order->estado === $state)>
                            {{ $state }}
                        </option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary">Actualizar estado</button>
        </form>
    @endif
    @foreach($order->details as $line)
        <article class="data-row"><span>{{ $line->product->nombre }} × {{ $line->cantidad }}</span><strong>S/ {{ number_format($line->cantidad * $line->precio_unitario, 2) }}</strong></article>
    @endforeach
    @if ((float) $order->descuento > 0)<p class="notice">Cupón {{ $order->cupon_codigo }}: -S/ {{ number_format((float) $order->descuento, 2) }}</p>@endif
    <div class="cart-total"><span>Total</span><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></div>
    <p class="notice">El pedido fue registrado; el pago debe coordinarse por los medios autorizados del negocio.</p>
</section>
@endsection
