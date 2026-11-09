<?php $__env->startSection('title', 'Cajas, aperturas y cierres'); ?>
<?php $__env->startSection('body_class', 'modulo-finanzas-facturacion'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-ff" data-cajas-operativas data-cajas-reopen="<?php echo e(old('_form') === 'crear' && $errors->any() ? 'crear' : ''); ?>">
    <header class="tc-ff__header">
        <div>
            <div class="tc-ff__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i><span>Finanzas y Facturación</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Cajas, aperturas y cierres</strong></div>
            <h1><i class="bi bi-cash-stack" aria-hidden="true"></i>Cajas, aperturas y cierres</h1>
            <p>Consulta y administra cajas de las sedes de la empresa activa.</p>
        </div>
        <div class="tc-ff__header-actions">
            <?php if($canCreate): ?>
                <button class="tc-ff__button tc-ff__button--primary" type="button" data-box-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva caja</button>
            <?php endif; ?>
        </div>
    </header>

    <?php if(session('status')): ?> <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p> <?php endif; ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <p class="text-danger" role="alert"><?php echo e($error); ?></p> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

    <div class="tc-ff__metrics">
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><small>Cajas registradas</small><strong><?php echo e($totalBoxes); ?></strong><span>Empresa activa</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--verde"><span class="tc-ff__metric-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span><div><small>Cajas activas</small><strong><?php echo e($activeBoxes); ?></strong><span>Disponibles para operar</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--ambar"><span class="tc-ff__metric-icon"><i class="bi bi-pause-circle" aria-hidden="true"></i></span><div><small>Cajas inactivas</small><strong><?php echo e($totalBoxes - $activeBoxes); ?></strong><span>Conservadas en el historial</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-safe" aria-hidden="true"></i></span><div><small>Sesiones abiertas</small><strong><?php echo e($openSessions); ?></strong><span>Registradas en sesiones_caja</span></div></article>
    </div>

    <form class="tc-ff__filters" method="GET" action="<?php echo e(route('management.payments.preview.cash_registers')); ?>">
        <label class="tc-ff__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" maxlength="120" placeholder="Buscar por código o nombre" aria-label="Buscar cajas"></label>
        <div class="tc-ff__filter-fields">
            <label><span>Sede</span><select name="sede_id"><option value="">Todas las sedes</option><?php $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($site->id); ?>" <?php if((string) ($filters['sede_id'] ?? '') === (string) $site->id): echo 'selected'; endif; ?>><?php echo e($site->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span>Estado</span><select name="activa"><option value="">Todas</option><option value="1" <?php if((string) ($filters['activa'] ?? '') === '1'): echo 'selected'; endif; ?>>Activa</option><option value="0" <?php if((string) ($filters['activa'] ?? '') === '0'): echo 'selected'; endif; ?>>Inactiva</option></select></label>
            <button class="tc-ff__button tc-ff__button--primary" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button>
            <a class="tc-ff__button" href="<?php echo e(route('management.payments.preview.cash_registers')); ?>">Limpiar</a>
        </div>
    </form>

    <div class="tc-ff__main-grid">
        <article class="tc-ff__card tc-ff__table-card">
            <div class="tc-ff__card-heading"><h2><i class="bi bi-list-ul" aria-hidden="true"></i>Cajas registradas</h2><small><?php echo e($boxes->total()); ?> registros</small></div>
            <div class="tc-ff__table-wrap">
                <table>
                    <thead><tr><th>Código</th><th>Sede</th><th>Nombre</th><th>Estado</th><th>Sesiones abiertas</th><th>Creación</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $boxes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $box): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($box->codigo); ?></td>
                                <td><?php echo e($box->sede?->nombre ?? 'Sede no disponible'); ?></td>
                                <td><?php echo e($box->nombre); ?></td>
                                <td><span class="tc-ff__pill tc-ff__pill--<?php echo e($box->activa ? 'verde' : 'rojo'); ?>"><?php echo e($box->activa ? 'Activa' : 'Inactiva'); ?></span></td>
                                <td><?php echo e($box->sesiones_abiertas); ?></td>
                                <td><?php echo e($box->creado_en?->format('d/m/Y H:i')); ?></td>
                                <td>
                                    <div class="tc-ff__row-actions">
                                        <?php if($canUpdate && in_array($box->sede_id, $editableSiteIds, true)): ?>
                                            <button type="button" data-box-edit data-box-edit-url="<?php echo e(route('management.payments.cash_registers.update', $box)); ?>" data-box-code="<?php echo e($box->codigo); ?>" data-box-name="<?php echo e($box->nombre); ?>" data-box-site-id="<?php echo e($box->sede_id); ?>" data-box-site-name="<?php echo e($box->sede?->nombre); ?>" title="Editar caja" aria-label="Editar caja <?php echo e($box->codigo); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                            <form method="POST" action="<?php echo e(route('management.payments.cash_registers.status.update', $box)); ?>" data-box-status>
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('PATCH'); ?>
                                                <input type="hidden" name="activa" value="<?php echo e($box->activa ? '0' : '1'); ?>">
                                                <button type="submit" data-box-status-label="<?php echo e($box->activa ? 'desactivar' : 'activar'); ?>" title="<?php echo e($box->activa ? 'Desactivar' : 'Activar'); ?> caja" aria-label="<?php echo e($box->activa ? 'Desactivar' : 'Activar'); ?> caja <?php echo e($box->codigo); ?>"><i class="bi <?php echo e($box->activa ? 'bi-pause-circle' : 'bi-play-circle'); ?>" aria-hidden="true"></i></button>
                                            </form>
                                        <?php else: ?>
                                            <span>Solo consulta</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="7">No hay cajas que coincidan con los filtros.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($boxes->hasPages()): ?>
                <div class="tc-ff__card-heading">
                    <small>Página <?php echo e($boxes->currentPage()); ?> de <?php echo e($boxes->lastPage()); ?></small>
                    <div class="tc-ff__header-actions">
                        <?php if($boxes->previousPageUrl()): ?> <a class="tc-ff__button" href="<?php echo e($boxes->previousPageUrl()); ?>">Anterior</a> <?php endif; ?>
                        <?php if($boxes->nextPageUrl()): ?> <a class="tc-ff__button" href="<?php echo e($boxes->nextPageUrl()); ?>">Siguiente</a> <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </article>
        <aside class="tc-ff__card tc-ff__aside">
            <h2><i class="bi bi-geo-alt" aria-hidden="true"></i>Cajas por sede</h2>
            <p>Distribución de los registros activos e inactivos en la empresa.</p>
            <div class="tc-ff__summary">
                <strong><?php echo e($totalBoxes); ?> cajas</strong>
                <?php $__empty_1 = true; $__currentLoopData = $sites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div><span><?php echo e($site->nombre); ?></span><b><?php echo e($siteCounts[$site->id] ?? 0); ?></b></div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div><span>No hay sedes activas.</span></div>
                <?php endif; ?>
            </div>
        </aside>
    </div>

    <div class="tc-ff__bottom-grid">
        <article class="tc-ff__card"><div class="tc-ff__card-heading"><h2><i class="bi bi-check-circle" aria-hidden="true"></i>Disponibilidad</h2></div><div class="tc-ff__ranking"><div><b>1</b><span>Cajas activas</span><strong><?php echo e($activeBoxes); ?></strong></div><div><b>2</b><span>Cajas inactivas</span><strong><?php echo e($totalBoxes - $activeBoxes); ?></strong></div></div></article>
        <article class="tc-ff__card"><div class="tc-ff__card-heading"><h2><i class="bi bi-safe" aria-hidden="true"></i>Sesiones</h2></div><div class="tc-ff__ranking"><div><b>1</b><span>Abiertas</span><strong><?php echo e($openSessions); ?></strong></div></div></article>
        <article class="tc-ff__card"><div class="tc-ff__card-heading"><h2><i class="bi bi-shield-check" aria-hidden="true"></i>Control operativo</h2></div><p>Una caja con sesión abierta no puede editarse ni desactivarse.</p></article>
    </div>

    <?php if($canCreate): ?>
        <dialog class="tc-ff__dialog" data-box-dialog="crear" aria-labelledby="box-create-title">
            <form class="tc-ff__dialog-card" method="POST" action="<?php echo e(route('management.payments.cash_registers.store')); ?>">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="_form" value="crear">
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><h2 id="box-create-title">Nueva caja</h2><p>Registro administrativo de una caja real.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <label class="tc-ff__field">Sede *<select name="sede_id" required><option value="">Selecciona una sede</option><?php $__currentLoopData = $createSites; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $site): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($site->id); ?>" <?php if((string) old('sede_id') === (string) $site->id): echo 'selected'; endif; ?>><?php echo e($site->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                    <label class="tc-ff__field">Código *<input type="text" name="codigo" value="<?php echo e(old('codigo')); ?>" maxlength="40" required></label>
                    <label class="tc-ff__field tc-ff__field--full">Nombre *<input type="text" name="nombre" value="<?php echo e(old('nombre')); ?>" maxlength="120" required></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Guardar caja</button></footer>
            </form>
        </dialog>
    <?php endif; ?>
    <?php if($canUpdate): ?>
        <dialog class="tc-ff__dialog" data-box-dialog="editar" aria-labelledby="box-edit-title">
            <form class="tc-ff__dialog-card" method="POST" data-box-edit-form>
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span><div><h2 id="box-edit-title">Editar caja</h2><p>La sede original no puede cambiarse.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <input type="hidden" name="sede_id" data-box-field="sede_id">
                    <label class="tc-ff__field">Sede<input type="text" data-box-field="sede_nombre" disabled></label>
                    <label class="tc-ff__field">Código *<input type="text" name="codigo" maxlength="40" required data-box-field="codigo"></label>
                    <label class="tc-ff__field tc-ff__field--full">Nombre *<input type="text" name="nombre" maxlength="120" required data-box-field="nombre"></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Guardar cambios</button></footer>
            </form>
        </dialog>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>