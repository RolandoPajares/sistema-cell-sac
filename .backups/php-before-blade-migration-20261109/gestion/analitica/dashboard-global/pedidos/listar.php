<?php $__env->startSection('title', 'Pedidos y operaciones recientes'); ?>
<?php $__env->startSection('body_class', 'modulo-dashboard-global'); ?>

<?php $__env->startSection('content'); ?>
<?php
$statusColors = ['#0767f5', '#0bc4ee', '#38d5a1', '#ff6575', '#9b82f6'];
$statusLegend = [];
$gradientStops = [];
$position = 0;
foreach ($byStatus as $status => $count) {
    $color = $statusColors[count($statusLegend) % count($statusColors)];
    $next = $position + ($orders->count() ? $count / $orders->count() * 100 : 0);
    $gradientStops[] = sprintf('%s %.2f%% %.2f%%', $color, $position, $next);
    $statusLegend[] = ['nombre' => $status, 'valor' => number_format($count / max(1, $orders->count()) * 100, 1).'%', 'color' => $color];
    $position = $next;
}
$statusGradient = $gradientStops ? 'conic-gradient('.implode(', ', $gradientStops).')' : 'conic-gradient(#dce9f6 0 100%)';
$maxChannel = max(1, (int) $byChannel->max());
$channelBars = $byChannel->map(fn ($count, $name) => ['etiqueta' => $name, 'valor' => $count, 'altura' => max(4, round($count / $maxChannel * 100))])->values();
$exportParameters = array_filter($filters, fn ($value) => $value !== null && $value !== '');
$countStatus = fn (array $names) => $byStatus->filter(fn ($count, $status) => in_array(mb_strtolower($status), $names, true))->sum();
?>
<section class="tc-dg" data-dashboard-global="pedidos">
    <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.cabecera', [
        'titulo' => 'Pedidos y operaciones recientes',
        'icono' => 'bi-box-seam',
        'descripcion' => 'Gestiona los pedidos del negocio y monitorea las operaciones recientes.',
        'exportRoute' => $canExport ? 'management.reports.dashboard_orders.export' : null,
        'exportParameters' => $exportParameters,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <form class="tc-dg__filters tc-dg__filters--five" method="GET" action="<?php echo e(route('management.dashboard.orders')); ?>">
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
        <label><span><i class="bi bi-layers"></i> Estado</span><select name="estado"><option value="">Todos los estados</option>
            <?php $__currentLoopData = $statuses;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $status) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($status); ?>" <?php if ($filters['estado'] === $status) {
                    echo 'selected';
                } ?>><?php echo e($status); ?></option>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        </select></label>
        <label><span><i class="bi bi-tag"></i> Canal</span><select name="canal" <?php if (! $hasChannel) {
            echo 'disabled';
        } ?>><option value="">Todos los canales</option>
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
        <label><span><i class="bi bi-shop"></i> Sede</span><select name="sede" <?php if (! $hasSite) {
            echo 'disabled';
        } ?>><option value="">Todas las sedes</option>
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
        <label><span><i class="bi bi-flag"></i> Prioridad</span><select disabled title="Los pedidos aún no registran prioridad en el esquema actual"><option>Sin prioridad registrada</option></select></label>
        <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
    </form>

    <div class="tc-dg__metrics tc-dg__metrics--six">
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Pedidos nuevos', 'icono' => 'bi-cart3', 'valor' => number_format($countStatus(['pendiente', 'nuevo'])), 'detalle' => 'En el período seleccionado'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'En proceso', 'icono' => 'bi-gear', 'valor' => number_format($countStatus(['en proceso', 'procesando', 'enviado'])), 'detalle' => 'En el período seleccionado'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Entregados', 'icono' => 'bi-check-circle', 'tono' => 'verde', 'valor' => number_format($countStatus(['entregado', 'completado'])), 'detalle' => 'En el período seleccionado'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Cancelados', 'icono' => 'bi-x-lg', 'tono' => 'rojo', 'valor' => number_format($countStatus(['cancelado', 'anulado'])), 'detalle' => 'En el período seleccionado'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Tiempo promedio', 'icono' => 'bi-clock', 'tono' => 'ambar', 'valor' => '—', 'detalle' => 'Sin marca de entrega verificable'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.metrica', ['titulo' => 'Operaciones activas', 'icono' => 'bi-lightning', 'tono' => 'violeta', 'valor' => number_format($countStatus(['pendiente', 'nuevo', 'en proceso', 'procesando', 'enviado'])), 'detalle' => 'Pedidos pendientes o en proceso'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <div class="tc-dg__grid tc-dg__grid--orders">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-cart3"></i><div><h2>Pedidos recientes</h2><p>Últimos pedidos registrados en el período.</p></div><a href="<?php echo e(route('management.orders')); ?>">Ver todos los pedidos <i class="bi bi-arrow-right"></i></a></div>
            <div class="tc-dg__table-wrap"><table><thead><tr><th># Pedido</th><th>Cliente</th><th>Canal</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Prioridad</th></tr></thead><tbody>
                <?php $__empty_1 = true;
