{{-- Analitica: dashboard-global\ventas\listar.blade.php. Presenta datos preparados por el controlador. --}}
{{-- Bloque principal: muestra indicadores, gráficos y registros de ventas preparados por el controlador. --}}
@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Indicadores de ventas e ingresos')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-dashboard-global')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- El controlador entrega las barras, filtros y parámetros de exportación preparados. --}}
<section class="tc-dg" data-dashboard-global="ventas">
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('gestion.analitica.dashboard-global.componentes.cabecera')

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form class="tc-dg__filters" method="GET" action="{{ route('management.dashboard.sales') }}">
        <label><span><i class="bi bi-calendar3"></i> Rango de fechas</span><select name="periodo">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($periodOptions as $days => $label)
                <option value="{{ $days }}" @selected($filters['periodo'] === $days)>{{ $label }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-shop"></i> Sede</span><select name="sede" @disabled(! $hasSite)>
            <option value="">Todas las sedes</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" @selected($filters['sede'] == $site->id)>{{ $site->nombre }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-tag"></i> Canal de venta</span><select name="canal" @disabled(! $hasChannel)>
            <option value="">Todos los canales</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($channels as $channel)
                <option value="{{ $channel }}" @selected($filters['canal'] === $channel)>{{ $channel }}</option>
            @endforeach
        </select></label>
        <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
    </form>

    <div class="tc-dg__metrics tc-dg__metrics--six">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($metrics as $metric)
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
            @include('gestion.analitica.dashboard-global.componentes.metrica', $metric)
        @endforeach
    </div>

    <div class="tc-dg__grid tc-dg__grid--wide">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart-line"></i><div><h2>Ventas e ingresos</h2><p>Pendiente de cobros confirmados por día.</p></div><span>Diario</span></div>
            <div class="tc-dg__chart tc-dg__chart--large">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($dailyBars->isEmpty())
                    <p class="tc-dg__empty">Pendiente de una fuente de cobros confirmados.</p>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @else
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
                    @include('gestion.analitica.dashboard-global.componentes.grafico-barras', $dailyChart)
                @endif
            </div>
        </article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Ventas por canal</h2><p>Pendiente de ventas confirmadas por canal.</p></div></div><div class="tc-dg__chart tc-dg__chart--large">
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
            @if ($channelBars->isEmpty())
                <p class="tc-dg__empty">Pendiente de una fuente de ventas confirmadas.</p>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
            @else
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
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
