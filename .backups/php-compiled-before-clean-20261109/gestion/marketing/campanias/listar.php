<?php $__env->startSection('title', 'Campañas'); ?>
<?php $__env->startSection('body_class', 'modulo-campanias'); ?>

<?php $__env->startSection('content'); ?>
    <?php
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
    ?>

    <section class="campanias-page" data-campanias-page>
        <header class="campanias-hero">
            <div>
                <span class="campanias-eyebrow">Marketing Center</span>
                <h1>Campañas <i class="bi bi-megaphone-fill" aria-hidden="true"></i></h1>
                <p>Promociones y banners con registro de vistas y clics.</p>
            </div>
            <div class="campanias-hero-actions" aria-label="Acciones de campañas">
                <button class="campanias-primary-button" type="button" data-open-campaign-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva campaña</button>
                <button class="campanias-toolbar-button" type="button" data-open-campaign-import><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
                <details class="campanias-export-menu"><summary class="campanias-toolbar-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="campanias-export-options"><a href="<?php echo e(route('management.campaigns.export', ['format' => 'pdf', 'buscar' => $filters['buscar'], 'ubicacion' => $filters['ubicacion'], 'estado' => $filters['estado']])); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="<?php echo e(route('management.campaigns.export', ['format' => 'excel', 'buscar' => $filters['buscar'], 'ubicacion' => $filters['ubicacion'], 'estado' => $filters['estado']])); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="<?php echo e(route('management.campaigns.export', ['format' => 'csv', 'buscar' => $filters['buscar'], 'ubicacion' => $filters['ubicacion'], 'estado' => $filters['estado']])); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
            </div>
        </header>

        <nav class="campanias-tabs" aria-label="Secciones de campañas">
            <span class="campanias-tab campanias-tab--active"><i class="bi bi-card-list" aria-hidden="true"></i> Lista de campañas</span>
            <span class="campanias-tab"><i class="bi bi-bar-chart-fill" aria-hidden="true"></i> Análisis y métricas</span>
        </nav>

        <form class="campanias-toolbar" method="get" action="<?php echo e(route('management.campaigns')); ?>">
            <label class="campanias-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar campañas</span><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar campañas..."></label>
            <label><span class="visually-hidden">Filtrar por ubicación</span><select name="ubicacion"><option value="">Todas las ubicaciones</option><?php $__currentLoopData = $locationLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(($filters['ubicacion'] ?? null) === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Todos los estados</option><option value="activa" <?php if(($filters['estado'] ?? null) === 'activa'): echo 'selected'; endif; ?>>Activas</option><option value="inactiva" <?php if(($filters['estado'] ?? null) === 'inactiva'): echo 'selected'; endif; ?>>Inactivas</option></select></label>
            <button class="campanias-toolbar-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <?php if(($filters['buscar'] ?? null) || ($filters['ubicacion'] ?? null) || ($filters['estado'] ?? null)): ?>
                <a class="campanias-clear-button" href="<?php echo e(route('management.campaigns')); ?>">Limpiar filtros</a>
            <?php endif; ?>
        </form>

        <section class="campanias-kpi-grid" aria-label="Resumen de campañas">
            <article class="campanias-kpi campanias-kpi--blue"><span class="campanias-kpi-icon"><i class="bi bi-eye-fill" aria-hidden="true"></i></span><div><small>Total de vistas</small><strong><?php echo e(number_format($totalViews)); ?></strong><span><?php echo e($formatTrend($viewsTrend)); ?></span></div><i class="campanias-kpi-spark campanias-kpi-spark--blue" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--purple"><span class="campanias-kpi-icon"><i class="bi bi-cursor-fill" aria-hidden="true"></i></span><div><small>Total de clics</small><strong><?php echo e(number_format($totalClicks)); ?></strong><span><?php echo e($formatTrend($clicksTrend)); ?></span></div><i class="campanias-kpi-spark campanias-kpi-spark--purple" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--green"><span class="campanias-kpi-icon"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><div><small>Tasa de conversión</small><strong><?php echo e(number_format($conversionRate, 1)); ?>%</strong><span>De vistas a clics</span></div><i class="campanias-kpi-spark campanias-kpi-spark--green" aria-hidden="true"></i></article>
            <article class="campanias-kpi campanias-kpi--orange"><span class="campanias-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span><div><small>Campañas activas</small><strong><?php echo e(number_format($activeCampaigns)); ?></strong><span><?php echo e(number_format($totalCampaigns)); ?> registradas</span></div><i class="campanias-kpi-spark campanias-kpi-spark--orange" aria-hidden="true"></i></article>
        </section>

        <div class="campanias-dashboard-grid">
            <section class="campanias-list-card">
                <header class="campanias-card-header"><div><h2>Campañas registradas (<?php echo e(number_format($campaigns->total())); ?>)</h2><p>Mostrando <?php echo e($campaigns->firstItem() ?? 0); ?> a <?php echo e($campaigns->lastItem() ?? 0); ?> campañas</p></div><button class="campanias-view-all" type="button" data-open-campaign-create>+ Nueva campaña</button></header>
                <div class="campanias-table-wrap">
                    <table class="campanias-table">
                        <thead><tr><th><span class="campanias-checkbox" aria-hidden="true"></span></th><th>Imagen</th><th>Campaña</th><th>Estado</th><th>Vistas</th><th>Clics</th><th>CTR</th><th>Ubicación</th><th>Acciones</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php ($status = $campaignStatus($campaign)); ?>
                                <tr>
                                    <td><span class="campanias-checkbox" aria-hidden="true"></span></td>
                                    <td><?php if($image = $imageUrl($campaign->url_imagen)): ?><img class="campanias-thumbnail" src="<?php echo e($image); ?>" alt="<?php echo e($campaign->titulo); ?>"><?php else: ?><span class="campanias-thumbnail campanias-thumbnail--empty"><i class="bi bi-image" aria-hidden="true"></i></span><?php endif; ?></td>
                                    <td><div class="campanias-name"><strong><?php echo e($campaign->nombre); ?></strong><small><?php echo e($campaign->titulo); ?></small></div></td>
                                    <td><span class="campanias-status campanias-status--<?php echo e($status['class']); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($status['label']); ?></span></td>
                                    <td><?php echo e(number_format($campaign->vistas)); ?></td>
                                    <td><?php echo e(number_format($campaign->clics)); ?></td>
                                    <td><?php echo e($campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0'); ?>%</td>
                                    <td><span class="campanias-location"><?php echo e($locationLabels[$campaign->ubicacion] ?? $campaign->ubicacion); ?></span></td>
                                    <td class="campanias-actions-cell"><a class="campanias-action" href="#campania-detalle-<?php echo e($campaign->id); ?>" data-open-campaign-view="<?php echo e($campaign->id); ?>" title="Ver campaña"><i class="bi bi-eye" aria-hidden="true"></i></a><button class="campanias-action" type="button" data-open-campaign-edit="<?php echo e($campaign->id); ?>" title="Editar campaña"><i class="bi bi-pencil" aria-hidden="true"></i></button><?php if($campaign->activo): ?><form method="post" action="<?php echo e(route('management.campaigns.deactivate', $campaign)); ?>"><?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?><button class="campanias-action campanias-action--danger" type="submit" data-confirm="¿Desactivar esta campaña?" title="Desactivar campaña"><i class="bi bi-trash3" aria-hidden="true"></i></button></form><?php endif; ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="9" class="campanias-empty"><i class="bi bi-megaphone" aria-hidden="true"></i><strong>No hay campañas registradas.</strong><span>Registra una campaña para comenzar a medir sus resultados.</span></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <footer class="campanias-pagination"><span>Mostrando <?php echo e($campaigns->firstItem() ?? 0); ?> a <?php echo e($campaigns->lastItem() ?? 0); ?> de <?php echo e(number_format($campaigns->total())); ?> campañas</span><div class="campanias-pagination-controls"><?php echo e($campaigns->onEachSide(1)->links()); ?><form method="get" action="<?php echo e(route('management.campaigns')); ?>" class="campanias-per-page"><?php $__currentLoopData = request()->except(['page', 'por_pagina']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><select name="por_pagina" aria-label="Campañas por página" onchange="this.form.submit()"><?php $__currentLoopData = [10, 20, 50]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if(($filters['por_pagina'] ?? 10) === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select><span>por página</span></form></div></footer>
            </section>

            <aside class="campanias-side-column">
                <section class="campanias-side-card"><header class="campanias-card-header"><div><h2>Distribución de campañas</h2><p>Campañas por ubicación.</p></div></header><div class="campanias-distribution"><div class="campanias-donut" style="--campanias-angle: <?php echo e(min(360, ($distribution->first()?->total ?? 0) / $distributionTotal * 360)); ?>deg"><strong><?php echo e(number_format($totalCampaigns)); ?></strong><span>Campañas</span></div><div class="campanias-distribution-list"><?php $__empty_1 = true; $__currentLoopData = $distribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><span><i class="campanias-dot campanias-dot--<?php echo e($loop->index % 4); ?>" aria-hidden="true"></i><?php echo e($locationLabels[$item->ubicacion] ?? $item->ubicacion); ?></span><b><?php echo e($item->total); ?></b><small><?php echo e(number_format(($item->total / $distributionTotal) * 100, 1)); ?>%</small></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><span class="campanias-muted">Sin datos de distribución.</span><?php endif; ?></div></div></section>
                <section class="campanias-side-card"><header class="campanias-card-header"><div><h2>Resumen por tipo de campaña</h2><p>Participación actual.</p></div></header><div class="campanias-bars"><?php $__empty_1 = true; $__currentLoopData = $distribution; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><div><span><?php echo e($locationLabels[$item->ubicacion] ?? $item->ubicacion); ?></span><b><?php echo e($item->total); ?></b></div><div class="campanias-bar"><i class="campanias-bar--<?php echo e($loop->index % 4); ?>" style="width: <?php echo e(($item->total / $distributionTotal) * 100); ?>%"></i></div></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><span class="campanias-muted">Aún no hay campañas.</span><?php endif; ?></div></section>
            </aside>
        </div>

        <div class="campanias-bottom-grid">
            <section class="campanias-bottom-card"><header class="campanias-card-header"><div><h2>Top campañas por rendimiento</h2><p>Ordenadas por cantidad de vistas.</p></div><span class="campanias-period-label">Datos acumulados</span></header><div class="campanias-top-list"><?php $__empty_1 = true; $__currentLoopData = $topCampaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><span class="campanias-rank"><?php echo e($loop->iteration); ?></span><span class="campanias-top-content"><?php if($image = $imageUrl($campaign->url_imagen)): ?><img src="<?php echo e($image); ?>" alt="<?php echo e($campaign->titulo); ?>"><?php else: ?><span class="campanias-thumbnail campanias-thumbnail--empty"><i class="bi bi-image" aria-hidden="true"></i></span><?php endif; ?><strong><?php echo e($campaign->nombre); ?></strong></span><span><?php echo e(number_format($campaign->vistas)); ?> vistas</span><b><?php echo e($campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0'); ?>% CTR</b></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="campanias-secondary-empty"><i class="bi bi-bar-chart" aria-hidden="true"></i><strong>Sin rendimiento todavía</strong><span>Las métricas aparecerán al registrar actividad.</span></div><?php endif; ?></div></section>
            <section class="campanias-bottom-card"><header class="campanias-card-header"><div><h2>Actividad reciente</h2><p>Últimos eventos registrados.</p></div><a href="<?php echo e(route('management.audit')); ?>">Ver todas</a></header><div class="campanias-activity-list"><?php $__empty_1 = true; $__currentLoopData = $recentEvents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="campanias-activity-row"><span class="campanias-activity-icon campanias-activity-icon--<?php echo e($event->tipo === 'clic' ? 'purple' : 'blue'); ?>"><i class="bi bi-<?php echo e($event->tipo === 'clic' ? 'cursor-fill' : 'eye-fill'); ?>" aria-hidden="true"></i></span><span><strong><?php echo e($event->tipo === 'clic' ? 'Clic registrado' : 'Vista registrada'); ?></strong><small><?php echo e($event->campaign?->nombre ?: 'Campaña'); ?> · <?php echo e($event->campaign?->titulo ?: 'Sin título'); ?></small></span><time><?php echo e($event->creado_en?->diffForHumans()); ?></time></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="campanias-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los eventos aparecerán aquí.</span></div><?php endif; ?></div></section>
        </div>
    </section>

    <dialog class="campanias-dialog" id="nueva-campania" aria-labelledby="campania-crear-titulo">
        <div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-send" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-crear-titulo">Nueva campaña</h2><p>Completa la información para crear una nueva promoción.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body"><?php echo $__env->make('gestion.marketing.campanias.crear', ['segments' => $segments], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div></div>
    </dialog>

    <?php $__currentLoopData = $campaigns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $campaign): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <dialog class="campanias-dialog" id="editar-campania-<?php echo e($campaign->id); ?>" aria-labelledby="campania-editar-titulo-<?php echo e($campaign->id); ?>"><div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-editar-titulo-<?php echo e($campaign->id); ?>">Editar campaña</h2><p>Actualiza la información de <?php echo e($campaign->nombre); ?>.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body"><?php echo $__env->make('gestion.marketing.campanias.editar', ['campaign' => $campaign, 'segments' => $segments], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div></div></dialog>
        <dialog class="campanias-dialog campanias-view-dialog" id="campania-detalle-<?php echo e($campaign->id); ?>" aria-labelledby="campania-detalle-titulo-<?php echo e($campaign->id); ?>"><div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-eye" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-detalle-titulo-<?php echo e($campaign->id); ?>"><?php echo e($campaign->nombre); ?></h2><p>Detalle y rendimiento de la campaña.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-view-body"><?php ($viewStatus = $campaignStatus($campaign)); ?><div class="campanias-view-banner"><?php if($image = $imageUrl($campaign->url_imagen)): ?><img src="<?php echo e($image); ?>" alt="<?php echo e($campaign->titulo); ?>"><?php else: ?><span><i class="bi bi-megaphone" aria-hidden="true"></i></span><?php endif; ?><div><span class="campanias-status campanias-status--<?php echo e($viewStatus['class']); ?>"><?php echo e($viewStatus['label']); ?></span><h3><?php echo e($campaign->titulo); ?></h3><p><?php echo e($campaign->descripcion ?: 'Sin descripción registrada.'); ?></p></div></div><div class="campanias-view-stats"><div><small>Vistas</small><strong><?php echo e(number_format($campaign->vistas)); ?></strong></div><div><small>Clics</small><strong><?php echo e(number_format($campaign->clics)); ?></strong></div><div><small>CTR</small><strong><?php echo e($campaign->vistas > 0 ? number_format(($campaign->clics / $campaign->vistas) * 100, 1) : '0.0'); ?>%</strong></div><div><small>Ubicación</small><strong><?php echo e($locationLabels[$campaign->ubicacion] ?? $campaign->ubicacion); ?></strong></div></div></div></div></dialog>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <dialog class="campanias-dialog" id="importar-campanias" aria-labelledby="campania-importar-titulo">
        <div class="campanias-dialog-card"><header class="campanias-dialog-header"><div><span class="campanias-dialog-icon"><i class="bi bi-upload" aria-hidden="true"></i></span><span><small>Marketing Center</small><h2 id="campania-importar-titulo">Importar campañas</h2><p>Carga campañas reales mediante un CSV validado.</p></span></div><button type="button" class="campanias-dialog-close" data-close-campaign-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="campanias-dialog-body">
            <form method="POST" action="<?php echo e(route('management.campaigns.import')); ?>" class="form-card" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <p>Columnas exactas: <code>nombre,ubicacion,titulo,descripcion,texto_boton,url_boton,inicia_en,finaliza_en,activo,segmentos</code>. En segmentos usa IDs separados por <code>|</code>.</p>
                <label>Archivo CSV<input type="file" name="archivo" accept=".csv,text/csv" required></label>
                <div class="campanias-form-actions"><button type="button" class="campanias-form-cancel" data-close-campaign-dialog>Cancelar</button><button type="submit" class="campanias-form-submit"><i class="bi bi-upload" aria-hidden="true"></i> Importar campañas</button></div>
            </form>
        </div></div>
    </dialog>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
            page.querySelector('[data-open-campaign-import]')?.addEventListener('click', () => openDialog(document.querySelector('#importar-campanias')));
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

            <?php if($errors->any()): ?>
                openDialog(document.querySelector('#nueva-campania'));
            <?php endif; ?>
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>