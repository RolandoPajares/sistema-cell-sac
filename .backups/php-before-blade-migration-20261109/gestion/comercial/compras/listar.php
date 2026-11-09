<?php $__env->startSection('title', 'Gestión de compras'); ?>
<?php $__env->startSection('body_class', 'modulo-compras'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $statusClass = static fn (string $status): string => match ($status) {
            'Pendiente', 'En proceso' => 'pending',
            'Aprobada' => 'approved',
            'Recibida', 'Completada' => 'received',
            'Parcial' => 'partial',
            'Cancelada', 'Cancelado', 'Rechazada' => 'cancelled',
            default => 'muted',
        };
$receptionLabel = static fn (string $status): string => match ($status) {
    'Recibida', 'Completada' => 'Completa',
    'Parcial' => 'Parcial',
    'Pendiente', 'En proceso', 'Aprobada' => 'Sin recibir',
    default => $status,
};
?>

    <section class="compras-page">
        <header class="compras-hero">
            <div>
                <nav class="compras-breadcrumb" aria-label="Ruta de navegación">
                    <a href="<?php echo e(route('management.dashboard')); ?>">Administración</a>
                    <span aria-hidden="true">/</span>
                    <span>Operaciones comerciales</span>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">Compras</span>
                </nav>
                <h1>Gestión de compras</h1>
                <p>Controla las compras a proveedores y las recepciones que actualizan tu inventario.</p>
            </div>
            <div class="compras-hero-actions">
                <button class="compras-secondary-button" type="button" data-admin-dialog-open="importar-compras" <?php if (! $canImport) {
                    echo 'disabled';
                } ?> title="<?php echo e($canImport ? 'Importar compras con permiso de creación' : 'Requiere permiso de creación y una ubicación autorizada'); ?>"><i class="bi bi-upload" aria-hidden="true"></i> Importar</button>
                <?php if ($canExport) { ?>
                    <details class="compras-export-menu"><summary class="compras-secondary-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="compras-export-options"><a href="<?php echo e(route('management.purchases.export', array_merge(request()->query(), ['format' => 'excel']))); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i> Excel (.xlsx)</a><a href="<?php echo e(route('management.purchases.export', array_merge(request()->query(), ['format' => 'pdf']))); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i> PDF (.pdf)</a><a href="<?php echo e(route('management.purchases.export', array_merge(request()->query(), ['format' => 'csv']))); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i> CSV (.csv)</a></div></details>
                <?php } else { ?>
                    <button class="compras-secondary-button" type="button" disabled title="Requiere el permiso de exportación de compras"><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
                <?php } ?>
                <button class="compras-primary-button" type="button" data-admin-dialog-open="nueva-compra" <?php if (! $canCreate) {
                    echo 'disabled';
                } ?> title="<?php echo e($canCreate ? 'Registrar una compra' : ($canCreatePermission && $canCreateLocation ? 'Registra al menos un proveedor y un producto activo' : 'Requiere permiso de creación y una ubicación autorizada')); ?>">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva compra
                </button>
            </div>
        </header>

        <section class="compras-kpi-grid" aria-label="Resumen de compras">
            <article class="compras-kpi compras-kpi--blue">
                <span class="compras-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
                <div><small>Órdenes de compra</small><strong><?php echo e(number_format($totalPurchases)); ?></strong><span>Compras registradas</span></div>
            </article>
            <article class="compras-kpi compras-kpi--orange">
                <span class="compras-kpi-icon"><i class="bi bi-cart" aria-hidden="true"></i></span>
                <div><small>Compras pendientes</small><strong><?php echo e(number_format($pendingPurchases)); ?></strong><span>Por recibir</span></div>
            </article>
            <article class="compras-kpi compras-kpi--green">
                <span class="compras-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Compras recibidas</small><strong><?php echo e(number_format($receivedPurchases)); ?></strong><span>Recepciones completadas</span></div>
            </article>
            <article class="compras-kpi compras-kpi--purple">
                <span class="compras-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Monto comprado este mes</small><strong>S/ <?php echo e(number_format($monthlyPurchaseTotal, 2)); ?></strong><span><?php echo e(number_format($monthlyPurchaseCount)); ?> compras del mes</span></div>
            </article>
        </section>

        <form class="compras-filters" method="GET" action="<?php echo e(route('management.purchases')); ?>">
            <label class="compras-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar orden, proveedor o producto..." aria-label="Buscar orden, proveedor o producto">
            </label>
            <label>
                <select name="proveedor" aria-label="Filtrar por proveedor">
                    <option value="">Proveedor: todos</option>
                    <?php $__currentLoopData = $supplierFilters;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $supplier) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($supplier->id); ?>" <?php if ((string) ($filters['proveedor'] ?? '') === (string) $supplier->id) {
                            echo 'selected';
                        } ?>><?php echo e($supplier->nombre); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>
                <select name="estado" aria-label="Filtrar por estado">
                    <option value="">Estado: todos</option>
                    <?php $__currentLoopData = $statusSummary;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $status) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($status['label']); ?>" <?php if (($filters['estado'] ?? '') === $status['label']) {
                            echo 'selected';
                        } ?>><?php echo e($status['label']); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label class="compras-date-filter">
                <input type="date" name="desde" value="<?php echo e($filters['desde'] ?? ''); ?>" aria-label="Fecha desde">
            </label>
            <label class="compras-date-filter">
                <input type="date" name="hasta" value="<?php echo e($filters['hasta'] ?? ''); ?>" aria-label="Fecha hasta">
            </label>
            <button class="compras-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <a class="compras-clear-button" href="<?php echo e(route('management.purchases')); ?>">Limpiar</a>
        </form>

        <section class="compras-table-card" aria-labelledby="compras-list-title">
            <header class="compras-table-header">
                <div><h2 id="compras-list-title">Órdenes de compra</h2><span><?php echo e(number_format($purchases->total())); ?> registros</span></div>
            </header>
            <div class="compras-table-scroll">
                <table class="compras-table">
                    <thead>
                        <tr>
                            <th scope="col">Orden</th>
                            <th scope="col">Proveedor</th>
                            <th scope="col">Fecha</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Total</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Recepción</th>
                            <th scope="col"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true;
