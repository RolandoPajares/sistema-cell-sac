@extends('layouts.administracion')

@section('title', 'Detalle de envío '.$envio->codigo)
@section('body_class', 'modulo-inventario-logistica')

@section('content')
<section class="tc-il">
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><a href="{{ route('management.inventory.preview.shipments') }}">Envíos y entregas</a> <i class="bi bi-chevron-right" aria-hidden="true"></i> Detalle</span>
            <h1><i class="bi bi-truck" aria-hidden="true"></i> {{ $envio->codigo }}</h1>
            <p>Detalle consultado desde MariaDB dentro de las sedes autorizadas.</p>
        </div>
    </header>

    <div class="tc-il__metrics">
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Pedido</small><strong>#{{ $envio->pedido_id }}</strong><span>{{ $envio->pedido?->numero_referencia ?: 'Sin referencia' }}</span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span><div><small>Estado actual</small><strong>{{ $envio->estado }}</strong><span>{{ $envio->modalidad }}</span></div></article>
        <article class="tc-il__metric tc-il__metric--violeta"><span class="tc-il__metric-icon"><i class="bi bi-upc" aria-hidden="true"></i></span><div><small>Seguimiento</small><strong>{{ $envio->numero_seguimiento ?: '—' }}</strong><span>{{ $envio->transportista ?: 'Transportista no registrado' }}</span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span><div><small>Entrega estimada</small><strong>{{ $envio->estimado_entrega_en?->format('d/m/Y') ?: '—' }}</strong><span>{{ $envio->almacen?->nombre ?: 'Almacén no registrado' }}</span></div></article>
    </div>

    <div class="tc-il__main-grid">
        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-info-circle" aria-hidden="true"></i> Información del envío</h2></div>
            <dl class="tc-il__detail-list">
                <div><dt>Código</dt><dd>{{ $envio->codigo }}</dd></div>
                <div><dt>Sede</dt><dd>{{ $envio->almacen?->sede?->nombre ?: '—' }}</dd></div>
                <div><dt>Almacén</dt><dd>{{ $envio->almacen?->nombre ?: '—' }}</dd></div>
                <div><dt>Servicio</dt><dd>{{ $envio->servicio ?: '—' }}</dd></div>
                <div><dt>Despachado</dt><dd>{{ $envio->despachado_en?->format('d/m/Y H:i') ?: 'No registrado' }}</dd></div>
                <div><dt>Entregado</dt><dd>{{ $envio->entregado_en?->format('d/m/Y H:i') ?: 'No registrado' }}</dd></div>
            </dl>
        </article>
        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-clock-history" aria-hidden="true"></i> Trazabilidad</h2><small>{{ $envio->eventos->count() }} eventos</small></div>
            @forelse ($envio->eventos as $evento)
                <div class="tc-il__card-heading"><span>{{ $evento->estado }}</span><strong>{{ $evento->ocurrido_en?->format('d/m/Y H:i') }}</strong><small>{{ $evento->descripcion ?: 'Sin descripción' }}</small></div>
            @empty
                <p class="tc-il__empty">No hay eventos registrados para este envío.</p>
            @endforelse
        </article>
    </div>
</section>
@endsection
