<?php $__env->startSection('body_class', 'modulo-checkout'); ?>
<?php $__env->startSection('title', 'Finalizar pedido'); ?>

<?php $__env->startSection('content'); ?>
<section>
    <div class="container">
        <div class="auth-wrap panel">
            <h1>Finalizar pedido</h1>
            <?php if(session('status')): ?><div class="alert alert-success"><?php echo e(session('status')); ?></div><a class="btn btn-ghost" href="<?php echo e(route('orders.index')); ?>">Ver mis pedidos</a><?php endif; ?>
            <?php if($lines->isEmpty()): ?>
                <p>No hay productos pendientes.</p>
                <a class="btn btn-primary" href="<?php echo e(route('catalog.index')); ?>">Volver al catálogo</a>
            <?php else: ?>
                <p>Este proyecto registra una solicitud de compra; no procesa pagos en línea.</p>
                <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="data-row"><span><?php echo e($line['product']->nombre); ?> × <?php echo e($line['quantity']); ?></span><strong>S/ <?php echo e(number_format($line['subtotal'], 2)); ?></strong></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <div class="cart-total"><b>Total</b><strong>S/ <?php echo e(number_format($total, 2)); ?></strong></div>
                <?php if (! ($puedeConfirmar)): ?><p>La disponibilidad cambió. Revisa las cantidades en tu carrito antes de confirmar.</p><?php endif; ?>
                <form method="post" action="<?php echo e(route('checkout')); ?>">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="checkout_token" value="<?php echo e($checkoutToken); ?>">
                    <button class="btn btn-primary" style="width:100%" <?php if(! $puedeConfirmar): echo 'disabled'; endif; ?>>
                        Confirmar pedido
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>