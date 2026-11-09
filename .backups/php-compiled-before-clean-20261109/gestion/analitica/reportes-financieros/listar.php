<?php $__env->startSection('title', 'Reportes financieros'); ?>
<?php $__env->startSection('body_class', 'modulo-reportes-financieros'); ?>

<?php $__env->startSection('content'); ?>
<?php
    $module = require config_path('gestion/analitica/reportes-financieros.php');
?>

<?php if(auth()->user()?->can('gestion.seguridad.consultar')): ?>
    <?php
    $moduleKey = $module['key'];
    $kpis = $module['kpis'] ?? [];
    $analytics = $module['analytics'] ?? [];
    $columns = $module['columns'] ?? [];
    $reportCopy = [
        'reportes-financieros' => ['title' => 'Reportes financieros', 'action' => 'Generar informe', 'secondary' => 'Descargar PDF', 'chart' => 'Ingresos y egresos por mes', 'donut' => 'Composición de egresos'],
        'analitica-clientes' => ['title' => 'Analítica de clientes', 'action' => 'Ver segmentos', 'secondary' => 'Comparar períodos', 'chart' => 'Captación y recurrencia', 'donut' => 'Tipos de clientes'],
        'metricas-marketing' => ['title' => 'Métricas de marketing', 'action' => 'Ver campañas', 'secondary' => 'Comparar campañas', 'chart' => 'Rendimiento de campañas', 'donut' => 'Conversión por canal'],
    ][$moduleKey] ?? ['title' => $module['title'], 'action' => 'Generar reporte', 'secondary' => 'Descargar PDF', 'chart' => 'Evolución del periodo', 'donut' => 'Distribución de resultados'];
    $legend = $analytics[1]['legend'] ?? ['Sin datos', 'Pendiente de integración'];
?>

