<div class="reportes-orders-table-wrap">
    <table class="reportes-orders-table">
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Productos</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $latestOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $customer = $order->user?->customer;
                    $customerName = $customer?->razon_social ?: $customer?->nombre_contacto ?: $order->user?->nombre ?: 'Cliente';
                    $status = $orderStatuses[$order->estado] ?? ['label' => $order->estado, 'class' => 'processing'];
                ?>
                <tr>
                    <td><strong><?php echo e(str_pad((string) $order->id, 6, '0', STR_PAD_LEFT)); ?></strong></td>
                    <td><?php echo e($customerName); ?></td>
                    <td><?php echo e($order->creado_en?->format('d/m/Y')); ?></td>
                    <td><?php echo e($order->details_count); ?> <?php echo e($order->details_count === 1 ? 'producto' : 'productos'); ?></td>
                    <td><strong>S/ <?php echo e(number_format((float) $order->total, 2)); ?></strong></td>
                    <td><span class="reportes-status reportes-status--<?php echo e($status['class']); ?>"><?php echo e($status['label']); ?></span></td>
                    <td><a class="reportes-view-button" href="<?php echo e(route('orders.show', $order)); ?>" aria-label="Ver pedido <?php echo e($order->id); ?>"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr><td class="reportes-table-empty" colspan="7"><i class="bi bi-receipt" aria-hidden="true"></i><strong>No hay pedidos recientes</strong><span>Las operaciones del periodo aparecerán aquí.</span></td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
