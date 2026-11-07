@extends('layouts.panel.principal')

@section('title', 'Indicadores por sede')
@section('body_class', 'modulo-dashboard-global')

@section('content')
@php
    $totalOrders = $results->sum('pedidos');
    $totalIncome = $results->sum('ingresos');
    $maxIncome = max(1, (float) $results->max('ingresos'));
    $exportParameters = array_filter(['periodo' => $filters['periodo'], 'sede' => $filters['sede'], 'canal' => $filters['canal']]);
@endphp
<section class="tc-dg" data-dashboard-global="sedes">
    @include('gestion.analitica.dashboard-global.componentes.cabecera', [
        'titulo' => 'Indicadores por sede',
        'icono' => 'bi-buildings',
        'descripcion' => 'Analiza los pedidos y sus importes registrados por sede.',
        'exportRoute' => $canExport ? 'management.reports.dashboard_sites.export' : null,
        'exportParameters' => $exportParameters,
    ])

    <form class="tc-dg__filters tc-dg__filters--four" method="GET" action="{{ route('management.dashboard.sites') }}">
        <label><span><i class="bi bi-calendar3"></i> Rango de fechas</span><select name="periodo">
            @foreach ([7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días'] as $days => $label)
                <option value="{{ $days }}" @selected($filters['periodo'] === $days)>{{ $label }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-shop"></i> Sede</span><select name="sede" @disabled(! $hasSite)>
            <option value="">Todas las sedes</option>
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" @selected($filters['sede'] == $site->id)>{{ $site->nombre }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-geo-alt"></i> Región</span><select disabled title="Las sedes aún no registran región"><option>Región no registrada</option></select></label>
        <label><span><i class="bi bi-share"></i> Canal de venta</span><select name="canal" @disabled(! $hasChannel)>
            <option value="">Todos los canales</option>
            @foreach ($channels as $channel)
                <option value="{{ $channel }}" @selected($filters['canal'] === $channel)>{{ $channel }}</option>
            @endforeach
        </select></label>
        <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
    </form>

    <div class="tc-dg__metrics tc-dg__metrics--six">
        @include('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Sedes registradas', 'icono' => 'bi-shop', 'valor' => number_format($results->count()), 'detalle' => 'Sedes incluidas en el filtro'])
        @include('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Importe de pedidos', 'icono' => 'bi-coin', 'tono' => 'verde', 'valor' => 'S/ '.number_format($totalIncome, 2), 'detalle' => 'Pedidos no cancelados del período'])
        @include('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Ticket promedio', 'icono' => 'bi-tag', 'tono' => 'violeta', 'valor' => 'S/ '.number_format($totalOrders ? $totalIncome / $totalOrders : 0, 2), 'detalle' => 'Por pedido no cancelado'])
        @include('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Crecimiento', 'icono' => 'bi-graph-up', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Pendiente de comparación entre períodos'])
        @include('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Pedidos', 'icono' => 'bi-calendar-check', 'valor' => number_format($totalOrders), 'detalle' => 'Pedidos no cancelados por sede'])
        @include('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Cumplimiento', 'icono' => 'bi-check-circle', 'tono' => 'verde', 'valor' => '—', 'detalle' => 'Sin metas por sede configuradas'])
    </div>

    <div class="tc-dg__grid tc-dg__grid--halves">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Pedidos por sede (comparativo)</h2><p>Importe de pedidos no cancelados en el período.</p></div></div><div class="tc-dg__chart tc-dg__chart--large">
            @if ($results->isEmpty())
                <p class="tc-dg__empty">No hay sedes con pedidos asociados.</p>
            @else
                @include('gestion.analitica.dashboard-global.componentes.grafico-barras', ['descripcion' => 'Importes reales de pedidos por sede', 'barras' => $results->take(8)->map(fn ($site) => ['etiqueta' => $site->nombre, 'valor' => 'S/ '.number_format($site->ingresos, 2), 'altura' => $site->ingresos > 0 ? max(4, round($site->ingresos / $maxIncome * 100)) : 0])->values()])
            @endif
        </div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-graph-up"></i><div><h2>Evolución de ventas por sede</h2><p>Tendencia diaria de ventas (S/).</p></div></div><p class="tc-dg__empty">Pendiente de integrar ventas y atribución de ingresos por sede.</p></article>
    </div>

    <div class="tc-dg__grid tc-dg__grid--sedes-bottom">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-geo-alt"></i><div><h2>Distribución por ciudad</h2><p>Participación de ingresos por ciudad.</p></div></div><p class="tc-dg__empty">Las sedes aún no tienen ciudad registrada.</p></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-trophy"></i><div><h2>Ranking de sedes</h2><p>Importe de pedidos no cancelados por sede.</p></div></div><div class="tc-dg__table-wrap"><table><thead><tr><th>#</th><th>Sede</th><th>Pedidos</th><th>Importe de pedidos</th></tr></thead><tbody>
            @forelse ($results as $site)
                <tr><td>{{ $loop->iteration }}</td><td>{{ $site->nombre }}</td><td>{{ number_format($site->pedidos) }}</td><td>S/ {{ number_format($site->ingresos, 2) }}</td></tr>
            @empty
                <tr><td colspan="4">No hay sedes con pedidos asociados.</td></tr>
            @endforelse
        </tbody></table></div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bullseye"></i><div><h2>Metas por sede</h2><p>Cumplimiento de metas de ventas por sede.</p></div></div><p class="tc-dg__empty">No hay metas configuradas.</p></article>
    </div>
</section>
@endsection
