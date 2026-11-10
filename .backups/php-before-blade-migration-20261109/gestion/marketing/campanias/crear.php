<form method="POST" action="<?php echo e(route('management.campaigns.store')); ?>" class="form-card" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>

    <h2>Nueva campaña</h2>

    <?php echo $__env->make('gestion.marketing.campanias.formulario', [
        'campaign' => null,
        'segments' => $segments,
    ], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <div class="campanias-form-actions">
        <button type="button" class="campanias-form-cancel" data-close-campaign-dialog>Cancelar</button>
        <button type="submit" class="campanias-form-submit"><i class="bi bi-send" aria-hidden="true"></i> Guardar campaña</button>
    </div>
</form>