<section class="admin-analysis-module admin-analysis-module--<?php echo e($moduleKey); ?>" data-admin-analysis="<?php echo e($moduleKey); ?>">
    <header class="admin-analysis-header">
        <nav class="admin-analysis-breadcrumb" aria-label="Ruta de navegación"><span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Análisis</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong><?php echo e($reportCopy['title']); ?></strong></nav>
        <div class="admin-analysis-heading-row"><div><span class="admin-analysis-eyebrow"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i> Análisis y reportes</span><h1><?php echo e($reportCopy['title']); ?></h1><p><?php echo e($module['description']); ?></p></div><div class="admin-analysis-actions"><button class="admin-analysis-button admin-analysis-button--secondary" type="button" data-analysis-notice="La exportación estará disponible cuando exista una fuente de datos integrada."><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> <?php echo e($reportCopy['secondary']); ?></button><button class="admin-analysis-button admin-analysis-button--secondary" type="button" data-analysis-notice="La comparación se habilitará con datos reales del periodo."><i class="bi bi-calendar2-range" aria-hidden="true"></i> Comparar períodos</button><button class="admin-analysis-button admin-analysis-button--primary" type="button" data-analysis-open="generar"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?php echo e($reportCopy['action']); ?></button></div></div>
    </header>

    <section class="admin-analysis-kpis" aria-label="Indicadores de <?php echo e($reportCopy['title']); ?>"><?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><article class="admin-analysis-kpi admin-analysis-kpi--<?php echo e($kpi['tone'] ?? 'blue'); ?>"><span class="admin-analysis-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span><div><small><?php echo e($kpi['label']); ?></small><strong><?php echo e($kpi['value'] ?? '—'); ?></strong><span><?php echo e($kpi['helper'] ?? 'Sin datos integrados'); ?></span></div><i class="admin-analysis-kpi-spark" aria-hidden="true"></i></article><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></section>

    <form class="admin-analysis-filters" data-analysis-filter-form><label class="admin-analysis-filter-search"><i class="bi bi-calendar3" aria-hidden="true"></i><span>Periodo:</span><select><option>Últimos 6 meses</option><option>Últimos 30 días</option><option>Este año</option></select></label><label><span>Tipo:</span><select><option>Todos</option><option>Ventas</option><option>Operaciones</option></select></label><label><span>Estado:</span><select><option>Todos</option><option>Actualizado</option><option>Pendiente</option></select></label><button class="admin-analysis-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button><button class="admin-analysis-reset" type="button" data-analysis-notice="Los filtros se restablecerán cuando exista una fuente de datos integrada."><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Restablecer</button></form>
    <span class="admin-analysis-status" data-analysis-status role="status" aria-live="polite"></span>

    <section class="admin-analysis-chart-grid"><article class="admin-analysis-card admin-analysis-chart-card"><header class="admin-analysis-card-header"><div><span class="admin-analysis-card-icon"><i class="bi bi-bar-chart-line" aria-hidden="true"></i></span><div><h2><?php echo e($reportCopy['chart']); ?></h2><p><?php echo e($analytics[0]['description'] ?? 'Evolución del periodo seleccionado.'); ?></p></div></div><span class="admin-analysis-period">Últimos 6 meses <i class="bi bi-chevron-down" aria-hidden="true"></i></span></header><div class="admin-analysis-legend"><span><i class="admin-analysis-legend-dot admin-analysis-legend-dot--blue"></i>Indicador principal</span><span><i class="admin-analysis-legend-dot admin-analysis-legend-dot--cyan"></i>Indicador secundario</span></div><div class="admin-analysis-line-chart" role="img" aria-label="Gráfica pendiente de integración"><div class="admin-analysis-y-axis"><span>—</span><span>—</span><span>—</span><span>—</span><span>—</span></div><div class="admin-analysis-plot"><div class="admin-analysis-grid-lines"></div><svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-hidden="true"><path d="M0 170 C85 150 110 176 190 135 S295 150 365 108 S485 128 555 72 S645 92 720 42"></path><path class="is-secondary" d="M0 194 C90 176 130 190 195 162 S310 178 380 145 S480 158 560 120 S650 138 720 98"></path></svg><div class="admin-analysis-chart-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La evolución aparecerá cuando se conecte el módulo.</span></div></div><div class="admin-analysis-x-axis"><span>May</span><span>Jun</span><span>Jul</span><span>Ago</span><span>Sep</span><span>Oct</span></div></div></article><article class="admin-analysis-card admin-analysis-donut-card"><header class="admin-analysis-card-header"><div><span class="admin-analysis-card-icon admin-analysis-card-icon--purple"><i class="bi bi-pie-chart" aria-hidden="true"></i></span><div><h2><?php echo e($reportCopy['donut']); ?></h2><p><?php echo e($analytics[1]['description'] ?? 'Distribución del periodo seleccionado.'); ?></p></div></div></header><div class="admin-analysis-donut-layout"><div class="admin-analysis-donut"><div><strong>—</strong><span>Sin datos</span></div></div><ul class="admin-analysis-legend-list"><?php $__currentLoopData = $legend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $legendItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><span><i class="admin-analysis-legend-dot admin-analysis-legend-dot--<?php echo e($loop->index % 4); ?>"></i><?php echo e($legendItem); ?></span><b>—</b></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div></article></section>

    <section class="admin-analysis-card admin-analysis-table-card"><header class="admin-analysis-card-header"><div><span class="admin-analysis-card-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><h2><?php echo e($module['title']); ?> disponibles</h2><p>La información aparecerá al conectar el origen de datos autorizado.</p></div></div><span class="admin-analysis-count">Sin registros</span></header><div class="admin-analysis-table-wrap"><table class="admin-analysis-table"><thead><tr><?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><th><?php echo e($column); ?></th><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><th>Acciones</th></tr></thead><tbody><tr><td colspan="<?php echo e(max(1, count($columns) + 1)); ?>"><div class="admin-analysis-empty"><span><i class="bi bi-inbox" aria-hidden="true"></i></span><strong>No hay información para mostrar</strong><p>El diseño está listo para recibir datos reales sin modificar este panel.</p><button type="button" class="admin-analysis-empty-button" data-analysis-open="generar"><i class="bi bi-plus-lg" aria-hidden="true"></i> <?php echo e($reportCopy['action']); ?></button></div></td></tr></tbody></table></div><footer class="admin-analysis-table-footer"><span>Sin resultados para mostrar</span><nav aria-label="Paginación deshabilitada"><button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button type="button" class="is-active" disabled>1</button><button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button></nav></footer></section>

    <section class="admin-analysis-summary-grid"><article class="admin-analysis-card"><header><span class="admin-analysis-card-icon"><i class="bi bi-lightbulb" aria-hidden="true"></i></span><div><h2>Resumen del módulo</h2><p>Indicadores y tendencias de <?php echo e(strtolower($reportCopy['title'])); ?>.</p></div></header><div class="admin-analysis-summary-empty"><i class="bi bi-stars" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>Los hallazgos aparecerán aquí.</span></div></article><article class="admin-analysis-card"><header><span class="admin-analysis-card-icon admin-analysis-card-icon--green"><i class="bi bi-activity" aria-hidden="true"></i></span><div><h2>Actividad reciente</h2><p>Últimas actualizaciones del análisis.</p></div></header><div class="admin-analysis-summary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad registrada</strong><span>La actividad aparecerá al conectar el módulo.</span></div></article></section>

    <dialog class="admin-analysis-dialog" data-analysis-modal="generar" aria-labelledby="<?php echo e($moduleKey); ?>-generar-title"><div class="admin-analysis-dialog-shell"><header><div><span class="admin-analysis-card-icon"><i class="bi bi-file-earmark-bar-graph" aria-hidden="true"></i></span><div><span>Análisis y reportes</span><h2 id="<?php echo e($moduleKey); ?>-generar-title"><?php echo e($reportCopy['action']); ?></h2><p>Prepara los parámetros del informe sin modificar datos.</p></div></div><button type="button" data-analysis-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="admin-analysis-dialog-body"><label><span>Periodo</span><select><option>Últimos 6 meses</option><option>Últimos 30 días</option><option>Este año</option></select></label><label><span>Formato</span><select><option>PDF</option><option>Excel</option><option>CSV</option></select></label><div class="admin-analysis-dialog-note"><i class="bi bi-info-circle" aria-hidden="true"></i> La generación real quedará habilitada cuando exista una fuente de datos integrada.</div></div><footer><button type="button" class="admin-analysis-button admin-analysis-button--secondary" data-analysis-close>Cancelar</button><button type="button" class="admin-analysis-button admin-analysis-button--primary" data-analysis-notice="El informe no se generó: la integración backend está pendiente."><i class="bi bi-file-earmark-arrow-down" aria-hidden="true"></i> Preparar informe</button></footer></div></dialog>
    <div class="admin-analysis-feedback" data-analysis-feedback role="status" aria-live="polite" hidden></div>
