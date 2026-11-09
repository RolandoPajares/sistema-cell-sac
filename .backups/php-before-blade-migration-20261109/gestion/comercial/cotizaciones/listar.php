<?php $__env->startSection('title', 'Cotizaciones mayoristas'); ?>

<?php $__env->startSection('content'); ?>
    <section class="container content-page">
        <h1>Cotizaciones mayoristas</h1>

        <?php echo $__env->make('gestion.comercial.cotizaciones.crear', ['customers' => $customers, 'products' => $products], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php $__currentLoopData = $quotations;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $quotation) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
    <article class="data-row">
        <div><b>Cotización #<?php echo e($quotation->id); ?> · <?php echo e($quotation->customer->nombre_contacto); ?></b><small><?php echo e($quotation->estado); ?> · <?php echo e($quotation->creado_en); ?></small></div>
        <strong>S/ <?php echo e(number_format((float) $quotation->total, 2)); ?></strong>
    </article>
<?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>

<?php echo e($quotations->links()); ?>


    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make(auth()->user()->can('gestion.cotizaciones.consultar') ? 'layouts.tienda.principal' : 'layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>