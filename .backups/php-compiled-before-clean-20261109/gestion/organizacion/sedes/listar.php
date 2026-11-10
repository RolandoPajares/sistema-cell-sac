<?php $__env->startSection('title', 'Administración de sedes'); ?>
<?php $__env->startSection('body_class', 'modulo-organizacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-org" data-organizacion="sedes" data-org-create-route="<?php echo e(route('management.company_settings.sites.store')); ?>">
    <?php echo $__env->make('gestion.organizacion.componentes.cabecera', [
        'titulo' => 'Administración de sedes',
        'icono' => 'bi-shop',
        'descripcion' => 'Consulta y administra las sedes registradas para '.($empresa?->nombre_comercial ?: $empresa?->razon_social ?: 'la empresa activa').'.',
        'acciones' => $puedeEditar && $empresa !== null
            ? [['texto' => 'Nueva sede', 'icono' => 'bi-person-plus', 'primaria' => true, 'modal' => 'nuevo']]
            : [],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(session('status')): ?>
        <div class="tc-org__alert tc-org__alert--success" role="status"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="tc-org__alert tc-org__alert--error" role="alert">
            <strong>No se pudo guardar la sede.</strong>
            <ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul>
        </div>
    <?php endif; ?>
    <?php if($empresa === null): ?>
        <div class="tc-org__alert" role="status">No hay una única empresa activa autorizada. Verifica la empresa antes de registrar sedes.</div>
    <?php endif; ?>

    <div class="tc-org__metrics">
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Total de sedes', 'valor' => $resumen['total'], 'icono' => 'bi-shop', 'detalle' => 'Empresa activa autorizada'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Sedes activas', 'valor' => $resumen['activas'], 'icono' => 'bi-shield-check', 'tono' => 'verde', 'detalle' => 'Disponibles para operaciones'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Sedes inactivas', 'valor' => $resumen['inactivas'], 'icono' => 'bi-shop-window', 'tono' => 'violeta', 'detalle' => 'Conservadas en MariaDB'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Almacenes asociados', 'valor' => $totalAlmacenes, 'icono' => 'bi-box-seam', 'detalle' => 'Conteo en las sedes de la empresa'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    </div>

    <form class="tc-org__filters" method="get" action="<?php echo e(route('management.company_settings.sites')); ?>">
        <div class="tc-org__filter-top">
            <label class="tc-org__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="<?php echo e($filtros['buscar'] ?? ''); ?>" placeholder="Buscar por código, sede o dirección" aria-label="Buscar sedes"></label>
            <button class="tc-org__button tc-org__button--apply" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            <a class="tc-org__button" href="<?php echo e(route('management.company_settings.sites')); ?>"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i> Limpiar</a>
        </div>
        <div class="tc-org__filter-fields">
            <label><span>Estado</span><select name="estado"><option value="">Todos los estados</option><option value="activa" <?php if(($filtros['estado'] ?? '') === 'activa'): echo 'selected'; endif; ?>>Activa</option><option value="inactiva" <?php if(($filtros['estado'] ?? '') === 'inactiva'): echo 'selected'; endif; ?>>Inactiva</option></select></label>
        </div>
    </form>

    <article class="tc-org__card tc-org__table-card">
        <div class="tc-org__card-title"><div><h2>Sedes registradas (<?php echo e($sedes->total()); ?>)</h2><p>Mostrando <?php echo e($sedes->count()); ?> registros consultados en MariaDB.</p></div></div>
        <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Sede</th><th>Código</th><th>Dirección</th><th>Teléfono</th><th>Zona horaria</th><th>Almacenes</th><th>Estado</th><th>Acciones</th></tr></thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $sedes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr data-org-row
                        data-org-nombre="<?php echo e($sede->nombre); ?>"
                        data-org-codigo="<?php echo e($sede->codigo); ?>"
                        data-org-direccion="<?php echo e($sede->direccion); ?>"
                        data-org-telefono="<?php echo e($sede->telefono); ?>"
                        data-org-zona-horaria="<?php echo e($sede->zona_horaria); ?>"
                        data-org-activa="<?php echo e($sede->activa ? '1' : '0'); ?>"
                        data-org-update-url="<?php echo e(route('management.company_settings.sites.update', $sede)); ?>"
                        data-org-status-url="<?php echo e(route('management.company_settings.sites.status', $sede)); ?>">
                        <td><?php echo e($sedes->firstItem() + $loop->index); ?></td>
                        <td><?php echo e($sede->nombre); ?></td>
                        <td><?php echo e($sede->codigo); ?></td>
                        <td><?php echo e($sede->direccion ?: '—'); ?></td>
                        <td><?php echo e($sede->telefono ?: '—'); ?></td>
                        <td><?php echo e($sede->zona_horaria); ?></td>
                        <td><?php echo e((int) $sede->almacenes_count); ?></td>
                        <td><?php echo e($sede->activa ? 'Activa' : 'Inactiva'); ?></td>
                        <td class="tc-org__row-actions">
                            <button type="button" data-org-open="detalle" aria-label="Ver <?php echo e($sede->nombre); ?>"><i class="bi bi-eye" aria-hidden="true"></i></button>
                            <?php if($puedeEditar): ?>
                                <button type="button" data-org-open="editar" aria-label="Editar <?php echo e($sede->nombre); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                <button type="button" data-org-open="estado" aria-label="Cambiar estado de <?php echo e($sede->nombre); ?>"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9"><p class="tc-org__no-results">No hay sedes registradas que coincidan con los filtros.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table></div>
        <?php if($sedes->hasPages()): ?>
            <nav class="pagination" aria-label="Paginación de sedes"><?php echo e($sedes->links()); ?></nav>
        <?php endif; ?>
    </article>

    <div class="tc-org__grid tc-org__grid--halves">
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-box-seam" aria-hidden="true"></i> Almacenes por sede</h2><p class="tc-org__section-copy">Conteos obtenidos de los almacenes asociados a cada sede.</p>
            <?php $__empty_1 = true; $__currentLoopData = $almacenesPorSede; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $resumenSede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="tc-org__key-list"><div><span><?php echo e($resumenSede->nombre); ?></span><strong><?php echo e((int) $resumenSede->almacenes_count); ?></strong></div></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="tc-org__no-results">No hay sedes registradas para resumir.</p>
            <?php endif; ?>
        </article>
        <article class="tc-org__card"><h2 class="tc-org__section-heading"><i class="bi bi-info-circle" aria-hidden="true"></i> Datos disponibles</h2><p class="tc-org__section-copy">Cada sede utiliza los campos definidos por el esquema vigente.</p><p>Código, nombre, dirección, teléfono, zona horaria y estado operativo.</p></article>
    </div>

    <?php echo $__env->make('gestion.organizacion.componentes.modales', ['tipo' => 'sedes'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>