<?php $__env->startSection('title', 'Almacenes'); ?>
<?php $__env->startSection('body_class', 'modulo-organizacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-org" data-organizacion="almacenes" data-org-create-route="<?php echo e(route('management.company_settings.warehouses.store')); ?>">
    <?php echo $__env->make('gestion.organizacion.componentes.cabecera', [
        'titulo' => 'Almacenes',
        'icono' => 'bi-box-seam',
        'descripcion' => 'Consulta y administra almacenes registrados por sede. Las existencias provienen del inventario operativo.',
        'acciones' => $puedeEditar && $sedesActivas->isNotEmpty()
            ? [['texto' => 'Nuevo almacén', 'icono' => 'bi-box-seam', 'primaria' => true, 'modal' => 'nuevo']]
            : [],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(session('status')): ?>
        <div class="tc-org__alert tc-org__alert--success" role="status"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="tc-org__alert tc-org__alert--error" role="alert">
            <strong>No se pudo guardar el almacén.</strong>
            <ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>
    <?php if($sedesActivas->isEmpty()): ?>
        <div class="tc-org__alert" role="status">No hay sedes activas autorizadas. Registra o activa una sede antes de crear un almacén.</div>
    <?php endif; ?>

    <div class="tc-org__metrics">
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Almacenes registrados', 'valor' => $resumen['total'], 'icono' => 'bi-box-seam', 'detalle' => 'Dentro de las sedes autorizadas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Almacenes activos', 'valor' => $resumen['activos'], 'icono' => 'bi-check-circle', 'tono' => 'verde', 'detalle' => $resumen['habilitados_venta'].' habilitan ventas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Existencias físicas', 'valor' => number_format($resumen['unidades_fisicas'], 2, '.', ','), 'icono' => 'bi-boxes', 'tono' => 'violeta', 'detalle' => $resumen['productos_con_existencias'].' productos con saldo'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Sedes con almacenes', 'valor' => $resumen['sedes_con_almacenes'], 'icono' => 'bi-buildings', 'detalle' => 'De '.$sedes->count().' sedes autorizadas'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <form class="tc-org__filters" method="get" action="<?php echo e(route('management.company_settings.warehouses')); ?>">
        <div class="tc-org__filter-top">
            <label class="tc-org__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="<?php echo e($filtros['buscar'] ?? ''); ?>" placeholder="Buscar por almacén, código o sede" aria-label="Buscar almacenes"></label>
            <button class="tc-org__button tc-org__button--apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <a class="tc-org__button" href="<?php echo e(route('management.company_settings.warehouses')); ?>"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Limpiar</a>
        </div>
        <div class="tc-org__filter-fields">
            <label><span>Sede</span><select name="sede_id"><option value="">Todas las sedes</option><?php $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sede->id); ?>" <?php if((string) ($filtros['sede_id'] ?? '') === (string) $sede->id): echo 'selected'; endif; ?>><?php echo e($sede->nombre); ?> · <?php echo e($sede->codigo); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span>Tipo</span><select name="tipo"><option value="">Todos los tipos</option><?php $__currentLoopData = $tipos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tipo); ?>" <?php if(($filtros['tipo'] ?? '') === $tipo): echo 'selected'; endif; ?>><?php echo e($tipo); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activo" <?php if(($filtros['estado'] ?? '') === 'activo'): echo 'selected'; endif; ?>>Activo</option><option value="inactivo" <?php if(($filtros['estado'] ?? '') === 'inactivo'): echo 'selected'; endif; ?>>Inactivo</option></select></label>
        </div>
    </form>

    <article class="tc-org__card tc-org__table-card">
        <div class="tc-org__card-title"><div><h2>Almacenes registrados (<?php echo e($almacenes->total()); ?>)</h2><p>Mostrando <?php echo e($almacenes->count()); ?> registros consultados en MariaDB.</p></div></div>
        <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Almacén</th><th>Código</th><th>Sede</th><th>Tipo</th><th>Existencias físicas</th><th>Productos con saldo</th><th>Permite ventas</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $almacenes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $almacen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr data-org-row
                        data-org-nombre="<?php echo e($almacen->nombre); ?>"
                        data-org-codigo="<?php echo e($almacen->codigo); ?>"
                        data-org-sede-id="<?php echo e($almacen->sede_id); ?>"
                        data-org-tipo="<?php echo e($almacen->tipo); ?>"
                        data-org-permite-venta="<?php echo e($almacen->permite_venta ? '1' : '0'); ?>"
                        data-org-activo="<?php echo e($almacen->activo ? '1' : '0'); ?>"
                        data-org-update-url="<?php echo e(route('management.company_settings.warehouses.update', $almacen)); ?>"
                        data-org-status-url="<?php echo e(route('management.company_settings.warehouses.status', $almacen)); ?>">
                        <td><?php echo e($almacenes->firstItem() + $loop->index); ?></td>
                        <td><?php echo e($almacen->nombre); ?></td>
                        <td><?php echo e($almacen->codigo); ?></td>
                        <td><?php echo e($almacen->sede?->nombre ?? 'Sede no disponible'); ?></td>
                        <td><?php echo e($almacen->tipo); ?></td>
                        <td><?php echo e(number_format((float) $almacen->unidades_fisicas, 2, '.', ',')); ?></td>
                        <td><?php echo e((int) $almacen->productos_con_existencias); ?></td>
                        <td><?php echo e($almacen->permite_venta ? 'Sí' : 'No'); ?></td>
                        <td><?php echo e($almacen->activo ? 'Activo' : 'Inactivo'); ?></td>
                        <td class="tc-org__row-actions">
                            <button type="button" data-org-open="detalle" aria-label="Ver <?php echo e($almacen->nombre); ?>"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            <?php if($puedeEditar): ?>
                                <button type="button" data-org-open="editar" aria-label="Editar <?php echo e($almacen->nombre); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <button type="button" data-org-open="estado" aria-label="Cambiar estado de <?php echo e($almacen->nombre); ?>"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="10"><p class="tc-org__no-results">No hay almacenes registrados que coincidan con los filtros.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table></div>
        <?php if($almacenes->hasPages()): ?>
            <nav class="pagination" aria-label="Paginación de almacenes"><?php echo e($almacenes->links()); ?></nav>
        <?php endif; ?>
    </article>

    <div class="tc-org__grid tc-org__grid--thirds">
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-boxes" aria-hidden="true"></i> Existencias físicas por almacén</h2><p class="tc-org__section-copy">Saldo operativo sumado desde existencias_almacen.</p>
            <?php if($barrasAlmacenes->isEmpty()): ?>
                <p class="tc-org__no-results">Todavía no hay almacenes para resumir.</p>
            <?php else: ?>
                <div class="tc-org__bar-list"><?php $__currentLoopData = $barrasAlmacenes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $barra): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><span><?php echo e($barra['nombre']); ?></span><i><b style="width: <?php echo e($barra['porcentaje_relativo']); ?>%"></b></i><strong><?php echo e(number_format($barra['unidades_fisicas'], 2, '.', ',')); ?></strong></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
            <?php endif; ?>
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-buildings" aria-hidden="true"></i> Almacenes por sede</h2><p class="tc-org__section-copy">Conteo y saldo físico para sedes autorizadas.</p>
            <?php $__empty_1 = true; $__currentLoopData = $resumenSedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="tc-org__key-list"><div><span><?php echo e($sede['nombre']); ?> · <?php echo e($sede['almacenes']); ?> almacenes</span><strong><?php echo e(number_format($sede['unidades_fisicas'], 2, '.', ',')); ?> u.</strong></div></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="tc-org__no-results">No hay sedes autorizadas para mostrar.</p>
            <?php endif; ?>
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-info-circle" aria-hidden="true"></i> Datos del almacén</h2><p class="tc-org__section-copy">La tabla real no contiene capacidad ni responsable asignado; no se muestran estimaciones para esos campos.</p><p>Para consultar movimientos y el kardex, abre Inventario.</p></article>
    </div>

    <?php echo $__env->make('gestion.organizacion.componentes.modales', ['tipo' => 'almacenes'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>