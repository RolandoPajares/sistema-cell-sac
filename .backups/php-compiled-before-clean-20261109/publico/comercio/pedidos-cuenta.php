<?php
    $isAdminPanel = (bool) ($adminView ?? false);
    $isAdmin = ($adminView ?? false)
        || auth()->user()?->can('gestion.seguridad.consultar')
        || auth()->user()?->can('gestion.ventas.consultar')
        || auth()->user()?->can('gestion.cotizaciones.consultar');
?>


<?php $__env->startSection('title', 'Pedidos'); ?>
<?php $__env->startSection('body_class', 'modulo-pedidos'); ?>

<?php $__env->startSection('content'); ?>
    <?php if($isAdminPanel || ($isAdmin && auth()->user()->can('gestion.seguridad.consultar'))): ?>
        <?php
            $productImage = static function (?string $path): ?string {
                if (!$path) {
                    return null;
                }

                return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
            };
            $statusFor = static function (string $status): array {
                return match ($status) {
                    'Pendiente' => ['label' => 'Pendiente', 'class' => 'pending'],
                    'En proceso' => ['label' => 'En preparación', 'class' => 'preparing'],
                    'Enviado' => ['label' => 'Enviado', 'class' => 'sent'],
                    'Entregado' => ['label' => 'Entregado', 'class' => 'delivered'],
                    'Cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
                    default => ['label' => $status, 'class' => 'muted'],
                };
            };
            $shippingFor = static function (string $status): array {
                return match ($status) {
                    'Pendiente' => ['label' => 'Por preparar', 'class' => 'muted'],
                    'En proceso' => ['label' => 'En preparación', 'class' => 'sent'],
                    'Enviado' => ['label' => 'En tránsito', 'class' => 'sent'],
                    'Entregado' => ['label' => 'Entregado', 'class' => 'delivered'],
                    'Cancelado' => ['label' => 'Cancelado', 'class' => 'cancelled'],
                    default => ['label' => 'Sin información', 'class' => 'muted'],
                };
            };
            $initialsFor = static function (?string $name): string {
                $parts = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

                return collect(array_slice($parts ?: ['CL'], 0, 2))->map(static fn (string $part): string => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
            };
            $months = [1 => 'ene.', 2 => 'feb.', 3 => 'mar.', 4 => 'abr.', 5 => 'may.', 6 => 'jun.', 7 => 'jul.', 8 => 'ago.', 9 => 'sep.', 10 => 'oct.', 11 => 'nov.', 12 => 'dic.'];
            $orderDate = static fn ($date): string => $date ? $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y') : 'Sin fecha';
            $palette = ['yellow' => '#f5a900', 'blue' => '#2b8df3', 'purple' => '#7137e8', 'green' => '#08a66b', 'red' => '#ef334f'];
            $gradientSegments = [];
            $gradientStart = 0;
            foreach ($statusSummary as $summary) {
                $gradientEnd = $gradientStart + $summary['percent'];
                $gradientSegments[] = $palette[$summary['color']].' '.$gradientStart.'% '.$gradientEnd.'%';
                $gradientStart = $gradientEnd;
            }
            $donutStyle = 'background: conic-gradient('.implode(', ', $gradientSegments ?: ['#dce8f5 0% 100%']).');';
            $alerts = [
                ['show' => $pendingOrders > 0, 'icon' => 'exclamation-triangle', 'class' => 'warning', 'title' => number_format($pendingOrders).' pedidos pendientes', 'detail' => 'Requieren atención inmediata.'],
                ['show' => $cancelledOrders > 0, 'icon' => 'bag-x', 'class' => 'danger', 'title' => number_format($cancelledOrders).' pedidos cancelados', 'detail' => 'Revisa los reembolsos y el stock.'],
                ['show' => $staleOrders > 0, 'icon' => 'bell', 'class' => 'purple', 'title' => number_format($staleOrders).' pedidos sin movimiento', 'detail' => 'Sin actualización en 48 horas.'],
            ];
        ?>

        <section class="pedidos-page" data-admin-orders-page>
            <header class="pedidos-hero">
                <div>
                    <span class="pedidos-eyebrow">Operación comercial</span>
                    <h1>Pedidos <i class="bi bi-cart3" aria-hidden="true"></i></h1>
                    <p>Gestiona pedidos, pagos, envíos y estados desde un solo lugar.</p>
                </div>
                <div class="pedidos-period-card"><i class="bi bi-calendar3" aria-hidden="true"></i><span><small>Periodo consultado</small><strong><?php echo e($filters['periodo'] === 'all' ? 'Todo el historial' : 'Últimos '.$filters['periodo'].' días'); ?></strong></span><i class="bi bi-chevron-down" aria-hidden="true"></i></div>
            </header>

            <section class="pedidos-kpi-grid" aria-label="Resumen de pedidos">
                <article class="pedidos-kpi pedidos-kpi--blue"><span class="pedidos-kpi-icon"><i class="bi bi-cart3" aria-hidden="true"></i></span><div><small>Pedidos hoy</small><strong><?php echo e(number_format($todayOrders)); ?></strong><span>vs. ayer</span></div><b class="pedidos-kpi-trend <?php echo e(($todayOrdersTrend ?? 0) >= 0 ? 'is-positive' : 'is-negative'); ?>"><i class="bi bi-arrow-<?php echo e(($todayOrdersTrend ?? 0) >= 0 ? 'up' : 'down'); ?>" aria-hidden="true"></i> <?php if($todayOrdersTrend !== null): ?><?php echo e(number_format(abs($todayOrdersTrend), 1)); ?>%<?php else: ?> sin periodo anterior <?php endif; ?></b></article>
                <article class="pedidos-kpi pedidos-kpi--orange"><span class="pedidos-kpi-icon"><i class="bi bi-clock" aria-hidden="true"></i></span><div><small>Pendientes</small><strong><?php echo e(number_format($pendingOrders)); ?></strong><span>Requieren atención</span></div><b class="pedidos-kpi-trend <?php echo e($pendingOrders > 0 ? 'is-negative' : 'is-positive'); ?>"><i class="bi bi-<?php echo e($pendingOrders > 0 ? 'arrow-down' : 'check'); ?>" aria-hidden="true"></i> <?php echo e($pendingOrders > 0 ? 'Revisar' : 'Al día'); ?></b></article>
                <article class="pedidos-kpi pedidos-kpi--green"><span class="pedidos-kpi-icon"><i class="bi bi-credit-card" aria-hidden="true"></i></span><div><small>Estado de pago</small><strong><?php echo e($paidOrders === null ? 'No verificado' : number_format($paidOrders)); ?></strong><span>Sin fuente financiera integrada</span></div><b class="pedidos-kpi-trend is-positive"><i class="bi bi-arrow-up" aria-hidden="true"></i> Estado actual</b></article>
                <article class="pedidos-kpi pedidos-kpi--blue"><span class="pedidos-kpi-icon"><i class="bi bi-truck" aria-hidden="true"></i></span><div><small>En envío</small><strong><?php echo e(number_format($shippingOrders)); ?></strong><span>En tránsito</span></div><b class="pedidos-kpi-trend is-positive"><i class="bi bi-arrow-up" aria-hidden="true"></i> Seguimiento</b></article>
                <article class="pedidos-kpi pedidos-kpi--purple"><span class="pedidos-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Entregados</small><strong><?php echo e(number_format($deliveredOrders)); ?></strong><span>Pedidos completados</span></div><b class="pedidos-kpi-trend is-positive"><i class="bi bi-check2" aria-hidden="true"></i> Finalizados</b></article>
                <article class="pedidos-kpi pedidos-kpi--red"><span class="pedidos-kpi-icon"><i class="bi bi-x-circle" aria-hidden="true"></i></span><div><small>Cancelados</small><strong><?php echo e(number_format($cancelledOrders)); ?></strong><span>Pedidos cancelados</span></div><b class="pedidos-kpi-trend <?php echo e($cancelledOrders > 0 ? 'is-negative' : 'is-positive'); ?>"><i class="bi bi-<?php echo e($cancelledOrders > 0 ? 'arrow-down' : 'check'); ?>" aria-hidden="true"></i> <?php echo e($cancelledOrders > 0 ? 'Revisar' : 'Sin casos'); ?></b></article>
            </section>

            <div class="pedidos-toolbar">
                <button class="pedidos-primary-button" type="button" data-admin-dialog-open="nuevo-pedido" title="Crear un pedido desde la tienda"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo pedido</button>
                <details class="pedidos-export-menu"><summary class="pedidos-toolbar-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="pedidos-export-options"><a href="<?php echo e(route('management.orders.export', ['format' => 'pdf'] + request()->query())); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="<?php echo e(route('management.orders.export', ['format' => 'excel'] + request()->query())); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="<?php echo e(route('management.orders.export', ['format' => 'csv'] + request()->query())); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
                <button class="pedidos-toolbar-button" type="button" data-refresh-orders><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Actualizar</button>
            </div>

            <dialog class="admin-dialog pedidos-create-dialog" id="nuevo-pedido" aria-labelledby="nuevo-pedido-title">
                <div class="pedidos-create-card">
                    <header class="pedidos-create-header"><span class="pedidos-create-icon"><i class="bi bi-cart-plus" aria-hidden="true"></i></span><div><small>Operaciones comerciales</small><h2 id="nuevo-pedido-title">Nuevo pedido</h2><p>Selecciona productos y completa el pedido en la tienda.</p></div><button type="button" class="admin-dialog-close" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                    <div class="pedidos-create-body"><div class="pedidos-create-step"><b>1</b><span><strong>Selecciona los productos</strong><small>Consulta el catálogo y agrega los artículos disponibles.</small></span></div><div class="pedidos-create-step"><b>2</b><span><strong>Completa los datos del pedido</strong><small>El cliente, cantidades y total se validan antes de guardar.</small></span></div><div class="pedidos-create-step"><b>3</b><span><strong>Confirma la compra</strong><small>El pedido se registra mediante el flujo real de la tienda.</small></span></div></div>
                    <footer class="pedidos-create-footer"><button type="button" class="pedidos-toolbar-button" data-admin-dialog-close>Cancelar</button><a class="pedidos-primary-button" href="<?php echo e(route('catalog.index')); ?>"><i class="bi bi-shop" aria-hidden="true"></i> Abrir tienda</a></footer>
                </div>
            </dialog>

            <form class="pedidos-filters" method="get" action="<?php echo e(route('management.orders')); ?>">
                <label class="pedidos-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar pedido o cliente</span><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar pedido o cliente..."></label>
                <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Estado: Todos</option><option value="pendiente" <?php if(($filters['estado'] ?? null) === 'Pendiente'): echo 'selected'; endif; ?>>Pendiente</option><option value="en_proceso" <?php if(($filters['estado'] ?? null) === 'En proceso'): echo 'selected'; endif; ?>>En preparación</option><option value="enviado" <?php if(($filters['estado'] ?? null) === 'Enviado'): echo 'selected'; endif; ?>>Enviado</option><option value="entregado" <?php if(($filters['estado'] ?? null) === 'Entregado'): echo 'selected'; endif; ?>>Entregado</option><option value="cancelado" <?php if(($filters['estado'] ?? null) === 'Cancelado'): echo 'selected'; endif; ?>>Cancelado</option></select></label>
                <label><span class="visually-hidden">Filtrar por fecha</span><select name="periodo"><option value="7" <?php if($filters['periodo'] === '7'): echo 'selected'; endif; ?>>Últimos 7 días</option><option value="30" <?php if($filters['periodo'] === '30'): echo 'selected'; endif; ?>>Últimos 30 días</option><option value="90" <?php if($filters['periodo'] === '90'): echo 'selected'; endif; ?>>Últimos 90 días</option><option value="all" <?php if($filters['periodo'] === 'all'): echo 'selected'; endif; ?>>Todo el historial</option></select></label>
                <label><span class="visually-hidden">Filtrar por canal</span><select name="canal"><option value="">Canal: Todos</option><option value="ecommerce" <?php if(($filters['canal'] ?? null) === 'ecommerce'): echo 'selected'; endif; ?>>Tienda online</option></select></label>
                <button class="pedidos-more-filters" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Más filtros</button>
                <?php if(($filters['buscar'] ?? null) || ($filters['estado'] ?? null) || $filters['periodo'] !== '30'): ?><a class="pedidos-clear-filters" href="<?php echo e(route('management.orders')); ?>">Limpiar</a><?php endif; ?>
            </form>

            <section class="pedidos-list-card">
                <header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-receipt" aria-hidden="true"></i><div><h2>Lista de pedidos</h2><p>Mostrando <?php echo e($orders->firstItem() ?? 0); ?> a <?php echo e($orders->lastItem() ?? 0); ?> de <?php echo e(number_format($orders->total())); ?> pedidos</p></div></div><span class="pedidos-total-badge"><?php echo e(number_format($totalOrders)); ?> pedidos totales</span></header>
                <div class="pedidos-table-wrap">
                    <table class="pedidos-table">
                        <thead><tr><th><span class="pedidos-checkbox" aria-hidden="true"></span></th><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Canal</th><th>Total</th><th>Estado</th><th>Pago</th><th>Envío</th><th>Acciones</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <?php
                                    $status = $statusFor($order->estado);
                                    $payment = ['label' => 'No verificado', 'class' => 'muted'];
                                    $shipping = $shippingFor($order->estado);
                                ?>
                                <tr>
                                    <td><span class="pedidos-checkbox" aria-hidden="true"></span></td>
                                    <td><button class="pedidos-number" type="button" data-open-order-dialog="pedido-<?php echo e($order->id); ?>">#PED-<?php echo e(str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)); ?></button></td>
                                    <td><span class="pedidos-customer"><b><?php echo e($initialsFor($order->user?->nombre)); ?></b><span><strong><?php echo e($order->user?->nombre ?: 'Cliente eliminado'); ?></strong><small><?php echo e($order->user?->correo ?: 'Sin correo'); ?></small></span></span></td>
                                    <td class="pedidos-date"><?php echo e($orderDate($order->creado_en)); ?><small><?php echo e($order->creado_en?->format('H:i')); ?></small></td>
                                    <td><span class="pedidos-channel"><i class="bi bi-shop" aria-hidden="true"></i> Tienda online</span></td>
                                    <td><strong class="pedidos-total">S/ <?php echo e(number_format((float) $order->total, 2)); ?></strong></td>
                                    <td><span class="pedidos-status pedidos-status--<?php echo e($status['class']); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($status['label']); ?></span></td>
                                    <td><span class="pedidos-status pedidos-status--<?php echo e($payment['class']); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($payment['label']); ?></span></td>
                                    <td><span class="pedidos-status pedidos-status--<?php echo e($shipping['class']); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($shipping['label']); ?></span></td>
                                    <td><button class="pedidos-action-button" type="button" data-open-order-dialog="pedido-<?php echo e($order->id); ?>" aria-label="Ver pedido <?php echo e($order->id); ?>"><i class="bi bi-three-dots" aria-hidden="true"></i></button></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr><td colspan="10" class="pedidos-empty"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay pedidos que coincidan con los filtros.</strong><span>Prueba con otro periodo o estado.</span></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <footer class="pedidos-pagination"><span>Mostrando <?php echo e($orders->firstItem() ?? 0); ?>–<?php echo e($orders->lastItem() ?? 0); ?> de <?php echo e(number_format($orders->total())); ?> pedidos</span><form class="pedidos-per-page" method="get" action="<?php echo e(route('management.orders')); ?>"><?php $__currentLoopData = request()->except(['page', 'por_pagina']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php endif; ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><label for="pedidos-por-pagina">Pedidos por página</label><select id="pedidos-por-pagina" name="por_pagina" onchange="this.form.submit()" aria-label="Pedidos por página"><?php $__currentLoopData = [10, 20, 50]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if(($filters['por_pagina'] ?? 10) === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></form><?php echo e($orders->onEachSide(1)->links()); ?></footer>
            </section>

            <div class="pedidos-insights-grid">
                <section class="pedidos-insight-card">
                    <header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-pie-chart" aria-hidden="true"></i><div><h2>Estado de pedidos</h2><p><?php echo e(number_format($totalOrders)); ?> pedidos totales</p></div></div><span class="pedidos-insight-period">Periodo actual</span></header>
                    <div class="pedidos-status-chart"><div class="pedidos-donut" style="<?php echo e($donutStyle); ?>"><span><strong><?php echo e(number_format($totalOrders)); ?></strong><small>Pedidos totales</small></span></div><div class="pedidos-legend"><?php $__currentLoopData = $statusSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div><span><i class="pedidos-legend-dot pedidos-legend-dot--<?php echo e($summary['color']); ?>" aria-hidden="true"></i><?php echo e($summary['label']); ?></span><strong><?php echo e(number_format($summary['count'])); ?></strong><small><?php echo e(number_format($summary['percent'], 1)); ?>%</small></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div></div>
                </section>

                <section class="pedidos-insight-card">
                    <header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-people" aria-hidden="true"></i><div><h2>Pedidos por cliente</h2><p>Distribución de compradores registrados.</p></div></div><span class="pedidos-insight-period"><?php echo e(number_format($totalOrders)); ?> pedidos</span></header>
                    <div class="pedidos-bars"><?php $__currentLoopData = $customerSummary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $summary): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><div class="pedidos-bar-row"><span><i class="pedidos-legend-dot pedidos-legend-dot--<?php echo e($summary['color']); ?>" aria-hidden="true"></i><?php echo e($summary['label']); ?></span><div class="pedidos-bar"><i class="pedidos-bar-fill pedidos-bar-fill--<?php echo e($summary['color']); ?>" style="width: <?php echo e(min(100, $summary['percent'])); ?>%"></i></div><strong><?php echo e(number_format($summary['percent'], 1)); ?>%</strong><small><?php echo e(number_format($summary['count'])); ?></small></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></div>
                </section>
            </div>

            <div class="pedidos-bottom-grid">
                <section class="pedidos-secondary-card"><header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-bag-check" aria-hidden="true"></i><div><h2>Pedidos recientes</h2><p>Últimas operaciones registradas.</p></div></div><button class="pedidos-view-all" type="button" data-refresh-orders>Actualizar</button></header><div class="pedidos-recent-list">
                    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $recentStatus = $statusFor($order->estado); $recentProduct = $order->details->first()?->product; $recentImage = $productImage($recentProduct?->url_imagen); ?>
                        <button class="pedidos-recent-row" type="button" data-open-order-dialog="pedido-<?php echo e($order->id); ?>"><?php if($recentImage): ?><img src="<?php echo e($recentImage); ?>" alt="<?php echo e($recentProduct?->nombre); ?>"><?php else: ?><span class="pedidos-recent-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span><?php endif; ?><span><strong>#PED-<?php echo e(str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)); ?></strong><small><?php echo e($order->user?->nombre ?: 'Cliente eliminado'); ?> · S/ <?php echo e(number_format((float) $order->total, 2)); ?></small></span><em class="pedidos-status pedidos-status--<?php echo e($recentStatus['class']); ?>"><?php echo e($recentStatus['label']); ?></em></button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="pedidos-secondary-empty">Todavía no hay pedidos recientes.</div>
                    <?php endif; ?>
                </div></section>

                <section class="pedidos-secondary-card"><header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-stars" aria-hidden="true"></i><div><h2>Actividad reciente</h2><p>Últimos cambios de la operación.</p></div></div><button class="pedidos-view-all" type="button" data-refresh-orders>Ver todo →</button></header><div class="pedidos-activity-list">
                    <?php $__empty_1 = true; $__currentLoopData = $recentOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $activity = $statusFor($order->estado); $activityIcon = match($order->estado) { 'Entregado' => 'check2', 'Cancelado' => 'x-lg', 'Enviado' => 'truck', 'En proceso' => 'box-seam', default => 'cart3' }; ?>
                        <div class="pedidos-activity-row"><span class="pedidos-activity-icon pedidos-activity-icon--<?php echo e($activity['class']); ?>"><i class="bi bi-<?php echo e($activityIcon); ?>" aria-hidden="true"></i></span><span><strong>Pedido #PED-<?php echo e(str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)); ?> <?php echo e(strtolower($activity['label'])); ?></strong><small><?php echo e($order->user?->nombre ?: 'Cliente eliminado'); ?> · <?php echo e($order->creado_en?->format('d/m/Y H:i')); ?></small></span></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="pedidos-secondary-empty">Todavía no hay actividad.</div>
                    <?php endif; ?>
                </div></section>

                <section class="pedidos-secondary-card pedidos-alert-card"><header class="pedidos-card-header"><div class="pedidos-card-title"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i><div><h2>Alertas e incidencias</h2><p>Situaciones que requieren seguimiento.</p></div></div><span class="pedidos-alert-count"><?php echo e(collect($alerts)->where('show', true)->count()); ?></span></header><div class="pedidos-alert-list">
                    <?php $__empty_1 = true; $__currentLoopData = collect($alerts)->where('show', true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div class="pedidos-alert-row"><span class="pedidos-alert-icon pedidos-alert-icon--<?php echo e($alert['class']); ?>"><i class="bi bi-<?php echo e($alert['icon']); ?>" aria-hidden="true"></i></span><span><strong><?php echo e($alert['title']); ?></strong><small><?php echo e($alert['detail']); ?></small></span></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="pedidos-secondary-empty"><i class="bi bi-check-circle" aria-hidden="true"></i>Operación al día.</div><?php endif; ?>
                </div></section>
            </div>
        </section>

        <?php $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $dialogStatus = $statusFor($order->estado); ?>
            <dialog class="pedidos-dialog" id="pedido-<?php echo e($order->id); ?>"><div class="pedidos-dialog-card"><header class="pedidos-dialog-header"><div><span class="pedidos-eyebrow">Detalle del pedido</span><h2>#PED-<?php echo e(str_pad((string) $order->id, 5, '0', STR_PAD_LEFT)); ?></h2><p><?php echo e($order->user?->nombre ?: 'Cliente eliminado'); ?> · <?php echo e($orderDate($order->creado_en)); ?></p></div><button class="pedidos-dialog-close" type="button" data-close-order-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="pedidos-dialog-body"><div class="pedidos-dialog-summary"><div><small>Total</small><strong>S/ <?php echo e(number_format((float) $order->total, 2)); ?></strong></div><div><small>Estado</small><span class="pedidos-status pedidos-status--<?php echo e($dialogStatus['class']); ?>"><?php echo e($dialogStatus['label']); ?></span></div><div><small>Cliente</small><strong><?php echo e($order->user?->correo ?: 'Sin correo'); ?></strong></div></div><h3>Productos del pedido</h3><div class="pedidos-dialog-products"><?php $__empty_1 = true; $__currentLoopData = $order->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><div><span><?php echo e($line->product?->nombre ?: 'Producto eliminado'); ?> × <?php echo e($line->cantidad); ?></span><strong>S/ <?php echo e(number_format($line->cantidad * $line->precio_unitario, 2)); ?></strong></div><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><div class="pedidos-secondary-empty">Sin detalle de productos.</div><?php endif; ?></div><form method="post" action="<?php echo e(route('legacy.admin.orders.status', $order)); ?>" class="pedidos-status-form"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?><label><span>Actualizar estado</span><select name="estado"><?php $__currentLoopData = ['Pendiente', 'En proceso', 'Enviado', 'Entregado', 'Cancelado']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $state): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($state); ?>" <?php if($order->estado === $state): echo 'selected'; endif; ?>><?php echo e($state === 'En proceso' ? 'En preparación' : $state); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label><button class="pedidos-primary-button" type="submit"><i class="bi bi-check2" aria-hidden="true"></i> Guardar estado</button></form></div></div></dialog>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    <?php else: ?>
        <section class="container content-page">
            <header class="admin-page-head">
                <div><span class="eyebrow">Operación comercial</span><h1>Pedidos <i class="bi bi-cart3"></i></h1><p>Consulta el historial y actualiza el estado según tu rol.</p></div>
            </header>
            <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <article class="data-row"><div><b>Pedido #<?php echo e($order->id); ?></b><small><?php echo e($order->creado_en?->format('d/m/Y H:i') ?? $order->creado_en); ?> · <?php echo e($order->estado); ?></small></div><strong>S/ <?php echo e(number_format((float) $order->total, 2)); ?></strong><a class="btn btn-secondary" href="<?php echo e(route('orders.show', $order)); ?>">Ver detalle</a></article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p>Aún no hay pedidos.</p>
            <?php endif; ?>
            <?php echo e($orders->links()); ?>

        </section>
    <?php endif; ?>
<?php $__env->stopSection(); ?>

<?php if($isAdminPanel || ($isAdmin && auth()->user()->can('gestion.seguridad.consultar'))): ?>
    <?php $__env->startPush('scripts'); ?>
        <script>
            (() => {
                const page = document.querySelector('[data-admin-orders-page]');

                if (!page) {
                    return;
                }

                page.querySelectorAll('[data-open-order-dialog]').forEach((button) => {
                    button.addEventListener('click', () => document.getElementById(button.dataset.openOrderDialog)?.showModal());
                });
                document.querySelectorAll('.pedidos-dialog').forEach((dialog) => {
                    dialog.querySelectorAll('[data-close-order-dialog]').forEach((button) => button.addEventListener('click', () => dialog.close()));
                    dialog.addEventListener('click', (event) => {
                        if (event.target === dialog) {
                            dialog.close();
                        }
                    });
                });
                page.querySelectorAll('[data-refresh-orders]').forEach((button) => button.addEventListener('click', () => window.location.reload()));
            })();
        </script>
    <?php $__env->stopPush(); ?>
<?php endif; ?>


<?php echo $__env->make($isAdmin ? 'layouts.panel.principal' : 'layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>