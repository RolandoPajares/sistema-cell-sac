<?php $__env->startSection('title', 'Control de IMEI y series'); ?>
<?php $__env->startSection('body_class', 'modulo-inventario-logistica'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-il">
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Inventario y logística <i class="bi bi-chevron-right" aria-hidden="true"></i> Trazabilidad</span>
            <h1><i class="bi bi-phone" aria-hidden="true"></i> Control de IMEI y series</h1>
            <p>Consulta de equipos serializados registrados en las sedes autorizadas.</p>
        </div>
    </header>

    <div class="tc-il__notice" role="status">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>Los equipos serializados deben vincularse a una entrada real de inventario. Este panel no genera existencias por separado.</span>
    </div>

    <div class="tc-il__metrics">
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-boxes" aria-hidden="true"></i></span><div><small>Equipos registrados</small><strong><?php echo e(number_format($resumen['total'], 0, ',', ' ')); ?></strong><span>En sedes autorizadas</span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span><div><small>Con IMEI</small><strong><?php echo e(number_format($resumen['con_imei'], 0, ',', ' ')); ?></strong><span>Identificadores consultados</span></div></article>
        <article class="tc-il__metric tc-il__metric--violeta"><span class="tc-il__metric-icon"><i class="bi bi-upc" aria-hidden="true"></i></span><div><small>Con número de serie</small><strong><?php echo e(number_format($resumen['con_serie'], 0, ',', ' ')); ?></strong><span>Identificadores consultados</span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-list-check" aria-hidden="true"></i></span><div><small>Estados registrados</small><strong><?php echo e(number_format($resumen['estados'], 0, ',', ' ')); ?></strong><span>Valores presentes en MariaDB</span></div></article>
    </div>

    <form class="tc-il__filters" method="GET" action="<?php echo e(route('management.inventory.preview.serials')); ?>">
        <label class="tc-il__search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="buscar" value="<?php echo e(request('buscar')); ?>" maxlength="100" placeholder="Buscar por IMEI, serie, producto o variante" aria-label="Buscar dispositivos">
        </label>
        <div class="tc-il__filter-fields">
            <label>
                <span>Producto</span>
                <select name="producto">
                    <option value="">Todos los productos</option>
                    <?php $__currentLoopData = $productos;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $producto) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($producto->id); ?>" <?php if ((string) request('producto') === (string) $producto->id) {
                            echo 'selected';
                        } ?>><?php echo e($producto->marca); ?> / <?php echo e($producto->nombre); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>
                <span>Almacén</span>
                <select name="almacen">
                    <option value="">Todos los almacenes autorizados</option>
                    <?php $__currentLoopData = $almacenes;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $almacen) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($almacen->id); ?>" <?php if ((string) request('almacen') === (string) $almacen->id) {
                            echo 'selected';
                        } ?>><?php echo e($almacen->nombre); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>
                <span>Estado</span>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <?php $__currentLoopData = $estados;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $estado) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($estado); ?>" <?php if (request('estado') === $estado) {
                            echo 'selected';
                        } ?>><?php echo e($estado); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <button type="submit" class="tc-il__button tc-il__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <?php if (request()->hasAny(['buscar', 'producto', 'almacen', 'estado'])) { ?>
                <a class="tc-il__button" href="<?php echo e(route('management.inventory.preview.serials')); ?>">Limpiar filtros</a>
            <?php } ?>
        </div>
    </form>

    <div class="tc-il__main-grid">
        <article class="tc-il__card tc-il__table-card">
            <div class="tc-il__card-heading">
                <h2><i class="bi bi-list-ul" aria-hidden="true"></i> IMEI y series registradas</h2>
                <small><?php echo e(number_format($dispositivos->total(), 0, ',', ' ')); ?> resultados</small>
            </div>
            <div class="tc-il__table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>IMEI / serie</th>
                            <th>Producto</th>
                            <th>Variante</th>
                            <th>Almacén</th>
                            <th>Estado</th>
                            <th>Ingreso</th>
                            <th>Último movimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true;
$__currentLoopData = $dispositivos;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $dispositivo) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($dispositivo->identificadorLegible()); ?></td>
                                <td><?php echo e($dispositivo->producto?->marca); ?> / <?php echo e($dispositivo->producto?->nombre); ?></td>
                                <td><?php echo e($dispositivo->variante?->codigo_sku ?: $dispositivo->variante?->nombre_color ?: '—'); ?></td>
                                <td><?php echo e($dispositivo->almacen?->nombre ?? '—'); ?><?php if ($dispositivo->almacen?->sede?->nombre) { ?> / <?php echo e($dispositivo->almacen->sede->nombre); ?><?php } ?></td>
                                <td><span class="tc-il__pill"><?php echo e($dispositivo->estado); ?></span></td>
                                <td><?php echo e($dispositivo->fecha_ingreso?->format('d/m/Y H:i') ?? '—'); ?></td>
                                <td>
                                    <?php if ($dispositivo->ultimoMovimiento) { ?>
                                        <?php echo e($dispositivo->ultimoMovimiento->estado_anterior ? $dispositivo->ultimoMovimiento->estado_anterior.' → ' : ''); ?><?php echo e($dispositivo->ultimoMovimiento->estado_nuevo); ?>

                                        <small><?php echo e($dispositivo->ultimoMovimiento->creado_en?->format('d/m/Y H:i')); ?></small>
                                    <?php } else { ?>
                                        Sin movimiento vinculado
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                            <tr>
                                <td colspan="7" class="tc-il__empty">
                                    <?php if ($resumen['total'] === 0) { ?>
                                        No hay dispositivos registrados para las sedes autorizadas.
                                    <?php } else { ?>
                                        No se encontraron dispositivos con los filtros seleccionados.
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <?php if ($dispositivos->hasPages()) { ?>
                <div class="tc-il__pagination"><?php echo e($dispositivos->links()); ?></div>
            <?php } ?>
        </article>

        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Equipos por estado</h2></div>
            <?php $__empty_1 = true;
$__currentLoopData = $distribucion;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $grupo) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                <div class="tc-il__card-heading">
                    <span><?php echo e($grupo['estado']); ?></span>
                    <strong><?php echo e(number_format($grupo['cantidad'], 0, ',', ' ')); ?></strong>
                </div>
            <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                <p class="tc-il__empty">Todavía no hay estados de equipos registrados.</p>
            <?php } ?>
        </article>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>