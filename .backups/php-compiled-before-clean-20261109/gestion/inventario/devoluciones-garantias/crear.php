<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">Operaciones comerciales</span><h2 id="devoluciones-garantias-crear-title">Nueva solicitud</h2><p>Registra una devolución o garantía y asigna su seguimiento.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <form method="POST" action="<?php echo e(route('management.returns.store')); ?>" data-pending-backend-form>
        <?php echo csrf_field(); ?>
        <div class="gestion-pendiente-modal-body">
            <?php echo $__env->make('gestion.inventario.devoluciones-garantias.formulario', ['module' => $module, 'mode' => 'crear'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="submit" class="gestion-pendiente-primary"><i class="bi bi-check2" aria-hidden="true"></i> Registrar solicitud</button></footer>
    </form>
</div>
