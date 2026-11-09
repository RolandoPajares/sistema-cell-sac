<?php $__env->startSection('title', 'Gestión de ventas'); ?>
<?php $__env->startSection('body_class', 'modulo-ventas'); ?>

<?php $__env->startSection('content'); ?>
<?php
$formatCurrency = static fn (float|int|string|null $value): string => $value === null ? 'Monedas múltiples' : ($monedaReporte === 'PEN' ? 'S/ ' : $monedaReporte.' ').number_format((float) $value, 2);
$initialsFor = static function (?string $name): string {
$parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

return collect(array_slice($parts ?: ['CL'], 0, 2))
->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))
->implode('');
};
$imageUrl = static function (?string $path): ?string {
if (! $path) {
return null;
}

return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
};
$statusFor = static function (string $status): array {
return ['label' => $status ?: 'Sin estado', 'class' => 'muted'];
};
$chartMax = max(1, ...array_column($salesTrend, 'sales'));
$channelSegments = [];
$channelStart = 0;
foreach ($channelSummary as $channel) {
$channelEnd = $channelStart + $channel['percent'];
$channelSegments[] = $channel['color'].' '.$channelStart.'% '.$channelEnd.'%';
$channelStart = $channelEnd;
}
$channelDonut = 'background: conic-gradient('.implode(', ', $channelSegments ?: ['#d9e7f6 0% 100%']).');';
$queryWithoutPage = request()->except(['page']);
?>

