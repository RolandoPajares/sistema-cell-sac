@extends(($isPanel ?? $isAdmin ?? false) ? 'layouts.panel.principal' : 'layouts.tienda.principal')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Pedido #'.$order->id)
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="{{ ($isPanel ?? $isAdmin ?? false) ? 'admin-page' : 'container content-page' }}">
    <div class="admin-page-head"><div><span class="product-category">Pedido #{{ $order->id }}</span><h1>{{ $order->user->nombre }}</h1><p>Estado actual: <strong>{{ $order->estado }}</strong></p></div></div>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($isPanel ?? $isAdmin ?? false)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form
            method="post"
            action="{{ route('orders.status', $order) }}"
            class="inline-form form-card compact-form"
        >
{{-- proteccion CSRF que valida el origen del formulario. --}}
            @csrf
{{-- metodo HTTP que permite simular PUT, PATCH o DELETE desde el formulario. --}}
            @method('PATCH')
            <label>
                Cambiar estado
                <select name="estado">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
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
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
    @foreach($order->details as $line)
        <article class="data-row"><span>{{ $line->product->nombre }} × {{ $line->cantidad }}</span><strong>S/ {{ number_format($line->cantidad * $line->precio_unitario, 2) }}</strong></article>
    @endforeach
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ((float) $order->descuento > 0)<p class="notice">Cupón {{ $order->cupon_codigo }}: -S/ {{ number_format((float) $order->descuento, 2) }}</p>@endif
    <div class="cart-total"><span>Total</span><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></div>
    <p class="notice">El pedido fue registrado; el pago debe coordinarse por los medios autorizados del negocio.</p>
</section>
@endsection
