<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi <?php echo e($module['icon']); ?>" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow"><?php echo e($module['area']); ?></span><h2 id="<?php echo e($module['key']); ?>-<?php echo e($mode); ?>-title"><?php echo e($mode === 'crear' ? 'Nuevo' : 'Editar'); ?> <?php echo e($module['record_singular'] ?? 'registro'); ?></h2><p><?php echo e($mode === 'crear' ? 'Completa la información del nuevo registro.' : 'Actualiza la información del registro seleccionado.'); ?></p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i><div><strong>Formulario frontend</strong><p>Los campos están preparados visualmente. No se guardará información hasta conectar el módulo con una ruta y un endpoint autorizados.</p></div></div>
        <form data-pending-form>
            <?php echo $__env->make('gestion.'.$module['key'].'.formulario', ['module' => $module, 'mode' => $mode], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </form>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-primary" data-pending-notice="No se guardaron cambios: la integración backend de este módulo está pendiente."><i class="bi bi-save" aria-hidden="true"></i> Guardar cuando se integre</button></footer>
</div>
