<?php $__env->startSection('title', 'Asignación de responsables'); ?>
<?php $__env->startSection('body_class', 'modulo-organizacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-org" data-organizacion="responsables" data-org-create-route="<?php echo e(route('management.company_settings.responsibles.store')); ?>">
    <?php echo $__env->make('gestion.organizacion.componentes.cabecera', [
        'titulo' => 'Asignación de responsables',
        'icono' => 'bi-person-check',
        'descripcion' => 'Consulta el historial y reasigna responsables principales por recurso y cargo.',
        'acciones' => $puedeEditar && $estructuraDisponible ? [['texto' => 'Nueva asignación', 'icono' => 'bi-person-check', 'primaria' => true, 'modal' => 'nuevo']] : [],
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php if(session('status')): ?>
        <div class="tc-org__alert tc-org__alert--success" role="status"><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="tc-org__alert tc-org__alert--error" role="alert"><strong>No se pudo guardar la asignación.</strong><ul><?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></ul></div>
    <?php endif; ?>
    <?php if (! ($estructuraDisponible)): ?>
        <div class="tc-org__alert" role="status"><strong>La tabla de responsables aún no está instalada.</strong><p>El formulario queda preparado. Revise <code>database/technology_cell_responsables_proposal.sql</code> y autorice su aplicación para registrar datos.</p></div>
    <?php else: ?>
        <div class="tc-org__metrics">
            <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Asignaciones registradas', 'valor' => $total, 'icono' => 'bi-person-lines-fill', 'detalle' => 'Historial conservado en MariaDB'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('gestion.organizacion.componentes.metrica', ['titulo' => 'Responsables vigentes', 'valor' => $vigentes, 'icono' => 'bi-person-check', 'tono' => 'verde', 'detalle' => 'Vigencia actual por recurso y cargo'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <article class="tc-org__card tc-org__table-card">
            <div class="tc-org__card-title"><div><h2>Historial de responsables (<?php echo e($total); ?>)</h2><p>Asignaciones consultadas en MariaDB para las sedes autorizadas.</p></div></div>
            <div class="tc-org__table-wrap"><table><thead><tr><th>#</th><th>Recurso</th><th>Sede</th><th>Cargo</th><th>Responsable</th><th>Vigente desde</th><th>Vigente hasta</th><th>Relevo de</th></tr></thead><tbody>
                <?php $__empty_1 = true; $__currentLoopData = $responsables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $responsable): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr><td><?php echo e($responsable->id); ?></td><td><?php echo e($responsable->tipo_recurso === 'almacen' ? $responsable->almacen?->nombre : $responsable->sede?->nombre); ?></td><td><?php echo e($responsable->sede?->nombre); ?></td><td><?php echo e($responsable->cargo); ?></td><td><?php echo e($responsable->responsable?->nombre); ?></td><td><?php echo e($responsable->vigente_desde->format('d/m/Y')); ?></td><td><?php echo e($responsable->vigente_hasta?->format('d/m/Y') ?? 'Vigente'); ?></td><td><?php echo e($responsable->reemplaza_asignacion_id ?? '—'); ?></td></tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8"><p class="tc-org__no-results">No hay responsables registrados. Puedes crear la primera asignación cuando la estructura esté instalada.</p></td></tr>
                <?php endif; ?>
            </tbody></table></div>
        </article>
    <?php endif; ?>
    <?php echo $__env->make('gestion.organizacion.componentes.modales', ['tipo' => 'responsables'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>