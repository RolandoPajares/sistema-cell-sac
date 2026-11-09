{{-- Analitica: dashboard-global\ventas\listar.blade.php. Presenta datos preparados por el controlador. --}}
{{-- Bloque principal: muestra indicadores, gráficos y registros de ventas preparados por el controlador. --}}
@extends('layouts.panel.principal')

@section('title', 'Indicadores de ventas e ingresos')
@section('body_class', 'modulo-dashboard-global')

@section('content')
{{-- El controlador entrega las barras, filtros y parámetros de exportación preparados. --}}
<section class="tc-dg" data-dashboard-global="ventas">
    @include('gestion.analitica.dashboard-global.componentes.cabecera')

    <form class="tc-dg__filters" method="GET" action="{{ route('management.dashboard.sales') }}">
        <label><span><i class="bi bi-calendar3"></i> Rango de fechas</span><select name="periodo">
            @foreach ($periodOptions as $days => $label)
                <option value="{{ $days }}" @selected($filters['periodo'] === $days)>{{ $label }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-shop"></i> Sede</span><select name="sede" @disabled(! $hasSite)>
            <option value="">Todas las sedes</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" @selected($filters['sede'] == $site->id)>{{ $site->nombre }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-tag"></i> Canal de venta</span><select name="canal" @disabled(! $hasChannel)>
            <option value="">Todos los canales</option>
            @foreach ($channels as $channel)
                <option value="{{ $channel }}" @selected($filters['canal'] === $channel)>{{ $channel }}</option>
            @endforeach
        </select></label>
        <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
    </form>

    <div class="tc-dg__metrics tc-dg__metrics--six">
        @foreach ($metrics as $metric)
            @include('gestion.analitica.dashboard-global.componentes.metrica', $metric)
        @endforeach
    </div>

    <div class="tc-dg__grid tc-dg__grid--wide">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart-line"></i><div><h2>Ventas e ingresos</h2><p>Pendiente de cobros confirmados por día.</p></div><span>Diario</span></div>
            <div class="tc-dg__chart tc-dg__chart--large">
                @if ($dailyBars->isEmpty())
                    <p class="tc-dg__empty">Pendiente de una fuente de cobros confirmados.</p>
                @else
                    @include('gestion.analitica.dashboard-global.componentes.grafico-barras', $dailyChart)
                @endif
            </div>
        </article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Ventas por canal</h2><p>Pendiente de ventas confirmadas por canal.</p></div></div><div class="tc-dg__chart tc-dg__chart--large">
            @if ($channelBars->isEmpty())
                <p class="tc-dg__empty">Pendiente de una fuente de ventas confirmadas.</p>
            @else
                @include('gestion.analitica.dashboard-global.componentes.grafico-barras', $channelChart)
            @endif
        </div></article>
    </div>

    <div class="tc-dg__grid tc-dg__grid--thirds">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-pie-chart"></i><div><h2>Distribución de ingresos</h2><p>Participación por categoría.</p></div></div><p class="tc-dg__empty">Pendiente de vincular el detalle de venta y las categorías.</p></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-trophy"></i><div><h2>Productos más vendidos</h2><p>Ranking por unidades vendidas.</p></div></div><p class="tc-dg__empty">Pendiente de integrar el ranking con el detalle de venta.</p></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bullseye"></i><div><h2>Metas vs. resultados</h2><p>Cumplimiento de metas del período.</p></div></div><p class="tc-dg__empty">No hay metas configuradas para este período.</p></article>
    </div>
</section>
@endsection
