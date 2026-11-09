<?php $__env->startSection('title', 'Envíos y entregas'); ?>
<?php $__env->startSection('body_class', 'modulo-inventario-logistica'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-il">
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Inventario y logística <i class="bi bi-chevron-right" aria-hidden="true"></i> Ecommerce</span>
            <h1><i class="bi bi-truck" aria-hidden="true"></i> Envíos y entregas</h1>
            <p>Consulta de envíos de pedidos y sus últimos eventos registrados.</p>
        </div>
        <details class="productos-export-menu tc-il__export-menu">
            <summary><i class="bi bi-download" aria-hidden="true"></i><span>Exportar</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
            <div class="productos-export-options">
                <a href="<?php echo e(route('management.inventory.preview.shipments.export', ['format' => 'pdf'] + request()->query())); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span>Exportar PDF<small>Documento imprimible</small></span></a>
                <a href="<?php echo e(route('management.inventory.preview.shipments.export', ['format' => 'excel'] + request()->query())); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span>Exportar Excel<small>Archivo compatible .xlsx</small></span></a>
                <a href="<?php echo e(route('management.inventory.preview.shipments.export', ['format' => 'csv'] + request()->query())); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span>Exportar CSV<small>Datos delimitados</small></span></a>
            </div>
        </details>
    </header>

    <div class="tc-il__notice" role="status">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <?php if(Route::has('management.inventory.shipments.status.update')): ?>
        <span>Los cambios de estado registran un evento histórico y quedan limitados a las sedes autorizadas.</span>
        <?php else: ?>
        <span>La consulta está operativa. La actualización de estados quedará disponible cuando el integrador conecte su ruta RBAC.</span>
        <?php endif; ?>
    </div>

    <div class="tc-il__metrics">
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
            <div><small>Envíos registrados</small><strong><?php echo e(number_format($resumen['total'], 0, ',', ' ')); ?></strong><span>En sedes autorizadas</span></div>
        </article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-upc" aria-hidden="true"></i></span>
            <div><small>Con seguimiento</small><strong><?php echo e(number_format($resumen['con_seguimiento'], 0, ',', ' ')); ?></strong><span>Números guardados</span></div>
        </article>
        <article class="tc-il__metric tc-il__metric--violeta"><span class="tc-il__metric-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
            <div><small>Con guía de remisión</small><strong><?php echo e(number_format($resumen['con_guia'], 0, ',', ' ')); ?></strong><span>Referencias almacenadas</span></div>
        </article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span>
            <div><small>Modalidades registradas</small><strong><?php echo e(number_format($resumen['modalidades'], 0, ',', ' ')); ?></strong><span>Valores presentes en MariaDB</span></div>
        </article>
    </div>

    <form class="tc-il__filters" method="GET" action="<?php echo e(route('management.inventory.preview.shipments')); ?>">
        <label class="tc-il__search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="buscar" value="<?php echo e(request('buscar')); ?>" maxlength="100" placeholder="Buscar por código, pedido, transportista o seguimiento" aria-label="Buscar envíos">
        </label>
        <div class="tc-il__filter-fields">
            <label>
                <span>Estado</span>
                <select name="estado">
                    <option value="">Todos los estados registrados</option>
                    <?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($estado); ?>" <?php if(request('estado')===$estado): echo 'selected'; endif; ?>><?php echo e($estado); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>
                <span>Modalidad</span>
                <select name="modalidad">
                    <option value="">Todas las modalidades registradas</option>
                    <?php $__currentLoopData = $modalidades; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $modalidad): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($modalidad); ?>" <?php if(request('modalidad')===$modalidad): echo 'selected'; endif; ?>><?php echo e($modalidad); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <button type="submit" class="tc-il__button tc-il__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <?php if(request()->hasAny(['buscar', 'estado', 'modalidad'])): ?>
            <a class="tc-il__button" href="<?php echo e(route('management.inventory.preview.shipments')); ?>">Limpiar filtros</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="tc-il__main-grid">
        <article class="tc-il__card tc-il__table-card">
            <div class="tc-il__card-heading">
                <h2><i class="bi bi-truck" aria-hidden="true"></i> Envíos registrados</h2>
                <small><?php echo e(number_format($envios->total(), 0, ',', ' ')); ?> resultados</small>
            </div>
            <div class="tc-il__table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Pedido</th>
                            <th>Almacén / sede</th>
                            <th>Transportista / servicio</th>
                            <th>Seguimiento</th>
                            <th>Estado</th>
                            <th>Último evento</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $envios; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $envio): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($envio->codigo); ?></td>
                            <td>Pedido #<?php echo e($envio->pedido_id); ?></td>
                            <td><?php echo e($envio->almacen?->nombre ?? '—'); ?><?php if($envio->almacen?->sede?->nombre): ?> / <?php echo e($envio->almacen->sede->nombre); ?><?php endif; ?></td>
                            <td><?php echo e($envio->transportista ?: '—'); ?><?php if($envio->servicio): ?> / <?php echo e($envio->servicio); ?><?php endif; ?></td>
                            <td><?php echo e($envio->numero_seguimiento ?: '—'); ?></td>
                            <td><span class="tc-il__pill"><?php echo e($envio->estado); ?></span></td>
                            <td>
                                <?php if($envio->ultimoEvento): ?>
                                <?php echo e($envio->ultimoEvento->estado); ?><?php if($envio->ultimoEvento->descripcion): ?> — <?php echo e($envio->ultimoEvento->descripcion); ?><?php endif; ?>
                                <small><?php echo e($envio->ultimoEvento->ocurrido_en?->format('d/m/Y H:i')); ?></small>
                                <?php else: ?>
                                Sin eventos registrados
                                <?php endif; ?>
                            </td>
                            <td>
                                <a class="tc-il__button" href="<?php echo e(route('management.inventory.preview.shipments.show', $envio)); ?>" aria-label="Ver detalle de <?php echo e($envio->codigo); ?>"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalle</a>
                                <?php if(Route::has('management.inventory.shipments.status.update')): ?>
                                <details>
                                    <summary class="tc-il__button">Actualizar</summary>
                                    <form method="POST" action="<?php echo e(route('management.inventory.shipments.status.update', $envio)); ?>">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <label>Estado
                                            <select name="estado" required>
                                                <?php $__currentLoopData = ['pendiente' => 'Pendiente', 'preparando' => 'Preparando', 'despachado' => 'Despachado', 'en_transito' => 'En tránsito', 'entregado' => 'Entregado', 'incidencia' => 'Incidencia', 'cancelado' => 'Cancelado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $status => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($status); ?>" <?php if($envio->estado === $status): echo 'selected'; endif; ?>><?php echo e($label); ?></option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </label>
                                        <label>Descripción
                                            <textarea name="descripcion" maxlength="500" rows="2" placeholder="Motivo o detalle del evento"></textarea>
                                        </label>
                                        <button type="submit" class="tc-il__button tc-il__button--primary">Registrar estado</button>
                                    </form>
                                </details>
                                <?php else: ?>
                                <span aria-label="Actualización pendiente de integración">Pendiente de ruta</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="tc-il__empty">
                                <?php if($resumen['total'] === 0): ?>
                                No hay envíos registrados para las sedes autorizadas.
                                <?php else: ?>
                                No se encontraron envíos con los filtros seleccionados.
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($envios->hasPages()): ?>
            <div class="tc-il__pagination"><?php echo e($envios->links()); ?></div>
            <?php endif; ?>
        </article>

        <article class="tc-il__card">
            <div class="tc-il__card-heading">
                <h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Envíos por estado</h2>
            </div>
            <?php $__empty_1 = true; $__currentLoopData = $distribucion; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $grupo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="tc-il__card-heading"><span><?php echo e($grupo['estado']); ?></span><strong><?php echo e(number_format($grupo['cantidad'], 0, ',', ' ')); ?></strong></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p class="tc-il__empty">Todavía no hay estados de envío registrados.</p>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>