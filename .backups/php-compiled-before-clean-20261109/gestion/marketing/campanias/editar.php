<form method="post" class="form-card compact-form" action="<?php echo e(route('management.campaigns.update', $campaign)); ?>" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <?php echo method_field('PUT'); ?>
    <?php echo $__env->make('gestion.marketing.campanias.formulario', ['campaign' => $campaign, 'segments' => $segments], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <div class="campanias-form-actions">
        <button type="button" class="campanias-form-cancel" data-close-campaign-dialog>Cancelar</button>
        <button type="submit" class="campanias-form-submit"><i class="bi bi-check2-circle" aria-hidden="true"></i> Guardar cambios</button>
    </div>
</form>