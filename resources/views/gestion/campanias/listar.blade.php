@extends('layouts.administracion')

@section('title', 'Campañas')
@section('body_class', 'modulo-campanias')

@section('content')
    @php
        $locationLabels = ['emergente' => 'Emergente', 'lateral' => 'Lateral', 'barra_superior' => 'Barra superior'];
        $campaignStatus = static function ($campaign): array {
            if (!$campaign->activo) {
                return ['label' => 'Pausada', 'class' => 'paused'];
            }

            if ($campaign->finaliza_en?->isPast()) {
                return ['label' => 'Finalizada', 'class' => 'finished'];
            }

            if ($campaign->inicia_en?->isFuture()) {
                return ['label' => 'Programada', 'class' => 'scheduled'];
            }

            return ['label' => 'Activa', 'class' => 'active'];
        };
        $imageUrl = static function (?string $path): ?string {
            if (!$path) {
                return null;
            }

            return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
        };
        $formatTrend = static fn (?float $trend): string => $trend === null ? 'Sin periodo anterior' : (($trend >= 0 ? '+' : '').number_format($trend, 1).'% vs. mes anterior');
        $distributionTotal = max(1, (int) $distribution->sum('total'));
    @endphp

    <section class="campanias-page" data-campanias-page>
        <header class="campanias-hero">
            <div>
                <span class="campanias-eyebrow">Marketing Center</span>
                <h1>Campañas <i class="bi bi-megaphone-fill" aria-hidden="true"></i></h1>
                <p>Promociones y banners con registro de vistas y clics.</p>
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
            <button class="campanias-primary-button" type="button" data-open-campaign-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva campaña</button>
            <button class="campanias-toolbar-button" type="button" disabled title="Importación disponible próximamente"><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
            <a class="campanias-toolbar-button" href="{{ route('management.campaigns') }}"><i class="bi bi-download" aria-hidden="true"></i> Exportar</a>
            @if (($filters['buscar'] ?? null) || ($filters['ubicacion'] ?? null) || ($filters['estado'] ?? null))
                <a class="campanias-clear-button" href="{{ route('management.campaigns') }}">Limpiar filtros</a>
            @endif
        </form>

        <section class="campanias-kpi-grid" aria-label="Resumen de campañas">
            <article class="campanias-kpi campanias-kpi--blue"><span class="campanias-kpi-icon"><i class="bi bi-eye-fill" aria-hidden="true"></i></span><div><small>Total de vistas</small><strong>{{ number_format($totalViews) }}</strong><span>{{ $formatTrend($viewsTrend) }}</span></div><i class="campanias-kpi-spark campanias-kpi-spark--blue" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--purple"><span class="campanias-kpi-icon"><i class="bi bi-cursor-fill" aria-hidden="true"></i></span><div><small>Total de clics</small><strong>{{ number_format($totalClicks) }}</strong><span>{{ $formatTrend($clicksTrend) }}</span></div><i class="campanias-kpi-spark campanias-kpi-spark--purple" aria-hidden="true"></i></article>
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
                                @php($status = $campaignStatus($campaign))
                                <tr>
                                    <td><span class="campanias-checkbox" aria-hidden="true"></span></td>
                                    <td>@if ($image = $imageUrl($campaign->url_imagen))<img class="campanias-thumbnail" src="{{ $image }}" alt="{{ $campaign->titulo }}">@else<span class="campanias-thumbnail campanias-thumbnail--empty"><i class="bi bi-image" aria-hidden="true"></i></span>@endif</td>
                                    <td><div class="campanias-name"><strong>{{ $campaign->nombre }}</strong><small>{{ $campaign->titulo }}</small></div></td>
                                    <td><span class="campanias-status campanias-status--{{ $status['class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status['label'] }}</span></td>
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
                <footer class="campanias-pagination"><span>Mostrando {{ $campaigns->firstItem() ?? 0 }} a {{ $campaigns->lastItem() ?? 0 }} de {{ number_format($campaigns->total()) }} campañas</span><div class="campanias-pagination-controls">{{ $campaigns->onEachSide(1)->links() }}<form method="get" action="{{ route('management.campaigns') }}" class="campanias-per-page">@foreach (request()->except(['page', 'por_pagina']) as $key => $value) @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="por_pagina" aria-label="Campañas por página" onchange="this.form.submit()">@foreach ([10, 20, 50] as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>@endforeach</select><span>por página</span></form></div></footer>
            </section>

            <aside class="campanias-side-column">
                <section class="campanias-side-card"><header class="campanias-card-header"><div><h2>Distribución de campañas</h2><p>Campañas por ubicación.</p></div></header><div class="campanias-distribution"><div class="campanias-donut" style="--campanias-angle: {{ min(360, ($distribution->first()?->total ?? 0) / $distributionTotal * 360) }}deg"><strong>{{ number_format($totalCampaigns) }}</strong><span>Campañas</span></div><div class="campanias-distribution-list">@forelse ($distribution as $item)<div><span><i class="campanias-dot campanias-dot--{{ $loop->index % 4 }}" aria-hidden="true"></i>{{ $locationLabels[$item->ubicacion] ?? $item->ubicacion }}</span><b>{{ $item->total }}</b><small>{{ number_format(($item->total / $distributionTotal) * 100, 1) }}%</small></div>@empty<span class="campanias-muted">Sin datos de distribución.</span>@endforelse</div></div></section>
                <section class="campanias-side-card"><header class="campanias-card-header"><div><h2>Resumen por tipo de campaña</h2><p>Participación actual.</p></div></header><div class="campanias-bars">@forelse ($distribution as $item)<div><div><span>{{ $locationLabels[$item->ubicacion] ?? $item->ubicacion }}</span><b>{{ $item->total }}</b></div><div class="campanias-bar"><i class="campanias-bar--{{ $loop->index % 4 }}" style="width: {{ ($item->total / $distributionTotal) * 100 }}%"></i></div></div>@empty<span class="campanias-muted">Aún no hay campañas.</span>@endforelse</div></section>
            </aside>
        </div>

        <div class="campanias-bottom-grid">
            <section class="campanias-bottom-card"><header class="campanias-card-header"><div><h2>Top campañas por rendimiento</h2><p>Ordenadas por cantidad de vistas.</p></div><span class="campanias-period-label">Datos acumulados</span></header><div class="campanias-top-list">@forelse ($topCampaigns as $campaign)<div><span class="campanias-rank">{{ $loop->iteration }}</span><span class="campanias-top-content">@if ($image = $imageUrl($campaign->url_imagen))<img src="{{ $image }}" alt="{{ $campaign->titulo }}">@else<span class="campanias-thumbnail campanias-thumbnail--empty"><i class="bi bi-image" aria-hidden="true"></i></span>@endif<strong>{{ $campaign->nombre }}</strong></span><span>{{ number_format($campaign->vistas) }} vistas</span><b>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}% CTR</b></div>@empty<div class="campanias-secondary-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>Sin rendimiento todavía</strong><span>Las métricas aparecerán al registrar actividad.</span></div>@endforelse</div></section>
            <section class="campanias-bottom-card"><header class="campanias-card-header"><div><h2>Actividad reciente</h2><p>Últimos eventos registrados.</p></div><a href="{{ route('management.audit') }}">Ver todas</a></header><div class="campanias-activity-list">@forelse ($recentEvents as $event)<div class="campanias-activity-row"><span class="campanias-activity-icon campanias-activity-icon--{{ $event->tipo === 'clic' ? 'purple' : 'blue' }}"><i class="bi bi-{{ $event->tipo === 'clic' ? 'cursor-fill' : 'eye-fill' }}" aria-hidden="true"></i></span><span><strong>{{ $event->tipo === 'clic' ? 'Clic registrado' : 'Vista registrada' }}</strong><small>{{ $event->campaign?->nombre ?: 'Campaña' }} · {{ $event->campaign?->titulo ?: 'Sin título' }}</small></span><time>{{ $event->creado_en?->diffForHumans() }}</time></div>@empty<div class="campanias-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los eventos aparecerán aquí.</span></div>@endforelse</div></section>
        </div>
    </section>

    <dialog class="campanias-dialog" id="nueva-campania">
        <div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-send" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2>Nueva campaña</h2><p>Completa la información para crear una nueva promoción.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body">@include('gestion.campanias.crear', ['segments' => $segments])</div></div>
    </dialog>

    @foreach ($campaigns as $campaign)
        <dialog class="campanias-dialog" id="editar-campania-{{ $campaign->id }}"><div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2>Editar campaña</h2><p>Actualiza la información de {{ $campaign->nombre }}.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body">@include('gestion.campanias.editar', ['campaign' => $campaign, 'segments' => $segments])</div></div></dialog>
        <dialog class="campanias-dialog campanias-view-dialog" id="campania-detalle-{{ $campaign->id }}"><div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2>{{ $campaign->nombre }}</h2><p>Detalle y rendimiento de la campaña.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-view-body">@php($viewStatus = $campaignStatus($campaign))<div class="campanias-view-banner">@if ($image = $imageUrl($campaign->url_imagen))<img src="{{ $image }}" alt="{{ $campaign->titulo }}">@else<span><i class="bi bi-megaphone" aria-hidden="true"></i></span>@endif<div><span class="campanias-status campanias-status--{{ $viewStatus['class'] }}">{{ $viewStatus['label'] }}</span><h3>{{ $campaign->titulo }}</h3><p>{{ $campaign->descripcion ?: 'Sin descripción registrada.' }}</p></div></div><div class="campanias-view-stats"><div><small>Vistas</small><strong>{{ number_format($campaign->vistas) }}</strong></div><div><small>Clics</small><strong>{{ number_format($campaign->clics) }}</strong></div><div><small>CTR</small><strong>{{ $campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0' }}%</strong></div><div><small>Ubicación</small><strong>{{ $locationLabels[$campaign->ubicacion] ?? $campaign->ubicacion }}</strong></div></div></div></div></dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (() => {
            const page = document.querySelector('[data-campanias-page]');
            if (!page) {
                return;
            }

            const openDialog = (dialog) => {
                if (dialog && !dialog.open) {
                    dialog.showModal();
                }
            };

            page.querySelectorAll('[data-open-campaign-create]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector('#nueva-campania'))));
            page.querySelectorAll('[data-open-campaign-edit]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector(`#editar-campania-${button.dataset.openCampaignEdit}`))));
            page.querySelectorAll('[data-open-campaign-view]').forEach((button) => button.addEventListener('click', (event) => { event.preventDefault(); openDialog(document.querySelector(`#campania-detalle-${button.dataset.openCampaignView}`)); }));
            document.querySelectorAll('[data-close-campaign-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            document.querySelectorAll('.campanias-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => { if (event.target === dialog) { dialog.close(); } }));

            document.querySelectorAll('[data-campaign-form]').forEach((form) => {
                const fileInput = form.querySelector('[data-campaign-image-input]');
                const urlInput = form.querySelector('[data-campaign-image-url]');
                const preview = form.querySelector('[data-campaign-preview]');
                const previewImage = form.querySelector('[data-campaign-preview-image]');
                const destination = form.querySelector('[data-campaign-destination]');
                const allSegments = form.querySelector('[data-campaign-all]');
                const segments = [...form.querySelectorAll('input[name="segmentos[]"]')];

                const showPreview = (source) => {
                    if (!source || !previewImage || !preview) { return; }
                    previewImage.src = source;
                    preview.hidden = false;
                };

                fileInput?.addEventListener('change', () => {
                    const file = fileInput.files?.[0];
                    if (file) { showPreview(URL.createObjectURL(file)); }
                });
                urlInput?.addEventListener('input', () => showPreview(urlInput.value.trim()));
                form.querySelector('[data-clear-campaign-image]')?.addEventListener('click', () => { if (fileInput) { fileInput.value = ''; } if (urlInput) { urlInput.value = ''; } if (preview) { preview.hidden = true; } });
                destination?.addEventListener('change', () => { if (urlInput && destination.value) { urlInput.value = destination.value; } });
                allSegments?.addEventListener('change', () => { if (allSegments.checked) { segments.forEach((checkbox) => { checkbox.checked = false; }); } });
                segments.forEach((checkbox) => checkbox.addEventListener('change', () => { if (checkbox.checked && allSegments) { allSegments.checked = false; } if (allSegments && !segments.some((item) => item.checked)) { allSegments.checked = true; } }));
            });

            @if ($errors->any())
                openDialog(document.querySelector('#nueva-campania'));
            @endif
        })();
    </script>
@endpush