<section class="ventas-page" data-sales-page>
    <header class="ventas-hero">
        <div>
            <nav class="ventas-breadcrumb" aria-label="Ruta de navegación">
                <a href="<?php echo e(route('management.dashboard')); ?>"><i class="bi bi-arrow-left" aria-hidden="true"></i> Administración</a>
                <span aria-hidden="true">/</span>
                <span>Ventas</span>
            </nav>
            <h1>Gestión de ventas <i class="bi bi-cart3" aria-hidden="true"></i></h1>
            <p>Administra y controla todas tus ventas, clientes y comprobantes en un solo lugar.</p>
        </div>
        <div class="ventas-hero-actions">
            <details class="ventas-export-menu"><summary class="ventas-secondary-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="ventas-export-options"><a href="<?php echo e(route('management.sales.export', ['format' => 'pdf'] + $queryWithoutPage)); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="<?php echo e(route('management.sales.export', ['format' => 'excel'] + $queryWithoutPage)); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="<?php echo e(route('management.sales.export', ['format' => 'csv'] + $queryWithoutPage)); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
            <button class="ventas-primary-button" type="button" <?php if(! $canCreate): echo 'disabled'; endif; ?> <?php if($canCreate): ?> data-open-sales-create <?php endif; ?> title="<?php echo e($canCreate ? 'Registrar venta física' : 'Registro no disponible: faltan permisos, almacén o la migración requerida.'); ?>"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva venta</button>
        </div>
    </header>

    <section class="ventas-kpi-grid" aria-label="Resumen de ventas">
        <article class="ventas-kpi ventas-kpi--blue">
            <span class="ventas-kpi-icon"><i class="bi bi-currency-dollar" aria-hidden="true"></i></span>
            <div><small>Importe registrado</small><strong><?php echo e($formatCurrency($totalSales)); ?></strong><span>Todos los estados; no implica cobro</span></div>
        </article>
        <article class="ventas-kpi ventas-kpi--green">
            <span class="ventas-kpi-icon"><i class="bi bi-cart-check" aria-hidden="true"></i></span>
            <div><small>Ventas registradas</small><strong><?php echo e(number_format($realizedSales)); ?></strong><span>Todos los estados</span></div>
        </article>
        <article class="ventas-kpi ventas-kpi--purple">
            <span class="ventas-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div><small>Clientes registrados</small><strong><?php echo e(number_format($customersServed)); ?></strong><span>Clientes asociados a ventas</span></div>
        </article>
        <article class="ventas-kpi ventas-kpi--orange">
            <span class="ventas-kpi-icon"><i class="bi bi-receipt" aria-hidden="true"></i></span>
            <div><small>Importe promedio</small><strong><?php echo e($formatCurrency($averageTicket)); ?></strong><span>Promedio de registros</span></div>
        </article>
    </section>

    <div class="ventas-main-grid">
        <section class="ventas-card ventas-chart-card">
            <header class="ventas-card-header">
                <div>
                    <h2><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ventas y tickets</h2>
                    <p>Comportamiento de ventas del periodo filtrado.</p>
                </div>
                <span class="ventas-period-label"><?php echo e(\Carbon\CarbonImmutable::parse($filters['desde'])->format('d/m/Y')); ?> - <?php echo e(\Carbon\CarbonImmutable::parse($filters['hasta'])->format('d/m/Y')); ?></span>
            </header>
            <?php if($monedasMultiples): ?>
            <p>La gráfica de importes no está disponible para ventas en monedas diferentes.</p>
            <?php else: ?>
            <div class="ventas-chart-legend"><span><i class="ventas-dot ventas-dot--blue" aria-hidden="true"></i> Importes registrados</span><span><i class="ventas-dot ventas-dot--green" aria-hidden="true"></i> N.º ventas</span><strong data-sales-chart-value><?php echo e($formatCurrency($totalSales)); ?></strong></div>
            <div class="ventas-chart" aria-label="Gráfico de ventas y número de ventas">
                <div class="ventas-chart-y-axis"><span><?php echo e($formatCurrency($chartMax)); ?></span><span><?php echo e($formatCurrency($chartMax / 2)); ?></span><span><?php echo e($formatCurrency(0)); ?></span></div>
                <div class="ventas-chart-plot">
                    <div class="ventas-chart-grid-lines" aria-hidden="true"><span></span><span></span><span></span></div>
                    <div class="ventas-chart-bars">
                        <?php $__currentLoopData = $salesTrend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $point): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="ventas-chart-point">
                            <div class="ventas-bar-group" title="<?php echo e($point['label']); ?>: <?php echo e($formatCurrency($point['sales'])); ?> · <?php echo e($point['orders']); ?> ventas">
                                <span class="ventas-bar ventas-bar--sales" style="height: <?php echo e($point['sales_height']); ?>%" data-sales-bar data-sales-value="<?php echo e($formatCurrency($point['sales'])); ?>"></span>
                                <span class="ventas-bar ventas-bar--orders" style="height: <?php echo e($point['orders_height']); ?>%"></span>
                            </div>
                            <small><?php echo e($point['label']); ?></small>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <section class="ventas-card ventas-channel-card">
            <header class="ventas-card-header">
                <div>
                    <h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Ventas por canal</h2>
                    <p>Distribución de las ventas registradas.</p>
                </div><span class="ventas-period-label">Periodo actual</span>
            </header>
            <div class="ventas-channel-content">
                <div class="ventas-donut" style="<?php echo e($channelDonut); ?>"><span><strong><?php echo e($formatCurrency($totalSales)); ?></strong><small>Total</small></span></div>
                <div class="ventas-channel-list">
                    <?php $__currentLoopData = $channelSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $channel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div><span><i class="ventas-dot" style="background: <?php echo e($channel['color']); ?>" aria-hidden="true"></i><?php echo e($channel['label']); ?></span><strong><?php echo e(number_format($channel['percent'], 1)); ?>%</strong><small><?php echo e(number_format($channel['count'])); ?></small></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>
    </div>

    <div class="ventas-layout-grid">
        <div class="ventas-content-column">
            <form class="ventas-filters" method="get" action="<?php echo e(route('management.sales')); ?>">
                <label class="ventas-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar venta</span><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar por cliente, número de venta, producto..."></label>
                <label><span class="visually-hidden">Desde</span><input type="date" name="desde" value="<?php echo e($filters['desde']); ?>" aria-label="Fecha desde"></label>
                <label><span class="visually-hidden">Hasta</span><input type="date" name="hasta" value="<?php echo e($filters['hasta']); ?>" aria-label="Fecha hasta"></label>
                <label><span class="visually-hidden">Cliente</span><select name="cliente_id" aria-label="Cliente">
                        <option value="">Cliente: Todos</option><?php $__currentLoopData = $clientesDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cliente): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($cliente->id); ?>" <?php if(($filters['cliente_id'] ?? null)===$cliente->id): echo 'selected'; endif; ?>><?php echo e($cliente->razon_social ?: $cliente->nombre_contacto ?: 'Cliente '.$cliente->id); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></label>
                <label><span class="visually-hidden">Sede</span><select name="sede_id" aria-label="Sede">
                        <option value="">Sede: Todas</option><?php $__currentLoopData = $sedesDisponibles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sede): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sede->id); ?>" <?php if(($filters['sede_id'] ?? null)===$sede->id): echo 'selected'; endif; ?>><?php echo e($sede->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></label>
                <label><span class="visually-hidden">Estado</span><select name="estado" aria-label="Estado">
                        <option value="">Estado: Todos</option><?php $__currentLoopData = $estados; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $estado): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($estado); ?>" <?php if(($filters['estado'] ?? null)===$estado): echo 'selected'; endif; ?>><?php echo e($estado); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select></label>
                <label><span class="visually-hidden">Canal</span><select name="canal" aria-label="Canal">
                        <option value="">Canal: Todos</option>
                        <option value="ecommerce" <?php if(($filters['canal'] ?? null)==='ecommerce' ): echo 'selected'; endif; ?>>Ecommerce</option>
                        <option value="presencial" <?php if(($filters['canal'] ?? null)==='presencial' ): echo 'selected'; endif; ?>>Presencial</option>
                    </select></label>
                <button class="ventas-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
                <button class="ventas-primary-button ventas-filter-submit" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
                <?php if(($filters['buscar'] ?? null) || ($filters['estado'] ?? null) || ($filters['canal'] ?? null) || ($filters['cliente_id'] ?? null) || ($filters['sede_id'] ?? null) || $filters['desde'] !== now()->subDays(29)->toDateString() || $filters['hasta'] !== now()->toDateString()): ?>
                <a class="ventas-clear-link" href="<?php echo e(route('management.sales')); ?>">Limpiar</a>
                <?php endif; ?>
            </form>

            <section class="ventas-card ventas-list-card">
                <header class="ventas-card-header">
                    <div>
                        <h2><i class="bi bi-receipt-cutoff" aria-hidden="true"></i> Listado de ventas</h2>
                        <p>Mostrando <?php echo e($orders->firstItem() ?? 0); ?> a <?php echo e($orders->lastItem() ?? 0); ?> de <?php echo e(number_format($orders->total())); ?> ventas</p>
                    </div><span class="ventas-total-badge"><?php echo e(number_format($ordersCount)); ?> ventas totales</span>
                </header>
                <div class="ventas-table-wrap">
                    <table class="ventas-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Cliente</th>
                                <th>Productos</th>
                                <th>Total</th>
                                <th>Canal</th>
                                <th>Estado</th>
                                <th>Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php ($status = $statusFor($order->estado)); ?>
                            <tr>
                                <td><strong class="ventas-order-number"><?php echo e($order->codigo); ?></strong></td>
                                <td class="ventas-date"><?php echo e($order->fecha_venta?->format('d/m/Y')); ?><small><?php echo e($order->fecha_venta?->format('H:i')); ?></small></td>
                                <td><span class="ventas-customer"><b><?php echo e($initialsFor($order->cliente?->razon_social ?: $order->cliente?->nombre_contacto)); ?></b><span><strong><?php echo e($order->cliente?->razon_social ?: $order->cliente?->nombre_contacto ?: 'Sin cliente asociado'); ?></strong><small><?php echo e($order->cliente?->correo ?: 'Sin correo'); ?></small></span></span></td>
                                <td><?php echo e(number_format((int) $order->detalles_count)); ?> productos</td>
                                <td><strong class="ventas-total"><?php echo e($order->moneda); ?> <?php echo e(number_format((float) $order->total, 2)); ?></strong></td>
                                <td><span class="ventas-channel"><i class="bi bi-shop" aria-hidden="true"></i> <?php echo e($order->canal ?: $order->pedido?->canal ?: 'Sin canal registrado'); ?></span></td>
                                <td><span class="ventas-status ventas-status--<?php echo e($status['class']); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($status['label']); ?></span></td>
                                <td><span class="ventas-receipt">No consultado</span></td>
                                <td><span class="ventas-row-actions"><button class="ventas-action-button" type="button" data-sales-details="<?php echo e($order->id); ?>" title="Ver detalle" aria-label="Ver detalle de venta <?php echo e($order->codigo); ?>"><i class="bi bi-eye" aria-hidden="true"></i></button><?php if(in_array($order->id, $editables, true)): ?><a class="ventas-action-button" href="<?php echo e(route('management.sales', ['editar' => $order->id])); ?>" title="Editar borrador" aria-label="Editar venta <?php echo e($order->codigo); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></a><?php else: ?><button class="ventas-action-button" type="button" disabled title="Solo se editan borradores autorizados sin movimientos asociados" aria-label="Editar venta no disponible"><i class="bi bi-pencil" aria-hidden="true"></i></button><?php endif; ?>
                                        <?php if(in_array($order->id, $confirmables, true) && $order->estado === 'borrador' && $order->pedido_id !== null): ?>
                                        <button class="ventas-action-button" type="button" data-open-sales-confirm="confirmar-venta-<?php echo e($order->id); ?>" title="Confirmar venta y descontar existencias" aria-label="Confirmar venta <?php echo e($order->codigo); ?>"><i class="bi bi-check-circle" aria-hidden="true"></i></button>
                                        <?php endif; ?>
                                        <?php if(in_array($order->id, $cancelables) && $order->estado === 'confirmada' && $order->canal === 'presencial' && $order->pedido_id === null): ?>
                                        <form method="post" action="<?php echo e(route('management.sales.cancel', $order)); ?>" data-sales-cancel>
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="motivo">
                                            <button class="ventas-action-button" type="submit" title="Anular venta" aria-label="Anular venta <?php echo e($order->codigo); ?>"><i class="bi bi-x-circle" aria-hidden="true"></i></button>
                                        </form>
                                        <?php endif; ?>
                                    </span></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="9" class="ventas-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay ventas para los filtros seleccionados.</strong><span>Prueba con otro periodo o estado.</span></td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if(in_array($order->id, $confirmables, true) && $order->estado === 'borrador' && $order->pedido_id !== null): ?>
                <dialog class="ventas-dialog" id="confirmar-venta-<?php echo e($order->id); ?>" aria-labelledby="confirmar-venta-titulo-<?php echo e($order->id); ?>">
                    <div class="ventas-dialog-card">
                        <header class="ventas-dialog-header"><div><h2 id="confirmar-venta-titulo-<?php echo e($order->id); ?>">Confirmar venta <?php echo e($order->codigo); ?></h2><p>Se validarán las existencias y los identificadores antes de descontar inventario.</p></div><button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                        <form class="ventas-modal-form" method="post" action="<?php echo e(route('management.sales.confirm', $order)); ?>" data-sales-confirm>
                            <?php echo csrf_field(); ?>
                            <section class="ventas-modal-section">
                                <div class="ventas-modal-section-header"><i class="bi bi-phone" aria-hidden="true"></i><h3>IMEI y números de serie</h3></div>
                            <?php $__currentLoopData = $order->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php ($configuracionDispositivos = $dispositivosPorDetalle->get($detalle->getKey(), ['requiere' => false, 'dispositivos' => collect()])); ?>
                                <?php if($configuracionDispositivos['requiere']): ?>
                                <label class="ventas-modal-field"><span><?php echo e($detalle->descripcion_historica); ?> — <?php echo e(number_format((float) $detalle->cantidad, 0)); ?> unidad(es) serializadas</span>
                                    <select name="dispositivos[<?php echo e($detalle->getKey()); ?>][]" multiple required size="<?php echo e(min(5, max(2, (int) $detalle->cantidad))); ?>">
                                        <?php $__currentLoopData = $configuracionDispositivos['dispositivos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dispositivo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($dispositivo->id); ?>"><?php echo e($dispositivo->imei ?: $dispositivo->numero_serie ?: 'Dispositivo '.$dispositivo->id); ?></option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </label>
                                <?php endif; ?>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if($order->detalles->contains(fn ($detalle) => ($dispositivosPorDetalle->get($detalle->getKey())['requiere'] ?? false) && $dispositivosPorDetalle->get($detalle->getKey())['dispositivos']->count() < (int) $detalle->cantidad)): ?>
                                    <p role="status">No hay suficientes IMEI o series disponibles en el almacén asociado. Registra o recibe las unidades antes de confirmar.</p>
                                <?php endif; ?>
                            </section>
                            <footer class="ventas-modal-footer"><button class="ventas-modal-cancel" type="button" data-close-sales-dialog>Cancelar</button><span></span><button class="ventas-primary-button" type="submit" <?php if($order->detalles->contains(fn ($detalle) => ($dispositivosPorDetalle->get($detalle->getKey())['requiere'] ?? false) && $dispositivosPorDetalle->get($detalle->getKey())['dispositivos']->count() < (int) $detalle->cantidad)): echo 'disabled'; endif; ?>>Confirmar y descontar existencias</button></footer>
                        </form>
                    </div>
                </dialog>
                <?php endif; ?>
                <dialog class="ventas-dialog" id="detalle-venta-<?php echo e($order->id); ?>" aria-labelledby="detalle-venta-titulo-<?php echo e($order->id); ?>">
                    <div class="ventas-dialog-card">
                        <header class="ventas-dialog-header">
                            <div>
                                <h2 id="detalle-venta-titulo-<?php echo e($order->id); ?>">Venta <?php echo e($order->codigo); ?></h2>
                                <p><?php echo e($order->fecha_venta?->format('d/m/Y H:i')); ?> · <?php echo e($order->estado); ?></p>
                            </div><button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                        </header>
                        <div class="ventas-modal-table-wrap">
                            <table class="ventas-modal-table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <th>SKU</th>
                                        <th>Cantidad</th>
                                        <th>Precio</th>
                                        <th>Importe</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $__empty_1 = true; $__currentLoopData = $order->detalles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detalle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($detalle->descripcion_historica); ?></td>
                                        <td><?php echo e($detalle->sku_historico ?: '—'); ?></td>
                                        <td><?php echo e($detalle->cantidad); ?></td>
                                        <td><?php echo e($order->moneda); ?> <?php echo e(number_format((float) $detalle->precio_unitario, 2)); ?></td>
                                        <td><?php echo e($order->moneda); ?> <?php echo e(number_format((float) $detalle->total_linea, 2)); ?></td>
                                    </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5">La venta no tiene detalles registrados.</td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php if($order->observaciones): ?><p><?php echo e($order->observaciones); ?></p><?php endif; ?>
                        <p>Total: <?php echo e($order->moneda); ?> <?php echo e(number_format((float) $order->total, 2)); ?></p>
                    </div>
                </dialog>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <footer class="ventas-pagination"><span>Mostrando <?php echo e($orders->firstItem() ?? 0); ?>–<?php echo e($orders->lastItem() ?? 0); ?> de <?php echo e(number_format($orders->total())); ?> ventas</span>
                    <div><?php echo e($orders->onEachSide(1)->links()); ?></div>
                </footer>
            </section>
        </div>

        <aside class="ventas-side-column">
            <section class="ventas-card ventas-quick-card">
                <header class="ventas-card-header">
                    <h2>Acciones rápidas</h2>
                </header>
                <div class="ventas-quick-actions">
                    <button type="button" <?php if(! $canCreate): echo 'disabled'; endif; ?> <?php if($canCreate): ?> data-open-sales-create <?php endif; ?>><i class="bi bi-plus-lg" aria-hidden="true"></i><span>Nueva venta</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <button type="button" <?php if(! $canCreate): echo 'disabled'; endif; ?> <?php if($canCreate): ?> data-open-sales-create <?php endif; ?>><i class="bi bi-shop" aria-hidden="true"></i><span>Registrar venta física</span><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <a href="<?php echo e(route('quotations.index')); ?>"><i class="bi bi-file-earmark-text" aria-hidden="true"></i><span>Crear cotización</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <a href="<?php echo e(route('management.orders', ['estado' => 'pendiente'])); ?>"><i class="bi bi-cart-check" aria-hidden="true"></i><span>Ver pedidos pendientes</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <a href="<?php echo e(route('management.customers')); ?>"><i class="bi bi-people" aria-hidden="true"></i><span>Gestionar clientes</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    <a href="<?php echo e(route('management.reports')); ?>"><i class="bi bi-bar-chart" aria-hidden="true"></i><span>Ver reportes de ventas</span><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                </div>
            </section>

            <section class="ventas-card ventas-side-list-card">
                <header class="ventas-card-header">
                    <h2>Últimas ventas</h2><a href="<?php echo e(route('management.sales')); ?>">Ver todas</a>
                </header>
                <div class="ventas-recent-list">
                    <?php $__empty_1 = true; $__currentLoopData = $latestSales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="ventas-recent-row"><span class="ventas-recent-avatar"><i class="bi bi-person" aria-hidden="true"></i></span><span><strong><?php echo e($sale->cliente?->razon_social ?: $sale->cliente?->nombre_contacto ?: 'Sin cliente asociado'); ?></strong><small><i class="bi bi-circle-fill" aria-hidden="true"></i> <?php echo e($sale->fecha_venta?->format('d/m/Y H:i')); ?></small></span><b><?php echo e($sale->moneda); ?> <?php echo e(number_format((float) $sale->total, 2)); ?></b></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="ventas-side-empty">Aún no hay ventas registradas.</p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="ventas-card ventas-side-list-card">
                <header class="ventas-card-header">
                    <h2>Productos más vendidos</h2><a href="<?php echo e(route('management.products')); ?>">Ver todos</a>
                </header>
                <div class="ventas-product-list">
                    <?php $__empty_1 = true; $__currentLoopData = $monedasMultiples ? [] : $topProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="ventas-product-row"><span class="ventas-product-rank"><?php echo e($index + 1); ?></span><span class="ventas-product-image"><?php if($imageUrl($product['image'])): ?><img src="<?php echo e($imageUrl($product['image'])); ?>" alt=""><?php else: ?><i class="bi bi-box-seam" aria-hidden="true"></i><?php endif; ?></span><span><strong><?php echo e($product['name']); ?></strong><small><?php echo e(rtrim(rtrim(number_format($product['units'], 6, '.', ''), '0'), '.')); ?> unidades</small></span><b><?php echo e($formatCurrency($product['revenue'])); ?></b></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="ventas-side-empty"><?php echo e($monedasMultiples ? 'No se suman importes de monedas diferentes.' : 'Aún no hay productos registrados en ventas.'); ?></p>
                    <?php endif; ?>
                </div>
            </section>
        </aside>
    </div>

    <dialog class="ventas-dialog" id="nueva-venta" data-sales-auto-open="<?php echo e($errors->any() && old('_method') !== 'PUT' ? '1' : '0'); ?>" aria-labelledby="nueva-venta-titulo">
        <div class="ventas-dialog-card">
            <header class="ventas-dialog-header">
                <div><span class="ventas-dialog-icon"><i class="bi bi-cart-plus" aria-hidden="true"></i></span><span><small>Ventas</small>
                        <h2 id="nueva-venta-titulo">Nueva venta</h2>
                        <p>Confirma la venta y descuenta existencias. El pago y comprobante se registran por separado.</p>
                    </span></div>
                <button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <?php echo $__env->make('gestion.comercial.ventas.crear', ['customers' => $customers, 'products' => $products], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </dialog>

    <?php if($editOrder): ?>
    <dialog class="ventas-dialog" id="editar-venta-<?php echo e($editOrder->id); ?>" data-sales-auto-open="1" aria-labelledby="editar-venta-titulo-<?php echo e($editOrder->id); ?>">
        <div class="ventas-dialog-card">
            <header class="ventas-dialog-header">
                <div><span class="ventas-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><span><small>Ventas</small>
                        <h2 id="editar-venta-titulo-<?php echo e($editOrder->id); ?>">Editar venta #<?php echo e(str_pad((string) $editOrder->id, 6, '0', STR_PAD_LEFT)); ?></h2>
                        <p>Actualiza el cliente, la fecha o los productos de esta venta.</p>
                    </span></div>
                <button class="ventas-dialog-close" type="button" data-close-sales-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <?php echo $__env->make('gestion.comercial.ventas.editar', ['order' => $editOrder, 'customers' => $customers, 'products' => $products], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </dialog>
    <?php endif; ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>