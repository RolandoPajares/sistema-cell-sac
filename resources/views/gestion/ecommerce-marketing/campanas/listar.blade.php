@extends('layouts.panel.principal')

@section('title', 'Campañas')
@section('body_class', 'modulo-campanias')

@section('content')

    <section class="campanias-page" data-campanias-page data-campanias-reopen="{{ $errors->any() || request('abrir') === 'crear' ? 'crear' : '' }}">
        <header class="campanias-hero">
            <div>
                <span class="campanias-eyebrow">Marketing Center</span>
                <h1>Campañas <i class="bi bi-megaphone-fill" aria-hidden="true"></i></h1>
                <p>Promociones y banners con registro de vistas y clics.</p>
            </div>
            <div class="campanias-hero-actions" aria-label="Acciones de campañas">
                <button class="campanias-primary-button" type="button" data-open-campaign-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva campaña</button>
                <button class="campanias-toolbar-button" type="button" data-open-campaign-import><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
                <details class="campanias-export-menu"><summary class="campanias-toolbar-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="campanias-export-options"><a href="{{ route('management.campaigns.export', ['format' => 'pdf', 'buscar' => $filters['buscar'], 'ubicacion' => $filters['ubicacion'], 'estado' => $filters['estado']]) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="{{ route('management.campaigns.export', ['format' => 'excel', 'buscar' => $filters['buscar'], 'ubicacion' => $filters['ubicacion'], 'estado' => $filters['estado']]) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="{{ route('management.campaigns.export', ['format' => 'csv', 'buscar' => $filters['buscar'], 'ubicacion' => $filters['ubicacion'], 'estado' => $filters['estado']]) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
            </div>
        </header>

        <nav class="campanias-tabs" aria-label="Secciones de campañas">
            <span class="campanias-tab campanias-tab--active"><i class="bi bi-card-list" aria-hidden="true"></i> Lista de campañas</span>
            <span class="campanias-tab"><i class="bi bi-bar-chart-fill" aria-hidden="true"></i> Análisis y métricas</span>
        </nav>

        <form class="campanias-toolbar" method="get" action="{{ route('management.campaigns') }}">
            <label class="campanias-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar campañas</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar campañas..."></label>
            <label><span class="visually-hidden">Filtrar por ubicación</span><select name="ubicacion"><option value="">Todas las ubicaciones</option>@foreach ($locationLabels as $value => $label)<option value="{{ $value }}" @selected(($filters['ubicacion'] ?? null) === $value)>{{ $label }}</option>@endforeach</select></label>
            <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Todos los estados</option><option value="activa" @selected(($filters['estado'] ?? null) === 'activa')>Activas</option><option value="inactiva" @selected(($filters['estado'] ?? null) === 'inactiva')>Inactivas</option></select></label>
            <button class="campanias-toolbar-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            @if (($filters['buscar'] ?? null) || ($filters['ubicacion'] ?? null) || ($filters['estado'] ?? null))
                <a class="campanias-clear-button" href="{{ route('management.campaigns') }}">Limpiar filtros</a>
            @endif
        </form>

        <section class="campanias-kpi-grid" aria-label="Resumen de campañas">
            <article class="campanias-kpi campanias-kpi--blue"><span class="campanias-kpi-icon"><i class="bi bi-eye-fill" aria-hidden="true"></i></span><div><small>Total de vistas</small><strong>{{ number_format($totalViews) }}</strong><span>{{ $viewsTrendLabel }}</span></div><i class="campanias-kpi-spark campanias-kpi-spark--blue" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--purple"><span class="campanias-kpi-icon"><i class="bi bi-cursor-fill" aria-hidden="true"></i></span><div><small>Total de clics</small><strong>{{ number_format($totalClicks) }}</strong><span>{{ $clicksTrendLabel }}</span></div><i class="campanias-kpi-spark campanias-kpi-spark--purple" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--green"><span class="campanias-kpi-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><div><small>Tasa de conversión</small><strong>{{ number_format($conversionRate, 1) }}%</strong><span>De vistas a clics</span></div><i class="campanias-kpi-spark campanias-kpi-spark--green" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--orange"><span class="campanias-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span><div><small>Campañas activas</small><strong>{{ number_format($activeCampaigns) }}</strong><span>{{ number_format($totalCampaigns) }} registradas</span></div><i class="campanias-kpi-spark campanias-kpi-spark--orange" aria-hidden="true"></i></article>
        </section>

        <div class="campanias-dashboard-grid">
            <section class="campanias-list-card">
                <header class="campanias-card-header"><div><h2>Campañas registradas ({{ number_format($campaigns->total()) }})</h2><p>Mostrando {{ $campaigns->firstItem() ?? 0 }} a {{ $campaigns->lastItem() ?? 0 }} campañas</p></div><button class="campanias-view-all" type="button" data-open-campaign-create>+ Nueva campaña</button></header>
                <div class="campanias-table-wrap">
                    <table class="campanias-table">
                        <thead><tr><th><span class="campanias-checkbox" aria-hidden="true"></span></th><th>Imagen</th><th>Campaña</th><th>Estado</th><th>Vistas</th><th>Clics</th><th>CTR</th><th>Ubicación</th><th>Acciones</th></tr></thead>
                        <tbody>
                            @forelse ($campaigns as $campaign)
                                <tr>
                                    <td><span class="campanias-checkbox" aria-hidden="true"></span></td>
                                    <td>@if ($campaignImages[$campaign->id])<img class="campanias-thumbnail" src="{{ $campaignImages[$campaign->id] }}" alt="{{ $campaign->titulo }}">@else<span class="campanias-thumbnail campanias-thumbnail--empty"><i class="bi bi-image" aria-hidden="true"></i></span>@endif</td>
                                    <td><div class="campanias-name"><strong>{{ $campaign->nombre }}</strong><small>{{ $campaign->titulo }}</small></div></td>
                                    <td><span class="campanias-status campanias-status--{{ $campaignStatuses[$campaign->id]['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $campaignStatuses[$campaign->id]['label'] }}</span></td>
                                    <td>{{ number_format($campaign->vistas) }}</td>
                                    <td>{{ number_format($campaign->clics) }}</td>
                                    <td>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}%</td>
                                    <td><span class="campanias-location">{{ $locationLabels[$campaign->ubicacion] ?? $campaign->ubicacion }}</span></td>
                                    <td class="campanias-actions-cell"><a class="campanias-action" href="#campania-detalle-{{ $campaign->id }}" data-open-campaign-view="{{ $campaign->id }}" title="Ver campaña"><i class="bi bi-eye" aria-hidden="true"></i></a><button class="campanias-action" type="button" data-open-campaign-edit="{{ $campaign->id }}" title="Editar campaña"><i class="bi bi-pencil" aria-hidden="true"></i></button>@if ($campaign->activo)<form method="post" action="{{ route('management.campaigns.deactivate', $campaign) }}">@csrf @method('DELETE')<button class="campanias-action campanias-action--danger" type="submit" data-confirm="¿Desactivar esta campaña?" title="Desactivar campaña"><i class="bi bi-trash3" aria-hidden="true"></i></button></form>@endif</td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="campanias-empty"><i class="bi bi-megaphone" aria-hidden="true"></i><strong>No hay campañas registradas.</strong><span>Registra una campaña para comenzar a medir sus resultados.</span></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer class="campanias-pagination"><span>Mostrando {{ $campaigns->firstItem() ?? 0 }} a {{ $campaigns->lastItem() ?? 0 }} de {{ number_format($campaigns->total()) }} campañas</span><div class="campanias-pagination-controls">{{ $campaigns->onEachSide(1)->links() }}<form method="get" action="{{ route('management.campaigns') }}" class="campanias-per-page">@foreach ($paginationQuery as $key => $value) @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="por_pagina" aria-label="Campañas por página" data-campaign-per-page>@foreach ($perPageOptions as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>@endforeach</select><span>por página</span></form></div></footer>
            </section>

            <aside class="campanias-side-column">
                <section class="campanias-side-card"><header class="campanias-card-header"><div><h2>Distribución de campañas</h2><p>Campañas por ubicación.</p></div></header><div class="campanias-distribution"><div class="campanias-donut" data-campanias-angle="{{ $distributionAngle }}"><strong>{{ number_format($totalCampaigns) }}</strong><span>Campañas</span></div><div class="campanias-distribution-list">@forelse ($distribution as $item)<div><span><i class="campanias-dot campanias-dot--{{ $loop->index % 4 }}" aria-hidden="true"></i>{{ $locationLabels[$item->ubicacion] ?? $item->ubicacion }}</span><b>{{ $item->total }}</b><small>{{ number_format(($item->total / $distributionTotal) * 100, 1) }}%</small></div>@empty<span class="campanias-muted">Sin datos de distribución.</span>@endforelse</div></div></section>
                <section class="campanias-side-card"><header class="campanias-card-header"><div><h2>Resumen por tipo de campaña</h2><p>Participación actual.</p></div></header><div class="campanias-bars">@forelse ($distribution as $item)<div><div><span>{{ $locationLabels[$item->ubicacion] ?? $item->ubicacion }}</span><b>{{ $item->total }}</b></div><div class="campanias-bar"><i class="campanias-bar--{{ $loop->index % 4 }}" data-campanias-width="{{ ($item->total / $distributionTotal) * 100 }}"></i></div></div>@empty<span class="campanias-muted">Aún no hay campañas.</span>@endforelse</div></section>
            </aside>
        </div>

        <div class="campanias-bottom-grid">
            <section class="campanias-bottom-card"><header class="campanias-card-header"><div><h2>Top campañas por rendimiento</h2><p>Ordenadas por cantidad de vistas.</p></div><span class="campanias-period-label">Datos acumulados</span></header><div class="campanias-top-list">@forelse ($topCampaigns as $campaign)<div><span class="campanias-rank">{{ $loop->iteration }}</span><span class="campanias-top-content">@if ($campaignImages[$campaign->id])<img src="{{ $campaignImages[$campaign->id] }}" alt="{{ $campaign->titulo }}">@else<span class="campanias-thumbnail campanias-thumbnail--empty"><i class="bi bi-image" aria-hidden="true"></i></span>@endif<strong>{{ $campaign->nombre }}</strong></span><span>{{ number_format($campaign->vistas) }} vistas</span><b>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}% CTR</b></div>@empty<div class="campanias-secondary-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>Sin rendimiento todavía</strong><span>Las métricas aparecerán al registrar actividad.</span></div>@endforelse</div></section>
            <section class="campanias-bottom-card"><header class="campanias-card-header"><div><h2>Actividad reciente</h2><p>Últimos eventos registrados.</p></div><a href="{{ route('management.audit') }}">Ver todas</a></header><div class="campanias-activity-list">@forelse ($recentEvents as $event)<div class="campanias-activity-row"><span class="campanias-activity-icon campanias-activity-icon--{{ $event->tipo === 'clic' ? 'purple' : 'blue' }}"><i class="bi bi-{{ $event->tipo === 'clic' ? 'cursor-fill' : 'eye-fill' }}" aria-hidden="true"></i></span><span><strong>{{ $event->tipo === 'clic' ? 'Clic registrado' : 'Vista registrada' }}</strong><small>{{ $event->campaign?->nombre ?: 'Campaña' }} · {{ $event->campaign?->titulo ?: 'Sin título' }}</small></span><time>{{ $event->creado_en?->diffForHumans() }}</time></div>@empty<div class="campanias-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los eventos aparecerán aquí.</span></div>@endforelse</div></section>
        </div>
    </section>

    <dialog class="campanias-dialog" id="nueva-campania" aria-labelledby="campania-crear-titulo">
        <div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-send" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-crear-titulo">Nueva campaña</h2><p>Completa la información de una nueva campaña.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body">@include('gestion.ecommerce-marketing.campanas.registrar', ['segments' => $segments, 'locationLabels' => $locationLabels, 'campaignDestinations' => $campaignDestinations])</div></div>
    </dialog>

    @foreach ($campaigns as $campaign)
        <dialog class="campanias-dialog" id="editar-campania-{{ $campaign->id }}" aria-labelledby="campania-editar-titulo-{{ $campaign->id }}"><div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-editar-titulo-{{ $campaign->id }}">Editar campaña</h2><p>Actualiza la información de {{ $campaign->nombre }}.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body">@include('gestion.ecommerce-marketing.campanas.editar', ['campaign' => $campaign, 'segments' => $segments, 'campaignImage' => $campaignImages[$campaign->id] ?? null, 'selectedSegments' => $campaignSegmentIds[$campaign->id] ?? []])</div></div></dialog>
        <dialog class="campanias-dialog campanias-view-dialog" id="campania-detalle-{{ $campaign->id }}" aria-labelledby="campania-detalle-titulo-{{ $campaign->id }}"><div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-detalle-titulo-{{ $campaign->id }}">{{ $campaign->nombre }}</h2><p>Detalle y rendimiento de la campaña.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-view-body"><div class="campanias-view-banner">@if ($campaignImages[$campaign->id])<img src="{{ $campaignImages[$campaign->id] }}" alt="{{ $campaign->titulo }}">@else<span><i class="bi bi-megaphone" aria-hidden="true"></i></span>@endif<div><span class="campanias-status campanias-status--{{ $campaignStatuses[$campaign->id]['class'] }}">{{ $campaignStatuses[$campaign->id]['label'] }}</span><h3>{{ $campaign->titulo }}</h3><p>{{ $campaign->descripcion ?: 'Sin descripción registrada.' }}</p></div></div><div class="campanias-view-stats"><div><small>Vistas</small><strong>{{ number_format($campaign->vistas) }}</strong></div><div><small>Clics</small><strong>{{ number_format($campaign->clics) }}</strong></div><div><small>CTR</small><strong>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}%</strong></div><div><small>Ubicación</small><strong>{{ $locationLabels[$campaign->ubicacion] ?? $campaign->ubicacion }}</strong></div></div></div></div></dialog>
    @endforeach

    <dialog class="campanias-dialog" id="importar-campanias" aria-labelledby="campania-importar-titulo">
        <div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-upload" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-importar-titulo">Importar campañas</h2><p>Carga campañas reales mediante un CSV validado.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body">
            <form method="POST" action="{{ route('management.campaigns.import') }}" class="form-card" enctype="multipart/form-data">
                @csrf
                <p>Columnas exactas: <code>nombre,ubicacion,titulo,descripcion,texto_boton,url_boton,inicia_en,finaliza_en,activo,segmentos</code>. En segmentos usa IDs separados por <code>|</code>.</p>
                <label>Archivo CSV<input type="file" name="archivo" accept=".csv,text/csv" required></label>
                <div class="campanias-form-actions"><button type="button" class="campanias-form-cancel" data-close-campaign-dialog>Cancelar</button><button type="submit" class="campanias-form-submit"><i class="bi bi-upload" aria-hidden="true"></i> Importar campañas</button></div>
            </form>
        </div></div>
    </dialog>
@endsection



