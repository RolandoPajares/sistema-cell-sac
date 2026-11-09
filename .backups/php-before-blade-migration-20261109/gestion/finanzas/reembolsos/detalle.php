<?php $__env->startSection('title', 'Detalle de reembolso'); ?>
<?php $__env->startSection('body_class', 'modulo-reembolsos'); ?>

<?php $__env->startSection('content'); ?>
<section class="admin-finance-module admin-finance-module--reembolsos" data-finance-module="reembolsos">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <a href="<?php echo e(route('management.refunds')); ?>">Reembolsos</a>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>Solicitud #<?php echo e($record->id); ?></strong>
        </nav>
        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Finanzas</span>
                <h1>Solicitud #<?php echo e($record->id); ?></h1>
                <p>Estado: <?php echo e($record->estado); ?>. La aprobación administrativa no ejecuta una devolución de dinero.</p>
            </div>
            <div class="admin-finance-actions"><a class="admin-finance-button admin-finance-button--secondary" href="<?php echo e(route('management.refunds')); ?>">Volver al listado</a></div>
        </div>
    </header>
    <?php if (session('status')) { ?> <p class="alert alert-success" role="status"><?php echo e(session('status')); ?></p> <?php } ?>
    <?php $__currentLoopData = $errors->all();
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $error) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?> <p class="text-danger" role="alert"><?php echo e($error); ?></p> <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><h2>Datos de la solicitud</h2></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <tbody>
                    <tr><th scope="row">Devolución</th><td><?php echo e($record->devolucion?->codigo ?? 'Sin devolución'); ?></td></tr>
                    <tr><th scope="row">Resolución</th><td><?php echo e($record->devolucion?->resolucion ?: 'Sin resolución'); ?></td></tr>
                    <tr><th scope="row">Cobro original</th><td><?php echo e($record->pago?->codigo ?? 'Sin cobro'); ?></td></tr>
                    <tr><th scope="row">Importe</th><td><?php echo e($record->pago?->moneda ?? 'PEN'); ?> <?php echo e($record->importe); ?></td></tr>
                    <tr><th scope="row">Estado</th><td><?php echo e($record->estado); ?></td></tr>
                    <tr><th scope="row">Autorizado por</th><td><?php echo e($record->autorizadoPor?->nombre ?? 'Pendiente'); ?></td></tr>
                    <tr><th scope="row">Registrado el</th><td><?php echo e($record->creado_en?->format('d/m/Y H:i')); ?></td></tr>
                </tbody>
            </table>
        </div>
    </section>
    <?php if ($canApprove) { ?>
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Aprobación administrativa</h2><p>Se comprobará la evidencia del cobro original. No se enviará dinero.</p></div></header>
            <form method="POST" action="<?php echo e(route('management.refunds.approve', $record)); ?>" class="admin-finance-filters">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <input type="hidden" name="confirmacion" value="aprobar">
                <button class="admin-finance-button admin-finance-button--primary" type="submit" data-confirm="¿Aprobar esta solicitud? Esta acción no envía dinero.">Aprobar solicitud</button>
            </form>
        </section>
    <?php } ?>
    <?php if ($canCancel) { ?>
        <section class="admin-finance-table-card">
            <header class="admin-finance-card-header"><div><h2>Anular solicitud pendiente</h2></div></header>
            <form method="POST" action="<?php echo e(route('management.refunds.cancel', $record)); ?>" class="admin-finance-filters">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PATCH'); ?>
                <label>Motivo de anulación
                    <input type="text" name="motivo" value="<?php echo e(old('motivo')); ?>" minlength="10" maxlength="500" required>
                </label>
                <button class="admin-finance-button admin-finance-button--secondary" type="submit" data-confirm="¿Anular esta solicitud pendiente?">Anular solicitud</button>
            </form>
        </section>
    <?php } ?>
    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header"><div><h2>Historial de auditoría</h2></div></header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead><tr><th>Fecha</th><th>Acción</th><th>Usuario</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true;
$__currentLoopData = $history;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $event) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                        <tr><td><?php echo e($event->creado_en?->format('d/m/Y H:i')); ?></td><td><?php echo e($event->accion); ?></td><td><?php echo e($event->user?->nombre ?? 'Usuario no disponible'); ?></td></tr>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                        <tr><td colspan="3">No hay eventos registrados para esta solicitud.</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </section>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>