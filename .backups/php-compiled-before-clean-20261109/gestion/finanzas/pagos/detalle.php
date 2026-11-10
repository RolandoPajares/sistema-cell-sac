<?php $__env->startSection('title', 'Detalle de pago'); ?>
<?php $__env->startSection('body_class', 'modulo-pagos'); ?>

<?php $__env->startSection('content'); ?>
<section class="admin-finance-module admin-finance-module--pagos" data-finance-module="pagos">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <a href="<?php echo e(route('management.payments')); ?>">Pagos y cobros</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong><?php echo e($record->codigo); ?></strong>
        </nav>
        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi bi-credit-card" aria-hidden="true"></i> Finanzas</span>
                <h1><?php echo e($record->codigo); ?></h1>
                <p>Estado registrado: <?php echo e($record->estado); ?>. Una solicitud pendiente no confirma dinero recibido o enviado.</p>
            </div>
            <div class="admin-finance-actions"><a class="admin-finance-button admin-finance-button--secondary" href="<?php echo e(route('management.payments')); ?>">Volver al listado</a></div>
        </div>
    </header>
    <?php if(session('status')): ?> <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p> <?php endif; ?>
    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <p class="text-danger" role="alert"><?php echo e($error); ?></p> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><span class="admin-finance-card-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span><div><h2>Datos del registro</h2><p>Información guardada en pagos.</p></div></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <tbody>
                    <tr><th scope="row">Tipo</th><td><?php echo e($record->tipo); ?></td></tr>
                    <tr><th scope="row">Estado</th><td><?php echo e($record->estado); ?></td></tr>
                    <tr><th scope="row">Importe</th><td><?php echo e($record->moneda); ?> <?php echo e($record->importe); ?></td></tr>
                    <tr><th scope="row">Medio</th><td><?php echo e($record->medioPago?->nombre ?? 'Sin medio asociado'); ?></td></tr>
                    <tr><th scope="row">Sesión de caja</th><td><?php echo e($record->sesion_caja_id ? '#'.$record->sesion_caja_id : 'No aplica'); ?></td></tr>
                    <tr><th scope="row">Cuenta bancaria</th><td><?php echo e($record->cuentaBancaria ? $record->cuentaBancaria->codigo.' · '.$record->cuentaBancaria->banco : 'No aplica'); ?></td></tr>
                    <tr><th scope="row">Origen</th><td><?php echo e($record->venta?->codigo ?? ($record->pedido_id ? 'Pedido #'.$record->pedido_id : ($record->compra?->numero_referencia ?: ($record->compra_id ? 'Compra #'.$record->compra_id : 'Sin documento')))); ?></td></tr>
                    <tr><th scope="row">Referencia</th><td><?php echo e($record->referencia ?: 'Sin referencia'); ?></td></tr>
                    <tr><th scope="row">Registrado por</th><td><?php echo e($record->usuario?->nombre ?? 'Usuario no disponible'); ?></td></tr>
                    <tr><th scope="row">Fecha</th><td><?php echo e($record->creado_en?->format('d/m/Y H:i')); ?></td></tr>
                </tbody>
            </table>
        </div>
    </section>
    <?php if($record->estado === 'pendiente' && $record->medioPago?->requiere_banco): ?>
        <p class="alert alert-warning" role="status">Transferencia declarada. La referencia debe verificarse y conciliarse administrativamente antes de confirmar dinero recibido o enviado.</p>
    <?php endif; ?>
    <?php if($canConfirmCash): ?>
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Verificación manual de efectivo</h2><p>La sesión debe permanecer abierta. Esta acción registrará una entrada o salida real de caja.</p></div></header>
            <form method="POST" action="<?php echo e(route('management.payments.cash.update', $record)); ?>" class="admin-finance-filters">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <label>Importe contado (S/) *<input type="text" name="importe_contado" inputmode="decimal" value="<?php echo e(old('importe_contado')); ?>" required></label>
                <label>Observación de verificación *<input type="text" name="observacion" value="<?php echo e(old('observacion')); ?>" maxlength="500" required></label>
                <button class="admin-finance-button admin-finance-button--primary" type="submit" data-confirm="¿Confirmas que contaste este efectivo y deseas registrar el movimiento?">Confirmar efectivo contado</button>
            </form>
        </section>
    <?php endif; ?>
    <?php if($canCancel): ?>
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Anular solicitud pendiente</h2></div></header>
            <form method="POST" action="<?php echo e(route('management.payments.cancel', $record)); ?>" class="admin-finance-filters">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <label>Motivo de anulación
                    <input type="text" name="motivo" value="<?php echo e(old('motivo')); ?>" minlength="10" maxlength="500" required>
                </label>
                <button class="admin-finance-button admin-finance-button--secondary" type="submit" data-confirm="¿Anular esta solicitud pendiente?">Anular solicitud</button>
            </form>
        </section>
    <?php endif; ?>
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><h2>Historial de auditoría</h2></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $history; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr><td><?php echo e($event->creado_en?->format('d/m/Y H:i')); ?></td><td><?php echo e($event->accion); ?></td><td><?php echo e($event->user?->nombre ?? 'Usuario no disponible'); ?></td></tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3">No hay eventos registrados para este pago.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>