$__currentLoopData = $purchases;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $purchase) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                            <?php
            $status = (string) $purchase->estado;
    $estadoRecepcion = $purchase->estado === 'Aprobada' && (int) $purchase->cantidad_recibida_total > 0 ? 'Parcial' : $status;
    $supplierName = $purchase->supplier?->nombre ?? 'Proveedor no disponible';
    ?>
                            <tr>
                                <td><strong class="compras-code">OC-<?php echo e($purchase->fecha_compra?->format('y') ?? now()->format('y')); ?>-<?php echo e(str_pad((string) $purchase->id, 5, '0', STR_PAD_LEFT)); ?></strong></td>
                                <td><span class="compras-supplier"><?php echo e($supplierName); ?></span><small><?php echo e($purchase->supplier?->ruc ?? '—'); ?></small></td>
                                <td><?php echo e($purchase->fecha_compra?->format('d/m/Y') ?? '—'); ?></td>
                                <td><?php echo e($purchase->details_count); ?> <?php echo e($purchase->details_count === 1 ? 'producto' : 'productos'); ?></td>
                                <td><strong>S/ <?php echo e(number_format((float) $purchase->total, 2)); ?></strong></td>
                                <td><span class="compras-status compras-status--<?php echo e($statusClass($status)); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($status); ?></span></td>
                                <td><span class="compras-reception compras-reception--<?php echo e($statusClass($estadoRecepcion)); ?>"><i class="bi bi-box-seam" aria-hidden="true"></i><?php echo e($receptionLabel($estadoRecepcion)); ?></span></td>
                                <td>
                                    <details class="compras-row-details" id="detalle-compra-<?php echo e($purchase->id); ?>">
                                        <summary aria-label="Ver detalle de orden <?php echo e($purchase->id); ?>" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></summary>
                                        <div class="compras-detail-popover">
                                            <strong>Orden de compra</strong>
                                            <span><?php echo e($supplierName); ?> · <?php echo e($purchase->fecha_compra?->format('d/m/Y') ?? 'Fecha no disponible'); ?></span>
                                            <ul>
                                                <?php $__currentLoopData = $purchase->details;
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $detail) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?>
                                                    <li><span><?php echo e($detail->product?->nombre ?? 'Producto no disponible'); ?> × <?php echo e($detail->cantidad); ?><small>Recibidas: <?php echo e($detail->cantidad_recibida); ?> · Pendientes: <?php echo e($detail->cantidad_pendiente); ?></small></span><b>S/ <?php echo e(number_format((float) $detail->costo_unitario * (int) $detail->cantidad, 2)); ?></b></li>
                                                <?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?>
                                            </ul>
                                            <p>Total <b>S/ <?php echo e(number_format((float) $purchase->total, 2)); ?></b></p>
                                            <?php if ($purchase->estado === 'Aprobada' && in_array($purchase->id, $recibibles, true) && isset($tokensRecepcion[$purchase->id]) && $purchase->details->contains(fn ($detail) => $detail->cantidad_pendiente > 0)) { ?>
                                                <details class="compras-partial-reception" id="recepcion-parcial-<?php echo e($purchase->id); ?>">
                                                    <summary>Registrar recepción parcial</summary>
                                                    <form method="post" action="<?php echo e(route('management.purchases.receive_partial', $purchase)); ?>">
                                                        <?php echo csrf_field(); ?>
                                                        <input type="hidden" name="token_recepcion" value="<?php echo e($tokensRecepcion[$purchase->id]); ?>">
                                                        <?php $__currentLoopData = $purchase->details;
                                                $__env->addLoop($__currentLoopData);
                                                foreach ($__currentLoopData as $indice => $detail) {
                                                    $__env->incrementLoopIndices();
                                                    $loop = $__env->getLastLoop(); ?>
                                                            <?php if ($detail->cantidad_pendiente > 0) { ?>
                                                                <label>
                                                                    <?php echo e($detail->product?->nombre ?? 'Producto no disponible'); ?> · pendiente <?php echo e($detail->cantidad_pendiente); ?>

                                                                    <input type="hidden" name="items[<?php echo e($indice); ?>][detalle_compra_id]" value="<?php echo e($detail->id); ?>">
                                                                    <input type="number" name="items[<?php echo e($indice); ?>][cantidad]" min="0" max="<?php echo e($detail->cantidad_pendiente); ?>" value="0" required>
                                                                    <?php if ($detail->variante?->controla_serie) { ?>
                                                                        <textarea name="items[<?php echo e($indice); ?>][identificadores]" maxlength="30000" rows="3" placeholder="Un equipo por línea: IMEI:15dígitos, SERIE:valor o IMEI:15dígitos|SERIE:valor"></textarea>
                                                                    <?php } ?>
                                                                </label>
                                                            <?php } ?>
                                                        <?php } $__env->popLoop();
                                                $loop = $__env->getLastLoop(); ?>
                                                        <label>Observación de recepción <textarea name="observaciones" maxlength="500" rows="2" placeholder="Obligatoria si quedan unidades pendientes"></textarea></label>
                                                        <button class="compras-reception-open" type="submit">Guardar cantidades recibidas</button>
                                                    </form>
                                                </details>
                                            <?php } ?>
                                        </div>
                                    </details>
                                    <?php if ($purchase->estado === 'Pendiente' && in_array($purchase->id, $aprobables)) { ?>
                                        <form method="post" action="<?php echo e(route('management.purchases.approve', $purchase)); ?>"><?php echo csrf_field(); ?> <button class="compras-reception-open" type="submit">Aprobar</button></form>
                                        <?php if (in_array($purchase->id, $anulables, true)) { ?><form method="post" action="<?php echo e(route('management.purchases.cancel', $purchase)); ?>"><?php echo csrf_field(); ?> <button class="compras-reception-open" type="submit" data-confirm="¿Anular esta compra pendiente? Se conservará la auditoría.">Anular</button></form><?php } ?>
                                    <?php } elseif ($purchase->estado === 'Aprobada' && in_array($purchase->id, $recibibles, true) && $purchase->details->contains(fn ($detail) => $detail->cantidad_pendiente > 0 && $detail->variante?->controla_serie)) { ?>
                                        <button class="compras-reception-open" type="button" data-purchase-reception-open="<?php echo e($purchase->id); ?>">Recibir</button>
                                    <?php } elseif ($purchase->estado === 'Aprobada' && in_array($purchase->id, $recibibles, true)) { ?>
                                        <form method="post" action="<?php echo e(route('management.purchases.receive', $purchase)); ?>"><?php echo csrf_field(); ?> <button class="compras-reception-open" type="submit" data-confirm="¿Confirmas la recepción completa de esta compra?">Recibir</button></form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                            <tr><td class="compras-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay compras para mostrar</strong><span>Cambia los filtros o registra una nueva compra.</span></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <footer class="compras-table-footer">
                <span>
                    <?php if ($purchases->total() > 0) { ?>
                        Mostrando <?php echo e($purchases->firstItem()); ?>–<?php echo e($purchases->lastItem()); ?> de <?php echo e($purchases->total()); ?> compras
                    <?php } else { ?>
                        Sin registros
                    <?php } ?>
                </span>
                <?php echo e($purchases->links()); ?>

            </footer>
        </section>

        <div class="compras-bottom-grid">
            <section class="compras-summary-card">
                <header><span class="compras-summary-icon compras-summary-icon--orange"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><div><h2>Seguimiento de compras</h2><p>Órdenes que requieren recepción</p></div></header>
                <?php $__empty_1 = true;
$__currentLoopData = $pendingPurchaseRecords;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $pending) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <?php
            $pendingReference = 'OC-'.($pending->fecha_compra?->format('y') ?? now()->format('y')).'-'.str_pad((string) $pending->id, 5, '0', STR_PAD_LEFT);
    $pendingSupplierName = $pending->supplier?->nombre ?? 'Proveedor no disponible';
    ?>
                    <div class="compras-recent-row">
                        <span class="compras-recent-dot compras-recent-dot--orange"></span>
                        <div><strong><?php echo e($pendingReference); ?> · <?php echo e($pendingSupplierName); ?></strong><small><?php echo e($pending->details_count); ?> <?php echo e($pending->details_count === 1 ? 'producto' : 'productos'); ?> · S/ <?php echo e(number_format((float) $pending->total, 2)); ?></small></div>
                        <span class="compras-status compras-status--<?php echo e($statusClass((string) $pending->estado)); ?>"><?php echo e($pending->estado); ?></span>
                        <?php if ($pending->estado === 'Pendiente' && in_array($pending->id, $aprobables)) { ?>
                            <form method="post" action="<?php echo e(route('management.purchases.approve', $pending)); ?>"><?php echo csrf_field(); ?> <button class="compras-reception-open" type="submit">Aprobar</button></form>
                            <?php if (in_array($pending->id, $anulables, true)) { ?><form method="post" action="<?php echo e(route('management.purchases.cancel', $pending)); ?>"><?php echo csrf_field(); ?> <button class="compras-reception-open" type="submit" data-confirm="¿Anular esta compra pendiente? Se conservará la auditoría.">Anular</button></form><?php } ?>
                        <?php } elseif ($pending->estado === 'Aprobada' && in_array($pending->id, $recibibles)) { ?>
                            <form method="post" action="<?php echo e(route('management.purchases.receive', $pending)); ?>"><?php echo csrf_field(); ?> <button class="compras-reception-open" type="submit" data-confirm="¿Confirmas la recepción completa de esta compra?">Recibir mercadería</button></form>
                        <?php } ?>
                    </div>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <p class="compras-summary-empty">No hay órdenes pendientes de recepción.</p>
                <?php } ?>
            </section>

            <section class="compras-summary-card">
                <header><span class="compras-summary-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><h2>Productos más comprados</h2><p>Unidades registradas en recepciones</p></div></header>
                <?php $__empty_1 = true;
