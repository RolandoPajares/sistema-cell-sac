<?php $__env->startSection('title', 'Transferencias entre almacenes'); ?>
<?php $__env->startSection('body_class', 'modulo-inventario-logistica'); ?>

<?php $__env->startSection('content'); ?>
<section class="tc-il" data-il-transfer-manager>
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Inventario y Logística <i class="bi bi-chevron-right" aria-hidden="true"></i> Transferencias</span>
            <h1><i class="bi bi-arrow-left-right" aria-hidden="true"></i>Transferencias entre sedes</h1>
            <p>Solicita, aprueba, despacha y recibe existencias entre almacenes de la empresa.</p>
        </div>
        <div class="tc-il__header-actions">
            <details class="productos-export-menu tc-il__export-menu">
                <summary><i class="bi bi-download" aria-hidden="true"></i><span>Exportar</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                <div class="productos-export-options">
                    <a href="<?php echo e(route('management.inventory.transfers_export', ['format' => 'pdf'] + request()->query())); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span>Exportar PDF<small>Documento imprimible</small></span></a>
                    <a href="<?php echo e(route('management.inventory.transfers_export', ['format' => 'excel'] + request()->query())); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span>Exportar Excel<small>Archivo compatible .xlsx</small></span></a>
                    <a href="<?php echo e(route('management.inventory.transfers_export', ['format' => 'csv'] + request()->query())); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span>Exportar CSV<small>Datos delimitados</small></span></a>
                </div>
            </details>
            <button type="button" class="tc-il__button tc-il__button--primary" data-transfer-open><i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva solicitud</button>
        </div>
    </header>

    <?php if(session('status')): ?>
        <div class="tc-il__notice" role="status"><i class="bi bi-check-circle" aria-hidden="true"></i><?php echo e(session('status')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="tc-il__notice" role="alert"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><span><?php echo e($errors->first()); ?></span></div>
    <?php endif; ?>
    <?php if (! ($supports_difference_reconciliation)): ?>
        <div class="tc-il__notice" role="status"><i class="bi bi-info-circle" aria-hidden="true"></i><strong>Conciliación pendiente</strong><span>El esquema aún no registra cantidades devueltas; la migración independiente sigue sin ejecutarse.</span></div>
    <?php endif; ?>

    <div class="tc-il__metrics">
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-clock" aria-hidden="true"></i></span><div><small>Solicitadas</small><strong><?php echo e($metrics['solicitadas']); ?></strong><span>Esperan aprobación</span></div></article>
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-truck" aria-hidden="true"></i></span><div><small>En tránsito</small><strong><?php echo e($metrics['en_transito']); ?></strong><span>Despachadas sin recepción</span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Recepción parcial</small><strong><?php echo e($metrics['recepcion_parcial']); ?></strong><span>Requieren completar o conciliar</span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span><div><small>Recibidas, por cerrar</small><strong><?php echo e($metrics['pendientes_cierre']); ?></strong><span>Listas para cierre</span></div></article>
    </div>

    <form method="GET" action="<?php echo e(route('management.inventory.preview.transfers')); ?>" class="tc-il__filters">
        <label class="tc-il__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar por código o producto…" aria-label="Buscar transferencias"></label>
        <div class="tc-il__filter-fields">
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><?php $__currentLoopData = ['solicitada' => 'Solicitada', 'aprobada' => 'Aprobada', 'en tránsito' => 'En tránsito', 'recibida parcialmente' => 'Recibida parcialmente', 'recibida totalmente' => 'Recibida totalmente', 'cerrada' => 'Cerrada', 'rechazada' => 'Rechazada', 'cancelada' => 'Cancelada']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($value); ?>" <?php if(($filters['estado'] ?? '') === $value): echo 'selected'; endif; ?>><?php echo e($label); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <button type="submit" class="tc-il__button tc-il__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button>
            <a class="tc-il__button" href="<?php echo e(route('management.inventory.preview.transfers')); ?>">Limpiar</a>
        </div>
    </form>

    <article class="tc-il__card tc-il__table-card">
        <div class="tc-il__card-heading"><h2><i class="bi bi-layers" aria-hidden="true"></i>Transferencias registradas</h2><small><?php echo e($transfers->total()); ?> registros</small></div>
        <div class="tc-il__table-wrap">
            <table>
                <thead><tr><th>Código</th><th>Origen</th><th>Destino</th><th>Solicitó</th><th>Fecha</th><th>Unidades solicitadas</th><th>Estado</th><th>Operaciones</th></tr></thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $transfers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transfer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $tone = match ($transfer->estado) {
                                'solicitada', 'recibida parcialmente' => 'ambar',
                                'recibida totalmente', 'cerrada' => 'verde',
                                'rechazada', 'cancelada' => 'rojo',
                                default => 'azul',
                            };
                            $lineCount = $transfer->detalles->sum(fn ($line) => (int) $line->cantidad_solicitada);
                        ?>
                        <tr>
                            <td><?php echo e($transfer->codigo); ?></td>
                            <td><?php echo e($transfer->sedeOrigen?->nombre); ?> · <?php echo e($transfer->almacenOrigen?->nombre); ?></td>
                            <td><?php echo e($transfer->sedeDestino?->nombre); ?> · <?php echo e($transfer->almacenDestino?->nombre); ?></td>
                            <td><?php echo e($transfer->solicitante?->nombre ?? '—'); ?></td>
                            <td><?php echo e($transfer->creado_en?->format('d/m/Y H:i')); ?></td>
                            <td><?php echo e($lineCount); ?></td>
                            <td><span class="tc-il__pill tc-il__pill--<?php echo e($tone); ?>"><?php echo e(ucfirst($transfer->estado)); ?></span></td>
                            <td>
                                <details class="tc-il__transfer-detail">
                                    <summary class="tc-il__button">Detalle / acciones</summary>
                                    <div class="tc-il__transfer-panel">
                                        <h3>Productos y cantidades</h3>
                                        <div class="tc-il__table-wrap"><table><thead><tr><th>Producto</th><th>Variante</th><th>Solicitada</th><th>Enviada</th><th>Recibida</th><th>Devuelta</th><th>Conciliada</th></tr></thead><tbody>
                                            <?php $__currentLoopData = $transfer->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <tr><td><?php echo e($detail->producto?->nombre); ?></td><td><?php echo e($detail->variante?->nombre_color ?? '—'); ?></td><td><?php echo e((int) $detail->cantidad_solicitada); ?></td><td><?php echo e((int) $detail->cantidad_enviada); ?></td><td><?php echo e((int) $detail->cantidad_recibida); ?></td><td><?php echo e($supports_return_reconciliation ? (int) $detail->cantidad_devuelta : '—'); ?></td><td><?php echo e($supports_difference_reconciliation ? (int) $detail->cantidad_conciliada : '—'); ?></td></tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </tbody></table></div>

                                        <?php if(in_array($transfer->estado, ['solicitada', 'aprobada'], true)): ?>
                                            <div class="tc-il__transfer-actions">
                                                <?php if($transfer->estado === 'solicitada'): ?>
                                                    <form method="POST" action="<?php echo e(route('management.inventory.transfer_approve', $transfer)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><button class="tc-il__button tc-il__button--primary" type="submit">Aprobar</button></form>
                                                <?php endif; ?>
                                                <?php $__currentLoopData = [['reject', 'Rechazar'], ['cancel', 'Cancelar']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$action, $label]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <form method="POST" action="<?php echo e(route('management.inventory.transfer_'.$action, $transfer)); ?>"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><input type="text" name="motivo" maxlength="500" required placeholder="Motivo"><button class="tc-il__button" type="submit"><?php echo e($label); ?></button></form>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if(in_array($transfer->estado, ['aprobada', 'en tránsito', 'recibida parcialmente'], true)): ?>
                                            <div class="tc-il__transfer-actions">
                                                <?php $__currentLoopData = $transfer->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <?php
                                                        $pendingDispatch = (int) $detail->cantidad_solicitada - (int) $detail->cantidad_enviada;
                                                        $returned = $supports_return_reconciliation ? (int) $detail->cantidad_devuelta : 0;
                                                        $reconciled = $supports_difference_reconciliation ? (int) $detail->cantidad_conciliada : 0;
                                                        $pendingReceipt = (int) $detail->cantidad_enviada - (int) $detail->cantidad_recibida - $returned - $reconciled;
                                                    ?>
                                                    <?php if($pendingDispatch > 0): ?>
                                                        <form method="POST" action="<?php echo e(route('management.inventory.transfer_dispatch', $transfer)); ?>" class="tc-il__transfer-line-form"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><input type="hidden" name="items[0][detalle_id]" value="<?php echo e($detail->id); ?>"><strong>Despachar · <?php echo e($detail->producto?->nombre); ?></strong><label>Cantidad<input type="number" name="items[0][cantidad]" min="1" max="<?php echo e($pendingDispatch); ?>" required></label><label>IMEI / serie, uno por línea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><button class="tc-il__button tc-il__button--primary" type="submit">Registrar despacho</button></form>
                                                    <?php endif; ?>
                                                    <?php if($pendingReceipt > 0 && $supports_difference_reconciliation): ?>
                                                        <form method="POST" action="<?php echo e(route('management.inventory.transfer_reconcile', $transfer)); ?>" class="tc-il__transfer-line-form"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><input type="hidden" name="items[0][detalle_id]" value="<?php echo e($detail->id); ?>"><strong>Conciliar faltante: <?php echo e($detail->producto?->nombre); ?></strong><label>Cantidad con diferencia<input type="number" name="items[0][cantidad]" min="1" max="<?php echo e($pendingReceipt); ?>" required></label><label>IMEI / serie de la diferencia, uno por linea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><label>Motivo documentado<input type="text" name="motivo" minlength="5" maxlength="500" required></label><button class="tc-il__button" type="submit">Registrar conciliacion</button></form>
                                                    <?php endif; ?>
                                                    <?php if($pendingReceipt > 0): ?>
                                                        <form method="POST" action="<?php echo e(route('management.inventory.transfer_receive', $transfer)); ?>" class="tc-il__transfer-line-form"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><input type="hidden" name="items[0][detalle_id]" value="<?php echo e($detail->id); ?>"><strong>Recibir · <?php echo e($detail->producto?->nombre); ?></strong><label>Cantidad recibida<input type="number" name="items[0][cantidad]" min="1" max="<?php echo e($pendingReceipt); ?>" required></label><label>IMEI / serie recibida, uno por línea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><button class="tc-il__button tc-il__button--primary" type="submit">Registrar recepción</button></form>
                                                        <?php if($supports_return_reconciliation): ?>
                                                            <form method="POST" action="<?php echo e(route('management.inventory.transfer_return', $transfer)); ?>" class="tc-il__transfer-line-form"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><input type="hidden" name="items[0][detalle_id]" value="<?php echo e($detail->id); ?>"><strong>Devolver al origen · <?php echo e($detail->producto?->nombre); ?></strong><label>Cantidad devuelta<input type="number" name="items[0][cantidad]" min="1" max="<?php echo e($pendingReceipt); ?>" required></label><label>IMEI / serie devuelta, uno por línea si aplica<textarea name="items[0][series]" rows="2" maxlength="4000"></textarea></label><label>Motivo documentado<input type="text" name="motivo" maxlength="500" required></label><button class="tc-il__button" type="submit">Registrar devolución</button></form>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php endif; ?>

                                        <?php if(in_array($transfer->estado, ['en tránsito', 'recibida totalmente', 'recibida parcialmente'], true)): ?>
                                            <form method="POST" action="<?php echo e(route('management.inventory.transfer_close', $transfer)); ?>" class="tc-il__transfer-actions"><?php echo csrf_field(); ?><input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>"><label>Nota de cierre si hay diferencias<input type="text" name="motivo" minlength="5" maxlength="500"></label><button class="tc-il__button tc-il__button--primary" type="submit">Cerrar transferencia</button></form>
                                        <?php endif; ?>

                                        <h3>Movimientos registrados</h3>
                                        <div class="tc-il__table-wrap"><table><thead><tr><th>Movimiento</th><th>Producto</th><th>Cantidad</th><th>Almacén</th><th>Usuario</th><th>Fecha</th></tr></thead><tbody>
                                            <?php $__empty_2 = true; $__currentLoopData = $movements->get((string) $transfer->id, collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                <tr><td><?php echo e(str_replace('transferencia_', '', $movement->documento_tipo)); ?></td><td><?php echo e($movement->product?->nombre); ?></td><td><?php echo e(abs((float) $movement->cantidad)); ?></td><td><?php echo e($movement->almacen?->nombre ?? $movement->almacen_id); ?></td><td><?php echo e($movement->user?->nombre ?? '—'); ?></td><td><?php echo e($movement->creado_en?->format('d/m/Y H:i:s')); ?></td></tr>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                <tr><td colspan="6">Aún no hay movimientos de inventario para esta transferencia.</td></tr>
                                            <?php endif; ?>
                                        </tbody></table></div>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="8">No hay transferencias que coincidan con los filtros.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="tc-il__pagination"><?php echo e($transfers->links()); ?></div>
    </article>

    <dialog class="tc-il__dialog" data-transfer-dialog aria-labelledby="transfer-create-title">
        <form method="POST" action="<?php echo e(route('management.inventory.transfer_request')); ?>" class="tc-il__dialog-card" data-transfer-create-form>
            <?php echo csrf_field(); ?>
            <input type="hidden" name="clave_idempotencia" value="<?php echo e(\Illuminate\Support\Str::uuid()); ?>">
            <header class="tc-il__dialog-header"><div><span class="tc-il__dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2 id="transfer-create-title">Nueva solicitud</h2><p>La solicitud no reserva ni descuenta existencias.</p></div></div><button type="button" class="tc-il__dialog-close" data-transfer-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <div class="tc-il__dialog-body">
                <div class="tc-il__field-grid">
                    <label class="tc-il__field">Almacén de origen *<select name="almacen_origen_id" required><option value="">Selecciona</option><?php $__currentLoopData = $source_warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($warehouse->id); ?>"><?php echo e($warehouse->sede_nombre); ?> · <?php echo e($warehouse->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                    <label class="tc-il__field">Almacén de destino *<select name="almacen_destino_id" required><option value="">Selecciona</option><?php $__currentLoopData = $warehouses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warehouse): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($warehouse->id); ?>"><?php echo e($warehouse->sede_nombre); ?> · <?php echo e($warehouse->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                </div>
                <div class="tc-il__transfer-line-list" data-transfer-lines>
                    <div class="tc-il__transfer-line-form" data-transfer-line>
                        <label class="tc-il__field">Producto / variante *<select data-transfer-product required><option value="">Selecciona un producto</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->producto_id); ?>:<?php echo e($product->variante_id ?? 'null'); ?>"><?php echo e($product->marca); ?> <?php echo e($product->nombre); ?><?php echo e($product->nombre_color ? ' · '.$product->nombre_color : ''); ?><?php echo e($product->codigo_sku ? ' · '.$product->codigo_sku : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                        <label class="tc-il__field">Cantidad *<input type="number" data-transfer-quantity min="1" max="1000000" step="1" value="1" required></label>
                        <input type="hidden" data-transfer-product-id><input type="hidden" data-transfer-variant-id>
                    </div>
                </div>
                <template data-transfer-line-template><div class="tc-il__transfer-line-form" data-transfer-line><label class="tc-il__field">Producto / variante *<select data-transfer-product required><option value="">Selecciona un producto</option><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->producto_id); ?>:<?php echo e($product->variante_id ?? 'null'); ?>"><?php echo e($product->marca); ?> <?php echo e($product->nombre); ?><?php echo e($product->nombre_color ? ' · '.$product->nombre_color : ''); ?><?php echo e($product->codigo_sku ? ' · '.$product->codigo_sku : ''); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><label class="tc-il__field">Cantidad *<input type="number" data-transfer-quantity min="1" max="1000000" step="1" value="1" required></label><input type="hidden" data-transfer-product-id><input type="hidden" data-transfer-variant-id><button type="button" class="tc-il__remove-row" data-transfer-remove aria-label="Quitar producto"><i class="bi bi-trash" aria-hidden="true"></i></button></div></template>
                <button type="button" class="tc-il__button" data-transfer-add><i class="bi bi-plus-lg" aria-hidden="true"></i>Agregar producto</button>
                <label class="tc-il__field">Observaciones<textarea name="observaciones" rows="3" maxlength="500"></textarea></label>
            </div>
            <footer class="tc-il__dialog-footer"><button type="button" class="tc-il__button" data-transfer-close>Cancelar</button><button type="submit" class="tc-il__button tc-il__button--primary">Registrar solicitud</button></footer>
        </form>
    </dialog>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>