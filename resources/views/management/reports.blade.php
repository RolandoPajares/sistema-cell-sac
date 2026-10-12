@extends('layouts.admin')

@section('title', 'Reportes')
@section('body_class', 'modulo-reportes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comunes/dashboard.css') }}">
@endpush

@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Analítica</span><h1>Reportes comerciales <i class="bi bi-bar-chart"></i></h1><p>Indicadores de ventas, inventario y campañas registrados en el sistema.</p></div></header>
        <div class="admin-kpi-grid">
            <article class="admin-kpi-card admin-kpi-card--verde"><span class="admin-kpi-icon"><i class="bi bi-cash-stack"></i></span><div><small>Ventas no canceladas</small><strong>S/ {{ number_format((float) $sales, 2) }}</strong><span>Periodo consultado</span></div></article>
            <article class="admin-kpi-card"><span class="admin-kpi-icon"><i class="bi bi-receipt"></i></span><div><small>Pedidos</small><strong>{{ $orders }}</strong><span>Operaciones comerciales</span></div></article>
            <article class="admin-kpi-card admin-kpi-card--violeta"><span class="admin-kpi-icon"><i class="bi bi-phone"></i></span><div><small>Productos</small><strong>{{ $products }}</strong><span>Catálogo registrado</span></div></article>
            <article class="admin-kpi-card admin-kpi-card--rojo"><span class="admin-kpi-icon"><i class="bi bi-box-seam"></i></span><div><small>Stock bajo</small><strong>{{ $lowStock }}</strong><span>Existencias ≤ 5</span></div></article>
        </div>
        <div class="admin-grid-equal">
            <section class="admin-panel"><header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-megaphone"></i><div><h2>Rendimiento de campañas</h2><p>Actividad acumulada de publicidad.</p></div></div></header><div class="admin-empty"><b>{{ number_format((int) $campaigns->views) }}</b><span>vistas · {{ number_format((int) $campaigns->clicks) }} clics</span></div></section>
            <section class="admin-panel"><header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-clock-history"></i><div><h2>Movimientos recientes</h2><p>Últimas operaciones de inventario.</p></div></div></header>@forelse($movements as $movement)<div class="admin-list-item"><span><b>{{ $movement->product?->nombre ?: 'Producto' }}</b><small>{{ $movement->tipo_movimiento }} · {{ $movement->creado_en }}</small></span><strong>{{ $movement->cantidad }}</strong></div>@empty<div class="admin-empty">No hay movimientos registrados.</div>@endforelse</section>
        </div>
    </section>
@endsection