$__currentLoopData = $mostPurchasedProducts;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $product) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <div class="compras-product-row"><span class="compras-product-icon"><i class="bi bi-phone" aria-hidden="true"></i></span><strong><?php echo e($product->nombre); ?></strong><span><?php echo e(number_format((int) $product->cantidad)); ?> unid.</span></div>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <p class="compras-summary-empty">Los productos comprados aparecerán aquí al registrar una recepción.</p>
                <?php } ?>
            </section>

            <section class="compras-summary-card">
                <header><span class="compras-summary-icon compras-summary-icon--blue"><i class="bi bi-activity" aria-hidden="true"></i></span><div><h2>Actividad reciente</h2><p>Últimas órdenes registradas</p></div></header>
                <?php $__empty_1 = true;
$__currentLoopData = $recentPurchases;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $recent) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <div class="compras-recent-row">
                        <span class="compras-recent-dot"></span>
                        <div><strong>OC-<?php echo e($recent->fecha_compra?->format('y') ?? now()->format('y')); ?>-<?php echo e(str_pad((string) $recent->id, 5, '0', STR_PAD_LEFT)); ?> · <?php echo e($recent->supplier?->nombre ?? 'Proveedor no disponible'); ?></strong><small><?php echo e($recent->fecha_compra?->format('d/m/Y') ?? 'Fecha no disponible'); ?> · S/ <?php echo e(number_format((float) $recent->total, 2)); ?></small></div>
                        <span class="compras-status compras-status--<?php echo e($statusClass((string) $recent->estado)); ?>"><?php echo e($recent->estado); ?></span>
                    </div>
                <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <p class="compras-summary-empty">Las compras recientes aparecerán aquí.</p>
                <?php } ?>
            </section>
        </div>

        <dialog class="admin-dialog compras-create-dialog" id="nueva-compra" aria-labelledby="nueva-compra-title">
            <header class="admin-dialog-header">
                <div><h2 id="nueva-compra-title">Registrar compra</h2><p>La compra quedará pendiente. La aprobación y recepción son pasos separados.</p></div>
                <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-dialog-body">
                <?php if ($suppliers->isNotEmpty() && $products->isNotEmpty()) { ?>
                    <?php echo $__env->make('gestion.comercial.compras.crear', ['suppliers' => $suppliers, 'products' => $products], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php } else { ?>
                    <p class="compras-summary-empty">Para registrar una compra, agrega al menos un proveedor activo y un producto disponible.</p>
                <?php } ?>
            </div>
        </dialog>

        <dialog class="admin-dialog compras-create-dialog" id="importar-compras" aria-labelledby="importar-compras-title">
            <header class="admin-dialog-header"><div><h2 id="importar-compras-title">Importar compras</h2><p>Importa líneas reales con validación y transacción.</p></div><button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <div class="admin-dialog-body"><form method="post" action="<?php echo e(route('management.purchases.import')); ?>" enctype="multipart/form-data" class="form-card"><?php echo csrf_field(); ?><p>Columnas exactas: <code>proveedor_id,fecha_compra,producto_id,variante_id,cantidad,costo_unitario</code>.</p><label>Archivo CSV<input type="file" name="archivo" accept=".csv,text/csv" required></label><footer class="compras-form-actions"><button type="button" class="compras-secondary-button" data-admin-dialog-close>Cancelar</button><button type="submit" class="compras-primary-button"><i class="bi bi-upload" aria-hidden="true"></i> Importar</button></footer></form></div>
        </dialog>

    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>