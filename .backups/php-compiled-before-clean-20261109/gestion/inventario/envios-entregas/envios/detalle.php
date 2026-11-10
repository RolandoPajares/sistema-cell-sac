<?php $__env->startSection('title', 'Detalle de envío '.$envio->codigo); ?>
<?php $__env->startSection('body_class', 'modulo-inventario-logistica'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-il">
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><a href="<?php echo e(route('management.inventory.preview.shipments')); ?>">Envíos y entregas</a> <i class="bi bi-chevron-right" aria-hidden="true"></i> Detalle</span>
            <h1><i class="bi bi-truck" aria-hidden="true"></i> <?php echo e($envio->codigo); ?></h1>
            <p>Detalle consultado desde MariaDB dentro de las sedes autorizadas.</p>
        </div>
    </header>

    <div class="tc-il__metrics">
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Pedido</small><strong>#<?php echo e($envio->pedido_id); ?></strong><span><?php echo e($envio->pedido?->numero_referencia ?: 'Sin referencia'); ?></span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span><div><small>Estado actual</small><strong><?php echo e($envio->estado); ?></strong><span><?php echo e($envio->modalidad); ?></span></div></article>
        <article class="tc-il__metric tc-il__metric--violeta"><span class="tc-il__metric-icon"><i class="bi bi-upc" aria-hidden="true"></i></span><div><small>Seguimiento</small><strong><?php echo e($envio->numero_seguimiento ?: '—'); ?></strong><span><?php echo e($envio->transportista ?: 'Transportista no registrado'); ?></span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-calendar-event" aria-hidden="true"></i></span><div><small>Entrega estimada</small><strong><?php echo e($envio->estimado_entrega_en?->format('d/m/Y') ?: '—'); ?></strong><span><?php echo e($envio->almacen?->nombre ?: 'Almacén no registrado'); ?></span></div></article>
    </div>

    <div class="tc-il__main-grid">
        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-info-circle" aria-hidden="true"></i> Información del envío</h2></div>
            <dl class="tc-il__detail-list">
                <div><dt>Código</dt><dd><?php echo e($envio->codigo); ?></dd></div>
                <div><dt>Sede</dt><dd><?php echo e($envio->almacen?->sede?->nombre ?: '—'); ?></dd></div>
                <div><dt>Almacén</dt><dd><?php echo e($envio->almacen?->nombre ?: '—'); ?></dd></div>
                <div><dt>Servicio</dt><dd><?php echo e($envio->servicio ?: '—'); ?></dd></div>
                <div><dt>Despachado</dt><dd><?php echo e($envio->despachado_en?->format('d/m/Y H:i') ?: 'No registrado'); ?></dd></div>
                <div><dt>Entregado</dt><dd><?php echo e($envio->entregado_en?->format('d/m/Y H:i') ?: 'No registrado'); ?></dd></div>
            </dl>
        </article>
        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-clock-history" aria-hidden="true"></i> Trazabilidad</h2><small><?php echo e($envio->eventos->count()); ?> eventos</small></div>
            <?php $__empty_1 = true; $__currentLoopData = $envio->eventos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $evento): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="tc-il__card-heading"><span><?php echo e($evento->estado); ?></span><strong><?php echo e($evento->ocurrido_en?->format('d/m/Y H:i')); ?></strong><small><?php echo e($evento->descripcion ?: 'Sin descripción'); ?></small></div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="tc-il__empty">No hay eventos registrados para este envío.</p>
            <?php endif; ?>
        </article>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>