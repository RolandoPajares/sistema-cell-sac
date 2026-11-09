<?php
$formData = $module['form_data'] ?? ['customers' => [], 'orders' => [], 'products' => []];
$values = $values ?? [];
$fieldId = static fn (string $field): string => 'devolucion-'.($mode ?? 'form').'-'.$field;
?>

<div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Seguimiento de la solicitud</strong><p>Selecciona el pedido, cliente y producto relacionados para conservar la trazabilidad del caso.</p></div></div>
<div class="gestion-pendiente-form-fields">
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('codigo')); ?>"><span>Código de solicitud <b>*</b></span><input id="<?php echo e($fieldId('codigo')); ?>" name="codigo" type="text" maxlength="40" required placeholder="Ej. DEV-2026-0001" data-pending-field="codigo" value="<?php echo e(old('codigo', $values['codigo'] ?? '')); ?>"></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('pedido')); ?>"><span>Pedido relacionado <b>*</b></span><select id="<?php echo e($fieldId('pedido')); ?>" name="pedido_id" required data-pending-field="pedido_id"><option value="">Seleccionar pedido</option><?php $__currentLoopData = $formData['orders'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $order) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($order->id); ?>" <?php if ((string) old('pedido_id', $values['pedido_id'] ?? '') === (string) $order->id) {
        echo 'selected';
    } ?>>#<?php echo e($order->id); ?> · <?php echo e($order->user?->nombre ?? 'Cliente'); ?> · <?php echo e($order->creado_en?->format('d/m/Y')); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('cliente')); ?>"><span>Cliente <b>*</b></span><select id="<?php echo e($fieldId('cliente')); ?>" name="cliente_id" required data-pending-field="cliente_id"><option value="">Seleccionar cliente</option><?php $__currentLoopData = $formData['customers'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $customer) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($customer->id); ?>" <?php if ((string) old('cliente_id', $values['cliente_id'] ?? '') === (string) $customer->id) {
        echo 'selected';
    } ?>><?php echo e($customer->nombre_contacto ?: $customer->razon_social); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('producto')); ?>"><span>Producto <small>(opcional)</small></span><select id="<?php echo e($fieldId('producto')); ?>" name="producto_id" data-pending-field="producto_id"><option value="">Seleccionar producto</option><?php $__currentLoopData = $formData['products'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $product) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->id); ?>" <?php if ((string) old('producto_id', $values['producto_id'] ?? '') === (string) $product->id) {
        echo 'selected';
    } ?>><?php echo e($product->nombre); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('tipo')); ?>"><span>Tipo <b>*</b></span><select id="<?php echo e($fieldId('tipo')); ?>" name="tipo" required data-pending-field="tipo"><option value="">Seleccionar tipo</option><?php $__currentLoopData = ['Devolución', 'Garantía', 'Cambio'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $type) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($type); ?>" <?php if (old('tipo', $values['tipo'] ?? '') === $type) {
        echo 'selected';
    } ?>><?php echo e($type); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('estado')); ?>"><span>Estado <b>*</b></span><select id="<?php echo e($fieldId('estado')); ?>" name="estado" required data-pending-field="estado"><option value="">Seleccionar estado</option><?php $__currentLoopData = ['Recibida', 'En evaluación', 'Aprobada', 'Rechazada', 'Resuelta'];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $status) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><option value="<?php echo e($status); ?>" <?php if (old('estado', $values['estado'] ?? '') === $status) {
        echo 'selected';
    } ?>><?php echo e($status); ?></option><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></select></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('fecha')); ?>"><span>Fecha de solicitud <b>*</b></span><input id="<?php echo e($fieldId('fecha')); ?>" name="fecha_solicitud" type="date" required data-pending-field="fecha_solicitud" value="<?php echo e(old('fecha_solicitud', $values['fecha_solicitud'] ?? now()->toDateString())); ?>"></label>
    <label class="gestion-pendiente-field" for="<?php echo e($fieldId('limite')); ?>"><span>Fecha límite</span><input id="<?php echo e($fieldId('limite')); ?>" name="fecha_limite" type="date" data-pending-field="fecha_limite" value="<?php echo e(old('fecha_limite', $values['fecha_limite'] ?? '')); ?>"></label>
    <label class="gestion-pendiente-field gestion-pendiente-field--wide" for="<?php echo e($fieldId('motivo')); ?>"><span>Motivo <b>*</b></span><textarea id="<?php echo e($fieldId('motivo')); ?>" name="motivo" rows="4" maxlength="5000" required placeholder="Describe el motivo del caso..." data-pending-field="motivo"><?php echo e(old('motivo', $values['motivo'] ?? '')); ?></textarea></label>
    <label class="gestion-pendiente-field gestion-pendiente-field--wide" for="<?php echo e($fieldId('resolucion')); ?>"><span>Resolución <small>(opcional)</small></span><textarea id="<?php echo e($fieldId('resolucion')); ?>" name="resolucion" rows="3" maxlength="5000" placeholder="Registra la decisión o solución aplicada..." data-pending-field="resolucion"><?php echo e(old('resolucion', $values['resolucion'] ?? '')); ?></textarea></label>
</div>
