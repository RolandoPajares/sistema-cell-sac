@extends('layouts.tienda.principal')

@section('title', 'Seguimiento de pedido')
@section('body_class', 'pagina-seguimiento')

@php
$email = $order?->user?->correo ?: $order?->cliente_invitado_correo;
$status = $order?->estado;
$steps = [
['label' => 'Confirmado', 'date' => $order?->creado_en?->format('d M')],
['label' => 'Pago verificado', 'date' => $order?->creado_en?->format('d M')],
['label' => 'En preparación', 'date' => in_array($status, ['En proceso', 'Enviado', 'Entregado'], true) ? $order?->creado_en?->format('d M') : null],
['label' => 'En camino', 'date' => in_array($status, ['Enviado', 'Entregado'], true) ? $order?->creado_en?->format('d M') : null],
['label' => 'Entregado', 'date' => $status === 'Entregado' ? $order?->creado_en?->format('d M') : null],
];
$activeStep = match ($status) {
'Entregado' => 4,
'Enviado' => 3,
'En proceso' => 2,
default => 1,
};
@endphp

@section('content')
<section class="seguimiento-page container">
    <p class="seguimiento-breadcrumb">Inicio / Seguimiento de pedido</p>
    <header class="seguimiento-heading">
        <h1>¿Dónde está mi pedido?</h1>
        <p>Consulta el estado de tu compra de forma rápida y segura.</p>
    </header>
    <form class="seguimiento-search" method="post" action="{{ route('orders.tracking.search') }}">
        @csrf
        <label>Número de pedido<input name="numero_pedido" value="{{ old('numero_pedido') }}" placeholder="CELL-000125" required></label>
        <label>Correo electrónico<input type="email" name="correo" value="{{ old('correo') }}" placeholder="cliente@ejemplo.com" required></label>
        <button type="submit">Rastrear pedido</button>
        <p>No necesitas iniciar sesión para consultar tu pedido.</p>
    </form>
    @if ($errors->any())<div class="seguimiento-error" role="alert">{{ $errors->first() }}</div>@endif
    @if ($order)
    <section class="seguimiento-result" aria-labelledby="pedido-titulo">
        <header class="seguimiento-result-header">
            <div>
                <h2 id="pedido-titulo">Pedido #CELL-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</h2>
                <p>Fecha estimada de entrega: {{ $status === 'Entregado' ? 'Pedido entregado' : 'En proceso de entrega' }}</p>
            </div><span class="seguimiento-badge">{{ $status }}</span>
        </header>
        <div class="seguimiento-steps" style="--seguimiento-progreso: {{ $activeStep * 20 }}%">@foreach ($steps as $index => $step)<div class="seguimiento-step {{ $index <= $activeStep ? 'is-active' : '' }}"><span class="seguimiento-step-icon">{{ $index < $activeStep || $status === 'Entregado' ? '✓' : ($index === $activeStep ? '→' : '○') }}</span><strong>{{ $step['label'] }}</strong><small>{{ $step['date'] ?: 'Pendiente' }}</small></div>@endforeach</div>
    </section>
    <div class="seguimiento-info-grid">
        <article>
            <h3>Historial de seguimiento</h3>
            <p class="is-current">• {{ $status }}</p>
            <p>✓ Pedido registrado</p>
            <p>✓ Datos verificados</p>
        </article>
        <article>
            <h3>Información de envío</h3>
            <p><strong>Transportista:</strong> Por confirmar</p>
            <p><strong>Correo:</strong> {{ $email }}</p>
            <p><strong>Destino:</strong> Bagua, Amazonas</p>
        </article>
        <article>
            <h3>Resumen de compra</h3>
            <p>{{ $order->details->first()?->product?->nombre ?: 'Productos de tu pedido' }}</p>
            <p>Cantidad: {{ $order->details->sum('cantidad') }} unidad(es)</p>
            <p>Total: S/ {{ number_format((float) $order->total, 2) }}</p>
        </article>
    </div>
    @endif
</section>
@endsection