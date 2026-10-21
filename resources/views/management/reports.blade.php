
@extends('layouts.admin')

@section('title', 'Reportes')
@section('body_class', 'modulo-reportes')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comunes/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/marketing/reportes.css') }}">
@endpush

@section('content')

<section class="admin-page">

    {{-- ENCABEZADO --}}
    <header class="admin-page-head">
        <div>
            <span class="eyebrow">Analítica</span>

            <h1>
                Reportes comerciales
                <i class="bi bi-bar-chart"></i>
            </h1>

            <p>
                Indicadores de ventas, inventario y campañas
                registrados en el sistema.
            </p>
        </div>
    </header>

    {{-- INDICADORES PRINCIPALES --}}
    <div class="admin-kpi-grid">

        <article class="admin-kpi-card admin-kpi-card--verde">
            <span class="admin-kpi-icon">
                <i class="bi bi-cash-stack"></i>
            </span>

            <div>
                <small>Ventas no canceladas</small>
                <strong>
                    S/ {{ number_format((float) $sales, 2) }}
                </strong>
                <span>Periodo consultado</span>
            </div>
        </article>

        <article class="admin-kpi-card">
            <span class="admin-kpi-icon">
                <i class="bi bi-receipt"></i>
            </span>

            <div>
                <small>Pedidos</small>
                <strong>{{ $orders }}</strong>
                <span>Operaciones comerciales</span>
            </div>
        </article>

        <article class="admin-kpi-card admin-kpi-card--violeta">
            <span class="admin-kpi-icon">
                <i class="bi bi-phone"></i>
            </span>

            <div>
                <small>Productos</small>
                <strong>{{ $products }}</strong>
                <span>Catálogo registrado</span>
            </div>
        </article>

        <article class="admin-kpi-card admin-kpi-card--rojo">
            <span class="admin-kpi-icon">
                <i class="bi bi-box-seam"></i>
            </span>

            <div>
                <small>Stock bajo</small>
                <strong>{{ $lowStock }}</strong>
                <span>Existencias ≤ 5</span>
            </div>
        </article>

    </div>

    {{-- PANELES DE REPORTES --}}
    <div class="reports-panels">

        {{-- RENDIMIENTO DE CAMPAÑAS --}}
        <section class="reports-panel">

            <header class="reports-panel-header">
                <div class="reports-panel-icon">
                    <i class="bi bi-megaphone"></i>
                </div>

                <div>
                    <h2>Rendimiento de campañas</h2>
                    <p>Actividad acumulada de publicidad.</p>
                </div>
            </header>

            <div class="reports-campaign-stats">

                <div class="reports-campaign-stat">
                    <span class="reports-stat-icon">
                        <i class="bi bi-eye"></i>
                    </span>

                    <span>Vistas registradas</span>

                    <strong>
                        {{ number_format((int) $campaigns->views) }}
                    </strong>
                </div>

                <div class="reports-campaign-stat">
                    <span class="reports-stat-icon">
                        <i class="bi bi-cursor"></i>
                    </span>

                    <span>Clics registrados</span>

                    <strong>
                        {{ number_format((int) $campaigns->clicks) }}
                    </strong>
                </div>

            </div>

        </section>

        {{-- MOVIMIENTOS RECIENTES --}}
        <section class="reports-panel">

            <header class="reports-panel-header">
                <div class="reports-panel-icon">
                    <i class="bi bi-clock-history"></i>
                </div>

                <div>
                    <h2>Movimientos recientes</h2>
                    <p>Últimas operaciones de inventario.</p>
                </div>
            </header>

            <div class="reports-movements">

                @forelse($movements as $movement)

                    <article class="reports-movement">

                        <div class="reports-movement-icon">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <div class="reports-movement-info">
                            <h3>
                                {{ $movement->product?->nombre ?: 'Producto' }}
                            </h3>

                            <p>
                                {{ ucfirst($movement->tipo_movimiento) }}
                                · {{ $movement->creado_en }}
                            </p>
                        </div>

                        <strong class="reports-movement-quantity">
                            {{ $movement->cantidad }}
                        </strong>

                    </article>

                @empty

                    <div class="reports-empty">
                        <i class="bi bi-inbox"></i>
                        <p>No hay movimientos registrados.</p>
                    </div>

                @endforelse

            </div>

        </section>

    </div>

</section>

@endsection