$__currentLoopData = $orders->take(5);
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $order) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <tr><td><?php echo e($order->id); ?></td><td><?php echo e($order->user?->nombre ?? 'Sin cliente asociado'); ?></td><td><?php echo e($order->canal ?? '—'); ?></td><td>S/ <?php echo e(number_format((float) $order->total, 2)); ?></td><td><span class="tc-dg__badge tc-dg__badge--<?php echo e(in_array(mb_strtolower($order->estado), ['cancelado', 'anulado'], true) ? 'red' : (in_array(mb_strtolower($order->estado), ['entregado', 'completado', 'pagado'], true) ? 'green' : 'blue')); ?>"><?php echo e($order->estado); ?></span></td><td><?php echo e($order->creado_en?->format('d/m/Y H:i')); ?></td><td>—</td></tr>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <tr><td class="tc-dg__table-empty" colspan="7">No hay pedidos en el período seleccionado.</td></tr>
                <?php } ?>
            </tbody></table></div>
        </article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-activity"></i><div><h2>Actividad operativa reciente</h2><p>Eventos registrados por el sistema.</p></div><a href="<?php echo e(route('management.audit')); ?>">Ver historial <i class="bi bi-arrow-right"></i></a></div>
            <ol class="tc-dg__timeline">
                <?php $__empty_1 = true;
$__currentLoopData = $activity;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $event) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <li><i class="bi bi-clock-history"></i><span><strong><?php echo e($event->accion); ?> · <?php echo e($event->entidad); ?></strong><small>Registro <?php echo e($event->entidad_id ?? 'general'); ?></small></span><time><?php echo e($event->creado_en?->format('d/m H:i')); ?></time></li>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <li class="tc-dg__empty">No hay eventos de auditoría registrados.</li>
                <?php } ?>
            </ol>
        </article>
    </div>

    <div class="tc-dg__grid tc-dg__grid--thirds">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-pie-chart"></i><div><h2>Pedidos por estado</h2><p>Distribución del período.</p></div></div><div class="tc-dg__chart"><?php echo $__env->make('gestion.analitica.dashboard-global.componentes.anillo', ['centro' => number_format($orders->count()), 'etiqueta' => 'Pedidos totales', 'gradiente' => $statusGradient, 'leyenda' => $statusLegend], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Operaciones por canal</h2><p>Cantidad registrada por canal.</p></div></div><div class="tc-dg__chart"><?php if ($channelBars->isEmpty()) { ?><p class="tc-dg__empty">Sin datos de canal disponibles.</p><?php } else { ?> <?php echo $__env->make('gestion.analitica.dashboard-global.componentes.grafico-barras', ['descripcion' => 'Pedidos reales por canal', 'barras' => $channelBars], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?> <?php } ?></div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-layers"></i><div><h2>Tareas pendientes y acciones rápidas</h2><p>Accesos a operaciones pendientes.</p></div></div><div class="tc-dg__task-list"><a href="<?php echo e(route('management.orders')); ?>">Revisar pedidos pendientes <b><?php echo e(number_format($countStatus(['pendiente', 'nuevo']))); ?></b><i class="bi bi-chevron-right"></i></a><a href="<?php echo e(route('management.returns')); ?>">Revisar devoluciones <i class="bi bi-chevron-right"></i></a></div></article>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>