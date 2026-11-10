<?php $__env->startSection('body_class', 'modulo-carrito'); ?>
<?php $__env->startSection('title', 'Carrito'); ?>

<?php $__env->startSection('content'); ?>
<section>
    <div class="container">
        <h1>Tu carrito</h1>
        <?php if($lines->isEmpty()): ?>
            <div class="empty panel">
                Tu carrito está vacío.<br><br>
                <a class="btn btn-primary" href="<?php echo e(route('catalog.index')); ?>"><i class="bi bi-bag" aria-hidden="true"></i> Explorar productos</a>
            </div>
        <?php else: ?>
            <div class="panel table-wrap">
                <table class="table">
                    <thead><tr><th>Producto</th><th>Precio</th><th>Cantidad</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody>
                        <?php $__currentLoopData = $lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php ($urlImagenPrincipal = $line['product']->url_imagen_principal); ?>
                            <tr>
                                <td class="cart-product-cell">
                                    <?php if($urlImagenPrincipal): ?><img src="<?php echo e($urlImagenPrincipal); ?>" alt="" loading="lazy"><?php else: ?><span class="cart-product-placeholder" aria-hidden="true"><i class="bi bi-box-seam"></i></span><?php endif; ?>
                                    <div><b><?php echo e($line['product']->marca); ?> <?php echo e($line['product']->nombre); ?></b><?php if($line['variant']): ?><small>Variante: <?php echo e($line['variant']->nombre_color); ?><?php echo e($line['variant']->codigo_sku ? ' · SKU '.$line['variant']->codigo_sku : ''); ?></small><?php endif; ?></div>
                                </td>
                                <td>S/ <?php echo e(number_format((float) $line['product']->effective_price, 2)); ?></td>
                                <td>
                                    <form
                                        class="qty"
                                        method="post"
                                        action="<?php echo e(route('cart.update', $line['product'])); ?>"
                                    >
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('PATCH'); ?>
                                        <input
                                            type="number"
                                            name="cantidad"
                                            min="0"
                                            max="<?php echo e($line['disponible']); ?>"
                                            value="<?php echo e($line['quantity']); ?>"
                                        >
                                        <button class="btn btn-ghost" type="submit">Actualizar</button>
                                    </form>
                                </td>
                                <td>S/ <?php echo e(number_format($line['subtotal'], 2)); ?></td>
                                <td>
                                    <form method="post" action="<?php echo e(route('cart.remove', $line['product'])); ?>">
                                        <?php echo csrf_field(); ?>
                                        <button class="btn btn-ghost" type="submit">
                                            <i class="bi bi-trash3" aria-hidden="true"></i>
                                            Quitar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
                <div class="cart-summary">
                    <div>
                        <form method="post" action="<?php echo e(route('cart.clear')); ?>">
                            <?php echo csrf_field(); ?>
                            <button class="btn btn-ghost" type="submit">
                                <i class="bi bi-x-circle" aria-hidden="true"></i>
                                Vaciar
                            </button>
                        </form>

                        <form class="inline-form" method="post" action="<?php echo e(route('cart.coupon')); ?>">
                            <?php echo csrf_field(); ?>
                            <label>
                                Cupón
                                <input
                                    name="codigo"
                                    value="<?php echo e(session('coupon_code')); ?>"
                                    maxlength="50"
                                >
                            </label>
                            <button class="btn btn-ghost" type="submit">Aplicar</button>
                        </form>
                    </div>
                    <div><b>Total: <span class="price">S/ <?php echo e(number_format($total, 2)); ?></span></b><a class="btn btn-primary" href="<?php echo e(route('checkout.form')); ?>"><i class="bi bi-arrow-right" aria-hidden="true"></i> Continuar</a></div>
                </div>
            </div>
            <section class="smart-cart panel">
                <div class="section-head"><div><span class="eyebrow">Carrito inteligente</span><h2><i class="bi bi-cart-check" aria-hidden="true"></i> Completa tu compra</h2><p>Agrega productos complementarios desde el catálogo para completar tu compra.</p></div></div>
                <div class="compatibility-note"><i class="bi bi-shield-check"></i><div><b>Validación del pedido</b><p>La disponibilidad del almacén autorizado y los precios se validan nuevamente al confirmar el pedido.</p></div></div>
            </section>
        <?php endif; ?>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>