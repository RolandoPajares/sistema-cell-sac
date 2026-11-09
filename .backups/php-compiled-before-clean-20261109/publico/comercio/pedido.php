<?php $__env->startSection('title', 'Pedido #'.$order->id); ?>
<?php $__env->startSection('content'); ?>
<section class="<?php echo e((auth()->user()->can('gestion.seguridad.consultar') || auth()->user()->can('gestion.ventas.consultar') || auth()->user()->can('gestion.cotizaciones.consultar')) ? 'admin-page' : 'container content-page'); ?>">
    <div class="admin-page-head"><div><span class="product-category">Pedido #<?php echo e($order->id); ?></span><h1><?php echo e($order->user->nombre); ?></h1><p>Estado actual: <strong><?php echo e($order->estado); ?></strong></p></div></div>
    <?php if(auth()->user()->can('gestion.seguridad.consultar') || auth()->user()->can('gestion.ventas.consultar') || auth()->user()->can('gestion.cotizaciones.consultar')): ?>
        <form
            method="post"
            action="<?php echo e(route('orders.status', $order)); ?>"
            class="inline-form form-card compact-form"
        >
            <?php echo csrf_field(); ?>
            <?php echo method_field('PATCH'); ?>
            <label>
                Cambiar estado
                <select name="estado">
                    <?php $__currentLoopData = ['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($state); ?>" <?php if($order->estado === $state): echo 'selected'; endif; ?>>
                            <?php echo e($state); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <button class="btn btn-primary">Actualizar estado</button>
        </form>
    <?php endif; ?>
    <?php $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <article class="data-row"><span><?php echo e($line->product->nombre); ?> × <?php echo e($line->cantidad); ?></span><strong>S/ <?php echo e(number_format($line->cantidad * $line->precio_unitario, 2)); ?></strong></article>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php if((float) $order->descuento > 0): ?><p class="notice">Cupón <?php echo e($order->cupon_codigo); ?>: -S/ <?php echo e(number_format((float) $order->descuento, 2)); ?></p><?php endif; ?>
    <div class="cart-total"><span>Total</span><strong>S/ <?php echo e(number_format((float) $order->total, 2)); ?></strong></div>
    <p class="notice">El pedido fue registrado; el pago debe coordinarse por los medios autorizados del negocio.</p>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make((auth()->user()->can('gestion.seguridad.consultar') || auth()->user()->can('gestion.ventas.consultar') || auth()->user()->can('gestion.cotizaciones.consultar')) ? 'layouts.panel.principal' : 'layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>