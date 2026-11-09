<?php
use Illuminate\Support\Carbon;

$__env->startSection('title', 'Indicadores de ventas e ingresos'); ?>
<?php $__env->startSection('body_class', 'modulo-dashboard-global'); ?>

<?php $__env->startSection('content'); ?>
<?php
$maxDaily = max(1, (float) $dailyTotals->max());
$maxChannel = max(1, (float) $channelTotals->max());
$channelBars = $channelTotals->map(fn ($amount, $name) => [
    'etiqueta' => $name,
    'valor' => 'S/ '.number_format($amount, 2),
    'altura' => max(4, round($amount / $maxChannel * 100)),
])->values();
$exportParameters = array_filter(['periodo' => $filters['periodo'], 'sede' => $filters['sede'], 'canal' => $filters['canal']]);
?>
<section class="tc-dg" data-dashboard-global="ventas">
    <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.cabecera', [
        'titulo' => 'Indicadores de ventas e ingresos',
        'icono' => 'bi-graph-up-arrow',
        'descripcion' => 'Analiza el desempeño de ventas e ingresos de tu negocio.',
        'exportRoute' => $canExport ? 'management.reports.dashboard_sales.export' : null,
        'exportParameters' => $exportParameters,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form class="tc-dg__filters" method="GET" action="<?php echo e(route('management.dashboard.sales')); ?>">
        <label><span><i class="bi bi-calendar3"></i> Rango de fechas</span><select name="periodo">
            <?php $__currentLoopData = [7 => 'Últimos 7 días', 30 => 'Últimos 30 días', 90 => 'Últimos 90 días'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $days => $label) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($days); ?>" <?php if ($filters['periodo'] === $days) {
                    echo 'selected';
                } ?>><?php echo e($label); ?></option>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </select></label>
        <label><span><i class="bi bi-shop"></i> Sede</span><select name="sede" <?php if (! $hasSite) {
            echo 'disabled';
        } ?>>
            <option value="">Todas las sedes</option>
            <?php $__currentLoopData = $sites;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $site) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($site->id); ?>" <?php if ($filters['sede'] == $site->id) {
                    echo 'selected';
                } ?>><?php echo e($site->nombre); ?></option>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </select></label>
        <label><span><i class="bi bi-tag"></i> Canal de venta</span><select name="canal" <?php if (! $hasChannel) {
            echo 'disabled';
        } ?>>
            <option value="">Todos los canales</option>
            <?php $__currentLoopData = $channels;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $channel) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($channel); ?>" <?php if ($filters['canal'] === $channel) {
                    echo 'selected';
                } ?>><?php echo e($channel); ?></option>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </select></label>
        <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
    </form>

    <div class="tc-dg__metrics tc-dg__metrics--six">
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Pedidos registrados', 'icono' => 'bi-cart3', 'valor' => number_format($orderCount), 'detalle' => 'Pedidos del período'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Pedidos no cancelados', 'icono' => 'bi-coin', 'tono' => 'verde', 'valor' => number_format($nonCancelledCount), 'detalle' => 'Excluye pedidos cancelados'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Ventas confirmadas', 'icono' => 'bi-tag', 'tono' => 'violeta', 'valor' => '—', 'detalle' => 'Pendiente de fuente de ventas confirmadas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Cobros confirmados', 'icono' => 'bi-percent', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Pendiente de conciliación de pagos'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Ingresos netos', 'icono' => 'bi-check-circle', 'valor' => '—', 'detalle' => 'Pendiente de cobros y reembolsos'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Ticket promedio', 'icono' => 'bi-graph-up', 'tono' => 'verde', 'valor' => '—', 'detalle' => 'Pendiente de ventas confirmadas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="tc-dg__grid tc-dg__grid--wide">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart-line"></i><div><h2>Ventas e ingresos</h2><p>Pendiente de cobros confirmados por día.</p></div><span>Diario</span></div>
            <div class="tc-dg__chart tc-dg__chart--large">
                <?php if ($dailyTotals->isEmpty()) { ?>
                    <p class="tc-dg__empty">Pendiente de una fuente de cobros confirmados.</p>
                <?php } else { ?>
                    <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.grafico-barras', ['descripcion' => 'Ingresos diarios reales', 'barras' => $dailyTotals->map(fn ($amount, $day) => ['etiqueta' => Carbon::parse($day)->format('d/m'), 'valor' => 'S/ '.number_format($amount, 2), 'altura' => max(4, round($amount / $maxDaily * 100))])->values()], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php } ?>
            </div>
        </article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Ventas por canal</h2><p>Pendiente de ventas confirmadas por canal.</p></div></div><div class="tc-dg__chart tc-dg__chart--large">
            <?php if ($channelBars->isEmpty()) { ?>
                <p class="tc-dg__empty">Pendiente de una fuente de ventas confirmadas.</p>
            <?php } else { ?>
                <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.grafico-barras', ['descripcion' => 'Ingresos reales por canal', 'barras' => $channelBars], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php } ?>
        </div></article>
    </div>

    <div class="tc-dg__grid tc-dg__grid--thirds">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-pie-chart"></i><div><h2>Distribución de ingresos</h2><p>Participación por categoría.</p></div></div><p class="tc-dg__empty">Pendiente de vincular el detalle de venta y las categorías.</p></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-trophy"></i><div><h2>Productos más vendidos</h2><p>Ranking por unidades vendidas.</p></div></div><p class="tc-dg__empty">Pendiente de integrar el ranking con el detalle de venta.</p></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bullseye"></i><div><h2>Metas vs. resultados</h2><p>Cumplimiento de metas del período.</p></div></div><p class="tc-dg__empty">No hay metas configuradas para este período.</p></article>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>