</section>

<?php else: ?>
    <?php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $analytics = $module['analytics'] ?? [];
    $fields = $module['fields'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';
?>

<section class="gestion-pendiente-page gestion-pendiente-page--<?php echo e($moduleKey); ?>" data-pending-module="<?php echo e($moduleKey); ?>">
    <header class="gestion-pendiente-hero">
        <div class="gestion-pendiente-heading">
            <span class="gestion-pendiente-eyebrow"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i><?php echo e($module['area']); ?></span>
            <h1><?php echo e($module['title']); ?></h1>
            <p><?php echo e($module['description']); ?></p>
        </div>
        <div class="gestion-pendiente-hero-actions">
            <span class="gestion-pendiente-status"><i class="bi bi-link-45deg" aria-hidden="true"></i> Pendiente de integración</span>
            <?php if($isCrud): ?>
                <button class="gestion-pendiente-primary" type="button" data-pending-open="crear">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Nuevo <?php echo e($recordSingular); ?>

                </button>
            <?php else: ?>
                <button class="gestion-pendiente-secondary" type="button" data-pending-notice="La exportación quedará habilitada cuando este módulo tenga una fuente de datos integrada.">
                    <i class="bi bi-download" aria-hidden="true"></i>
                    Exportar consulta
                </button>
            <?php endif; ?>
        </div>
    </header>

    <div class="gestion-pendiente-kpis">
        <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <article class="gestion-pendiente-kpi gestion-pendiente-kpi--<?php echo e($kpi['tone'] ?? 'blue'); ?>">
                <span class="gestion-pendiente-kpi-icon"><i class="bi <?php echo e($kpi['icon']); ?>" aria-hidden="true"></i></span>
                <div>
                    <small><?php echo e($kpi['label']); ?></small>
                    <strong><?php echo e($kpi['value'] ?? '—'); ?></strong>
                    <span><?php echo e($kpi['helper'] ?? 'Sin datos integrados'); ?></span>
                </div>
            </article>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <?php if($analytics !== []): ?>
        <section class="gestion-pendiente-analytics" aria-label="Análisis visual de <?php echo e($module['title']); ?>">
            <?php $__currentLoopData = $analytics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $analyticsCard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="gestion-pendiente-analytics-card">
                    <header class="gestion-pendiente-analytics-header">
                        <div>
                            <span class="gestion-pendiente-analytics-icon"><i class="bi <?php echo e($analyticsCard['icon'] ?? 'bi-bar-chart-line'); ?>" aria-hidden="true"></i></span>
                            <div>
                                <h2><?php echo e($analyticsCard['title']); ?></h2>
                                <p><?php echo e($analyticsCard['description']); ?></p>
                            </div>
                        </div>
                        <span class="gestion-pendiente-chart-status">Sin datos</span>
                    </header>

                    <?php if(($analyticsCard['type'] ?? 'line') === 'donut'): ?>
                        <div class="gestion-pendiente-donut-layout">
                            <div class="gestion-pendiente-donut" role="img" aria-label="Gráfica pendiente de <?php echo e($analyticsCard['title']); ?>">
                                <div><strong>—</strong><span>Sin datos</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-legend">
                                <?php $__currentLoopData = ($analyticsCard['legend'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $legendItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div><span class="gestion-pendiente-legend-dot gestion-pendiente-legend-dot--<?php echo e($loop->index % 4); ?>"></span><span><?php echo e($legendItem); ?></span><strong>—</strong></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="gestion-pendiente-line-chart" role="img" aria-label="Gráfica pendiente de <?php echo e($analyticsCard['title']); ?>">
                            <div class="gestion-pendiente-chart-y-axis"><span>—</span><span>—</span><span>—</span><span>—</span></div>
                            <div class="gestion-pendiente-chart-plot">
                                <svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M0 168 C75 158 93 174 150 138 S260 162 316 104 S420 132 478 88 S600 126 720 54" class="gestion-pendiente-placeholder-line"></path>
                                </svg>
                                <div class="gestion-pendiente-chart-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La tendencia aparecerá aquí.</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-x-axis">
                                <?php $__currentLoopData = ($analyticsCard['labels'] ?? ['Periodo 1', 'Periodo 2', 'Periodo 3', 'Periodo 4', 'Periodo 5']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <span><?php echo e($label); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </section>
    <?php endif; ?>

    <div class="gestion-pendiente-toolbar">
        <label class="gestion-pendiente-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar <?php echo e($recordPlural); ?></span>
            <input type="search" placeholder="Buscar <?php echo e($recordPlural); ?>..." data-pending-search>
        </label>
        <label>
            <span class="visually-hidden">Filtrar por estado</span>
            <select data-pending-filter>
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="pendiente">Pendientes</option>
                <option value="cerrado">Cerrados</option>
            </select>
        </label>
        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="Los filtros quedarán disponibles cuando exista una fuente de datos para este módulo.">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Aplicar filtros
        </button>
    </div>

    <div class="gestion-pendiente-layout">
        <section class="gestion-pendiente-card gestion-pendiente-list-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span>
                    <div>
                        <h2>Listado de <?php echo e($recordPlural); ?></h2>
                        <p>La información aparecerá cuando se conecte este módulo al backend.</p>
                    </div>
                </div>
                <span class="gestion-pendiente-count">Sin registros</span>
            </header>

            <div class="gestion-pendiente-table-wrap">
                <table class="gestion-pendiente-table">
                    <thead>
                        <tr>
                            <?php $__currentLoopData = $columns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th><?php echo e($column); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($isCrud): ?>
                                <th>Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="<?php echo e(max(1, count($columns) + ($isCrud ? 1 : 0))); ?>">
                                <div class="gestion-pendiente-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay datos disponibles</strong>
                                    <p>Este módulo está preparado visualmente y quedará disponible cuando se integre con datos reales.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="gestion-pendiente-card-footer">
                <span>Sin resultados para mostrar</span>
                <div class="gestion-pendiente-pagination" aria-label="Paginación deshabilitada">
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" class="is-active" disabled>1</button>
                    <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </footer>
        </section>

        <aside class="gestion-pendiente-card gestion-pendiente-side-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon gestion-pendiente-card-icon--purple"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div>
                        <h2>Estado del módulo</h2>
                        <p>Preparación frontend</p>
                    </div>
                </div>
            </header>
            <div class="gestion-pendiente-integration">
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Interfaz administrativa creada</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Diseño responsive preparado</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Conexión de datos pendiente</span></div>
            </div>
            <?php if($isCrud): ?>
                <div class="gestion-pendiente-capabilities">
                    <h3>Interfaces preparadas</h3>
                    <button type="button" data-pending-open="crear"><i class="bi bi-plus-circle" aria-hidden="true"></i> Crear <?php echo e($recordSingular); ?></button>
                    <button type="button" data-pending-open="ver"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalle</button>
                    <button type="button" data-pending-open="editar"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar registro</button>
                    <button type="button" data-pending-open="eliminar"><i class="bi bi-trash3" aria-hidden="true"></i> Confirmar eliminación</button>
                </div>
            <?php else: ?>
                <button class="gestion-pendiente-secondary gestion-pendiente-secondary--full" type="button" data-pending-notice="La consulta quedará habilitada cuando exista el origen de datos correspondiente.">
                    <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    Preparar consulta
                </button>
            <?php endif; ?>
        </aside>
    </div>

    <?php if($isCrud): ?>
        <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="<?php echo e($moduleKey); ?>-crear-title">
            <?php echo $__env->make('gestion.' . $moduleKey . '.crear', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="<?php echo e($moduleKey); ?>-editar-title">
            <?php echo $__env->make('gestion.' . $moduleKey . '.editar', ['module' => $module], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="<?php echo e($moduleKey); ?>-ver-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-ver-title">Ver <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            <?php $__currentLoopData = ($module['fields'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div><small><?php echo e($field['label']); ?></small><strong>—</strong></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>

        </dialog>
        <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="<?php echo e($moduleKey); ?>-eliminar-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-eliminar-title">Eliminar <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p>Confirma la acción antes de continuar.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Acción no disponible todavía</strong><p>No se eliminó ningún registro. Esta confirmación es visual y quedará conectada cuando exista una operación autorizada del backend.</p></div></div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminación permanece pendiente de integración y no modificó ningún dato.">Eliminar cuando se integre</button></footer>
</div>

        </dialog>
    <?php endif; ?>

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

<?php endif; ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>