<?php $__env->startSection('title', 'Inventario'); ?>
<?php $__env->startSection('body_class', 'modulo-inventario'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $productImage = static function (?string $path): ?string {
            if (!$path) {
                return null;
            }

            return str_starts_with($path, 'http') ? $path : asset(ltrim($path, '/'));
        };
        $statusForStock = static function (int $stock): array {
            return $stock > 0
                ? ['label' => 'Con existencias', 'class' => 'optimal']
                : ['label' => 'Sin existencias', 'class' => 'critical'];
        };
        $monthNames = [1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun', 7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'];
        $today = now();
        $todayLabel = $today->format('d').' de '.$monthNames[(int) $today->format('n')].', '.$today->format('Y');
    ?>

    <section class="inventario-page" data-inventory-page>
        <header class="inventario-hero">
            <div>
                <span class="inventario-eyebrow">Operaciones</span>
                <h1>Inventario <i class="bi bi-boxes" aria-hidden="true"></i></h1>
                <p>Controla existencias, entradas, salidas y ajustes con trazabilidad completa.</p>
            </div>
            <div class="inventario-date-card">
                <i class="bi bi-calendar3" aria-hidden="true"></i>
                <span><small>Hoy</small><strong><?php echo e($todayLabel); ?></strong></span>
                <i class="bi bi-chevron-down" aria-hidden="true"></i>
            </div>
        </header>

        <section class="inventario-kpi-grid" aria-label="Resumen del inventario">
            <article class="inventario-kpi inventario-kpi--blue">
                <span class="inventario-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Productos con existencias</small><strong><?php echo e(number_format($activeProducts)); ?></strong><span>Productos con saldo físico en el alcance autorizado</span></div>
                <b class="inventario-kpi-trend is-positive"><i class="bi bi-box-seam" aria-hidden="true"></i> Saldo físico</b>
            </article>
            <article class="inventario-kpi inventario-kpi--orange">
                <span class="inventario-kpi-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span>
                <div><small>Productos sin existencias</small><strong><?php echo e(number_format($sinExistencias)); ?></strong><span>Productos con saldo físico igual a cero</span></div>
                <b class="inventario-kpi-trend <?php echo e($sinExistencias > 0 ? 'is-negative' : 'is-positive'); ?>"><i class="bi bi-<?php echo e($sinExistencias > 0 ? 'exclamation-triangle' : 'check'); ?>" aria-hidden="true"></i> <?php echo e($sinExistencias > 0 ? 'Revisar' : 'Sin faltantes'); ?></b>
            </article>
            <article class="inventario-kpi inventario-kpi--indigo">
                <span class="inventario-kpi-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span>
                <div><small>Movimientos hoy</small><strong><?php echo e(number_format($movementsToday)); ?></strong><span>Entradas, salidas y ajustes</span></div>
                <?php if($movementsTrend !== null): ?>
                    <b class="inventario-kpi-trend <?php echo e($movementsTrend >= 0 ? 'is-positive' : 'is-negative'); ?>"><i class="bi bi-arrow-<?php echo e($movementsTrend >= 0 ? 'up' : 'down'); ?>-right" aria-hidden="true"></i> <?php echo e(number_format(abs($movementsTrend), 1)); ?>%</b>
                <?php else: ?>
                    <b class="inventario-kpi-trend">Sin periodo anterior</b>
                <?php endif; ?>
            </article>
            <article class="inventario-kpi inventario-kpi--green">
                <span class="inventario-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Valor inventario</small><strong>S/ <?php echo e(number_format($inventoryValue, 2)); ?></strong><span>Valor a precio de catálogo del stock autorizado</span></div>
                <b class="inventario-kpi-trend is-positive"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i> Valor actual</b>
            </article>
        </section>

        <div class="inventario-toolbar">
            <button class="inventario-primary-button" type="button" data-open-inventory-movement><i class="bi bi-plus-lg" aria-hidden="true"></i> Registrar movimiento <i class="bi bi-chevron-down" aria-hidden="true"></i></button>
            <button class="inventario-toolbar-button inventario-toolbar-button--green" type="button" data-open-inventory-movement data-movement-type="entrada"><i class="bi bi-arrow-down" aria-hidden="true"></i> Entrada</button>
            <button class="inventario-toolbar-button inventario-toolbar-button--red" type="button" data-open-inventory-movement data-movement-type="salida"><i class="bi bi-arrow-up" aria-hidden="true"></i> Salida</button>
            <button class="inventario-toolbar-button inventario-toolbar-button--purple" type="button" data-open-inventory-movement data-movement-type="ajuste"><i class="bi bi-pencil" aria-hidden="true"></i> Ajuste</button>
            <button class="inventario-toolbar-button" type="button" data-open-inventory-movement data-movement-type="ajuste"><i class="bi bi-clipboard-check" aria-hidden="true"></i> Conteo físico</button>
        </div>

        <form class="inventario-filters" method="get" action="<?php echo e(route('management.inventory')); ?>">
            <label class="inventario-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar producto</span><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar producto, SKU..."></label>
            <label><span class="visually-hidden">Filtrar por categoría</span><select name="categoria"><option value="">Todas las categorías</option><?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($category->id); ?>" <?php if(($filters['categoria'] ?? null) === $category->id): echo 'selected'; endif; ?>><?php echo e($category->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <label><span class="visually-hidden">Filtrar por estado</span><select name="estado"><option value="">Todos los estados</option><option value="con_existencias" <?php if(($filters['estado'] ?? null) === 'con_existencias'): echo 'selected'; endif; ?>>Con existencias</option><option value="sin_existencias" <?php if(($filters['estado'] ?? null) === 'sin_existencias'): echo 'selected'; endif; ?>>Sin existencias</option></select></label>
            <label><span class="visually-hidden">Filtrar por proveedor</span><select name="proveedor"><option value="">Todos los proveedores</option><?php $__currentLoopData = $suppliers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $supplier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($supplier->id); ?>" <?php if(($filters['proveedor'] ?? null) === $supplier->id): echo 'selected'; endif; ?>><?php echo e($supplier->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
            <button class="inventario-filter-button" type="submit"><i class="bi bi-search" aria-hidden="true"></i> Buscar</button>
            <?php if(($filters['buscar'] ?? null) || ($filters['categoria'] ?? null) || ($filters['estado'] ?? null) || ($filters['proveedor'] ?? null)): ?>
                <a class="inventario-clear-button" href="<?php echo e(route('management.inventory')); ?>">Limpiar filtros</a>
            <?php endif; ?>
        </form>

        <section class="inventario-table-card">
            <header class="inventario-card-header">
                <div class="inventario-card-title"><i class="bi bi-boxes" aria-hidden="true"></i><div><h2>Lista de inventario</h2><p><?php echo e(number_format($products->total())); ?> productos en total</p></div></div>
                <button class="inventario-view-all" type="button" data-open-inventory-movement>Registrar movimiento</button>
            </header>
            <div class="inventario-table-wrap">
                <table class="inventario-table">
                    <thead><tr><th>Producto</th><th>SKU</th><th>Categoría</th><th>Existencias físicas</th><th>Reservado</th><th>Proveedor</th><th>Último movimiento</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $image = $productImage($product->url_imagen);
                                $status = $statusForStock((int) $product->existencias_alcance);
                                $movement = $latestMovements->get($product->id);
                                $codigosSku = $product->variants->pluck('codigo_sku')->filter()->unique()->values();
                                $movementLabel = match ($movement?->tipo_movimiento) { 'entrada' => 'Entrada', 'salida' => 'Salida', 'ajuste' => 'Ajuste', default => 'Sin movimientos' };
                                $movementClass = match ($movement?->tipo_movimiento) { 'entrada' => 'entry', 'salida' => 'exit', 'ajuste' => 'adjustment', default => 'empty' };
                            ?>
                            <tr>
                                <td class="inventario-product-cell">
                                    <?php if($image): ?><img src="<?php echo e($image); ?>" alt="<?php echo e($product->nombre); ?>"><?php else: ?><span class="inventario-product-image"><i class="bi bi-box-seam" aria-hidden="true"></i></span><?php endif; ?>
                                    <span><strong><?php echo e($product->marca); ?> <?php echo e($product->nombre); ?></strong><small><?php echo e($product->nombre); ?></small></span>
                                </td>
                                <td class="inventario-sku"><?php echo e($codigosSku->isNotEmpty() ? $codigosSku->implode(', ') : 'Sin SKU registrado'); ?></td>
                                <td><?php echo e($product->category?->nombre ?: ($product->categoria ?: 'Sin categoría')); ?></td>
                                <td><span class="inventario-stock inventario-stock--<?php echo e($status['class']); ?>"><?php echo e(number_format($product->existencias_alcance)); ?> unidades</span></td>
                                <td class="inventario-minimum"><?php echo e(number_format($product->existencias_reservadas)); ?></td>
                                <td><?php echo e($product->suppliers->first()?->nombre ?: 'Sin proveedor'); ?></td>
                                <td class="inventario-movement inventario-movement--<?php echo e($movementClass); ?>">
                                    <?php if($movement): ?>
                                        <strong><i class="bi bi-arrow-<?php echo e($movement->tipo_movimiento === 'entrada' ? 'down' : 'up'); ?>" aria-hidden="true"></i> <?php echo e($movementLabel); ?></strong><small><?php echo e($movement->creado_en?->format('d/m/Y, H:i')); ?></small>
                                    <?php else: ?>
                                        <span>Sin movimientos</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="inventario-status inventario-status--<?php echo e($status['class']); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($status['label']); ?></span></td>
                                <td class="inventario-actions-cell"><button class="inventario-view-button" type="button" data-open-inventory-movement data-movement-type="ajuste" data-inventory-product="<?php echo e($product->id); ?>" title="Registrar conteo físico para <?php echo e($product->nombre); ?>"><i class="bi bi-clipboard-check" aria-hidden="true"></i><span>Ajustar</span></button><button class="inventario-more-button" type="button" data-open-inventory-detail aria-label="Consultar información de inventario"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="9" class="inventario-empty"><i class="bi bi-inbox" aria-hidden="true"></i><?php if($products->total() === 0 && !($filters['buscar'] || $filters['categoria'] || $filters['estado'] || $filters['proveedor'])): ?><strong>No hay productos registrados.</strong><span>Los productos aparecerán aquí cuando se registren en el catálogo.</span><?php else: ?><strong>No hay productos para los filtros aplicados.</strong><span>Prueba con otros filtros de inventario.</span><?php endif; ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <footer class="inventario-pagination">
                <span>Mostrando <?php echo e($products->firstItem() ?? 0); ?> a <?php echo e($products->lastItem() ?? 0); ?> de <?php echo e($products->total()); ?> productos</span>
                <form class="inventario-per-page-form" method="get" action="<?php echo e(route('management.inventory')); ?>">
                    <?php $__currentLoopData = request()->except(['page', 'por_pagina']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if(is_scalar($value)): ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <label for="inventario-por-pagina">Productos por página</label>
                    <select id="inventario-por-pagina" name="por_pagina" onchange="this.form.submit()" aria-label="Productos por página"><?php $__currentLoopData = [7, 8, 10, 20, 50]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($option); ?>" <?php if(($filters['por_pagina'] ?? 7) === $option): echo 'selected'; endif; ?>><?php echo e($option); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select>
                </form>
                <?php echo e($products->onEachSide(1)->links()); ?>

            </footer>
        </section>

        <div class="inventario-bottom-grid">
            <section class="inventario-secondary-card">
                <header class="inventario-card-header"><div class="inventario-card-title"><i class="bi bi-exclamation-triangle-fill inventario-title-icon--orange" aria-hidden="true"></i><div><h2>Productos sin existencias</h2><p>Saldo físico igual a cero en el alcance autorizado.</p></div></div><a class="inventario-view-all" href="<?php echo e(route('management.inventory', ['estado' => 'sin_existencias'])); ?>">Ver todas</a></header>
                <div class="inventario-alert-list">
                    <?php $__empty_1 = true; $__currentLoopData = $productosSinExistencias; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $status = $statusForStock((int) $product->existencias_alcance);
                        ?>
                        <div class="inventario-alert-row"><span class="inventario-alert-icon inventario-alert-icon--<?php echo e($status['class']); ?>"><i class="bi bi-<?php echo e($status['class'] === 'critical' ? 'box' : 'exclamation-triangle'); ?>" aria-hidden="true"></i></span><span><strong><?php echo e($product->nombre); ?></strong><small><?php echo e($product->suppliers->first()?->nombre ?: 'Sin proveedor'); ?></small></span><b class="inventario-alert-stock"><?php echo e(number_format($product->existencias_alcance)); ?></b><span class="inventario-status inventario-status--<?php echo e($status['class']); ?>"><?php echo e($status['label']); ?></span><button type="button" class="inventario-register-alert" data-open-inventory-movement data-inventory-product="<?php echo e($product->id); ?>">Registrar</button></div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="inventario-secondary-empty"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>No hay productos sin existencias</strong><span>Los saldos físicos consultados son mayores que cero.</span></div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="inventario-secondary-card">
                <header class="inventario-card-header"><div class="inventario-card-title"><i class="bi bi-clock-history" aria-hidden="true"></i><div><h2><?php echo e($verHistorialMovimientos ? 'Historial de movimientos' : 'Movimientos recientes'); ?></h2><p><?php echo e($verHistorialMovimientos ? 'Movimientos registrados en las sedes autorizadas.' : 'Últimos movimientos de inventario.'); ?></p></div></div><a class="inventario-view-all" href="<?php echo e($verHistorialMovimientos ? route('management.inventory') : route('management.inventory.movements')); ?>"><?php echo e($verHistorialMovimientos ? 'Volver al inventario' : 'Ver todos'); ?></a></header>
                <?php if($verHistorialMovimientos): ?>
                    <form class="inventario-filters" method="get" action="<?php echo e(route('management.inventory.movements')); ?>">
                        <label class="inventario-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar movimientos</span><input type="search" name="mov_buscar" value="<?php echo e($filtrosMovimientos['buscar']); ?>" maxlength="100" placeholder="Producto, motivo o documento"></label>
                        <label><span class="visually-hidden">Tipo de movimiento</span><select name="mov_tipo"><option value="">Todos los tipos</option><?php $__currentLoopData = $tiposMovimiento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($tipo); ?>" <?php if($filtrosMovimientos['tipo'] === $tipo): echo 'selected'; endif; ?>><?php echo e(ucfirst($tipo)); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                        <label><span class="visually-hidden">Almacén</span><select name="mov_almacen"><option value="">Todos los almacenes autorizados</option><?php $__currentLoopData = $almacenesMovimiento; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $almacen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($almacen->id); ?>" <?php if($filtrosMovimientos['almacen'] === (int) $almacen->id): echo 'selected'; endif; ?>><?php echo e($almacen->sede_nombre); ?> · <?php echo e($almacen->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                        <button class="inventario-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
                        <?php if($hayFiltrosMovimientos): ?><a class="inventario-clear-button" href="<?php echo e(route('management.inventory.movements')); ?>">Limpiar</a><?php endif; ?>
                    </form>
                <?php endif; ?>
                <div class="inventario-recent-table-wrap">
                    <table class="inventario-recent-table"><thead><tr><th>Tipo</th><th>Producto</th><th>Cantidad</th><th>Motivo</th><th>Usuario</th><th>Fecha</th><?php if($verHistorialMovimientos): ?><th>Detalle</th><?php endif; ?></tr></thead><tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $recentMovements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $movement): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $movementClass = $movement->tipo_movimiento === 'entrada' ? 'entry' : ($movement->tipo_movimiento === 'salida' ? 'exit' : 'adjustment');
                                $movementQuantity = $movement->tipo_movimiento === 'salida' ? -abs((float) $movement->cantidad) : (float) $movement->cantidad;
                                $cantidadFormateada = rtrim(rtrim(number_format($movementQuantity, 6, '.', ''), '0'), '.');
                            ?>
                            <tr><td><span class="inventario-recent-type inventario-recent-type--<?php echo e($movementClass); ?>"><i class="bi bi-<?php echo e($movement->tipo_movimiento === 'ajuste' ? 'pencil' : 'arrow-'.($movement->tipo_movimiento === 'entrada' ? 'down' : 'up')); ?>" aria-hidden="true"></i><?php echo e(ucfirst($movement->tipo_movimiento)); ?></span></td><td><?php echo e($movement->product?->marca); ?> <?php echo e($movement->product?->nombre ?: 'Producto eliminado'); ?></td><td class="inventario-quantity <?php echo e($movementQuantity >= 0 ? 'is-positive' : 'is-negative'); ?>"><?php echo e($movementQuantity >= 0 ? '+' : ''); ?><?php echo e($cantidadFormateada); ?></td><td><?php echo e(\Illuminate\Support\Str::limit($movement->notas, 24)); ?></td><td><?php echo e($movement->user?->nombre ?: 'Sistema'); ?></td><td><?php echo e($movement->creado_en?->format('d/m/Y H:i')); ?></td><?php if($verHistorialMovimientos): ?><td><button class="inventario-more-button" type="button" data-open-movement-detail data-tipo="<?php echo e($movement->tipo_movimiento); ?>" data-cantidad="<?php echo e($movementQuantity); ?>" data-fecha="<?php echo e($movement->creado_en?->format('d/m/Y H:i')); ?>" data-usuario="<?php echo e($movement->user?->nombre ?: 'Sistema'); ?>" data-notas="<?php echo e($movement->notas); ?>" data-producto="<?php echo e(trim(($movement->product?->marca ?? '').' '.($movement->product?->nombre ?? 'Producto eliminado'))); ?>" data-variante="<?php echo e($movement->variante?->codigo_sku ?: $movement->variante?->nombre_color ?: 'Sin variante'); ?>" data-almacen="<?php echo e($movement->almacen?->nombre ?: 'Sin almacén'); ?>" data-documento="<?php echo e(trim(($movement->documento_tipo ?? '').' '.($movement->documento_id ?? '')) ?: 'Sin documento asociado'); ?>" aria-label="Ver detalle del movimiento"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button></td><?php endif; ?></tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="<?php echo e($verHistorialMovimientos ? 7 : 6); ?>" class="inventario-recent-empty"><?php if($hayFiltrosMovimientos): ?>No se encontraron movimientos con los filtros seleccionados.<?php else: ?> Todavía no hay movimientos registrados.<?php endif; ?></td></tr>
                        <?php endif; ?>
                    </tbody></table>
                </div>
                <?php if($verHistorialMovimientos && $recentMovements->hasPages()): ?>
                    <div class="inventario-pagination"><?php echo e($recentMovements->links()); ?></div>
                <?php endif; ?>
            </section>
        </div>
    </section>

    <dialog class="inventario-dialog" id="registrar-movimiento-inventario" aria-labelledby="inventario-movimiento-titulo">
        <div class="inventario-dialog-card">
            <header class="inventario-dialog-header"><div class="inventario-card-title"><span class="inventario-dialog-icon"><i class="bi bi-arrow-left-right" aria-hidden="true"></i></span><div><h2 id="inventario-movimiento-titulo">Registrar movimiento</h2><p>Actualiza las existencias y conserva la trazabilidad.</p></div></div><button class="inventario-dialog-close" type="button" data-close-inventory-movement aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <form method="post" action="<?php echo e(route('management.inventory.adjust', ['product' => 0])); ?>" data-ruta-base="<?php echo e(route('management.inventory.adjust', ['product' => 0])); ?>" data-hay-productos="<?php echo e($movementProducts->isNotEmpty() ? '1' : '0'); ?>" data-hay-almacenes="<?php echo e($almacenes->isNotEmpty() ? '1' : '0'); ?>" data-inventory-movement-form class="inventario-movement-form">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="producto_id" value="<?php echo e(old('producto_id')); ?>" data-inventory-product-input>
                <label><span>Producto <b>*</b></span><select data-inventory-product-select required><option value="">Selecciona un producto</option><?php $__currentLoopData = $movementProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($product->id); ?>" <?php if((string) old('producto_id') === (string) $product->id): echo 'selected'; endif; ?>><?php echo e($product->marca); ?> <?php echo e($product->nombre); ?> · Existencias: <?php echo e($product->existencias_alcance); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <?php if($movementProducts->isEmpty()): ?>
                    <p class="inventario-count-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>No hay productos registrados para esta empresa. Registra primero un producto en <a href="<?php echo e(route('management.products')); ?>">Gestión de productos</a>.</p>
                <?php endif; ?>
                <label><span>Almacén <b>*</b></span><select name="almacen_id" data-inventory-warehouse-select required><option value="">Selecciona un almacén</option><?php $__currentLoopData = $almacenes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $almacen): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($almacen->id); ?>" <?php if((string) old('almacen_id') === (string) $almacen->id): echo 'selected'; endif; ?>><?php echo e($almacen->sede_nombre); ?> · <?php echo e($almacen->nombre); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <?php if($almacenes->isEmpty()): ?>
                    <p class="inventario-count-notice"><i class="bi bi-info-circle" aria-hidden="true"></i>No hay almacenes activos autorizados para esta empresa y sede. Configura primero un almacén para registrar existencias.</p>
                <?php endif; ?>
                <label><span>Variante</span><select name="variante_id" data-inventory-variant-select><option value="" <?php if(old('variante_id') === null): echo 'selected'; endif; ?>>Producto sin variante</option><?php $__currentLoopData = $movementProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php $__currentLoopData = $product->variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variante): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($variante->id); ?>" data-producto-id="<?php echo e($product->id); ?>" <?php if((string) old('variante_id') === (string) $variante->id): echo 'selected'; endif; ?>><?php echo e($product->nombre); ?> · <?php echo e($variante->codigo_sku ?: $variante->nombre_color); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?></select></label>
                <label><span>Tipo de movimiento <b>*</b></span><select name="tipo_movimiento" data-inventory-type-select required><option value="entrada" <?php if(old('tipo_movimiento') === 'entrada'): echo 'selected'; endif; ?>>Entrada</option><option value="salida" <?php if(old('tipo_movimiento') === 'salida'): echo 'selected'; endif; ?>>Salida</option><option value="ajuste" <?php if(old('tipo_movimiento') === 'ajuste'): echo 'selected'; endif; ?>>Ajuste</option></select></label>
                <label><span><span data-inventory-quantity-label>Cantidad del movimiento</span> <b>*</b></span><input type="number" name="cantidad" min="1" value="<?php echo e(old('cantidad')); ?>" data-inventory-quantity required></label>
                <label class="inventario-form-wide"><span>Motivo u observación <b>*</b></span><textarea name="notas" maxlength="500" rows="3" required><?php echo e(old('notas')); ?></textarea></label>
                <footer class="inventario-dialog-footer"><button class="inventario-cancel-button" type="button" data-close-inventory-movement>Cancelar</button><button class="inventario-primary-button" type="submit" <?php if($movementProducts->isEmpty() || $almacenes->isEmpty()): echo 'disabled'; endif; ?>><i class="bi bi-check2" aria-hidden="true"></i> Guardar movimiento</button></footer>
            </form>
        </div>
    </dialog>

    <dialog class="inventario-dialog inventario-detail-dialog" data-inventory-detail-dialog aria-labelledby="inventory-detail-title">
        <div class="inventario-dialog-card"><header class="inventario-dialog-header"><div class="inventario-card-title"><span class="inventario-dialog-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><h2 id="inventory-detail-title">Detalle de inventario</h2><p>Consulta de solo lectura con la información de esta pantalla.</p></div></div><button class="inventario-dialog-close" type="button" data-close-inventory-detail aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><dl class="inventario-detail-list" data-inventory-detail-fields></dl><footer class="inventario-dialog-footer"><button class="inventario-cancel-button" type="button" data-close-inventory-detail>Cerrar</button></footer></div>
    </dialog>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const dialog = document.querySelector('#registrar-movimiento-inventario');
            const page = document.querySelector('[data-inventory-page]');
            const productSelect = dialog?.querySelector('[data-inventory-product-select]');
            const productInput = dialog?.querySelector('[data-inventory-product-input]');
            const warehouseSelect = dialog?.querySelector('[data-inventory-warehouse-select]');
            const variantSelect = dialog?.querySelector('[data-inventory-variant-select]');
            const typeSelect = dialog?.querySelector('[data-inventory-type-select]');
            const quantityInput = dialog?.querySelector('[data-inventory-quantity]');
            const quantityLabel = dialog?.querySelector('[data-inventory-quantity-label]');
            const formularioMovimiento = dialog?.querySelector('[data-inventory-movement-form]');
            const botonGuardarMovimiento = formularioMovimiento?.querySelector('[type="submit"]');
            const detailDialog = document.querySelector('[data-inventory-detail-dialog]');

            if (!dialog || !page || !productSelect || !productInput || !warehouseSelect || !variantSelect || !typeSelect || !quantityInput || !quantityLabel || !formularioMovimiento || !botonGuardarMovimiento) {
                return;
            }

            const rutaMovimientoBase = formularioMovimiento.dataset.rutaBase.replace(/\/0$/, '');
            const opcionProductoBase = variantSelect.querySelector('option[value=""]');
            const opcionesVariantes = [...variantSelect.querySelectorAll('option[data-producto-id]')];

            const actualizarRutaMovimiento = () => {
                const productoId = productSelect.value;
                productInput.value = productoId;
                formularioMovimiento.action = productoId ? `${rutaMovimientoBase}/${encodeURIComponent(productoId)}` : `${rutaMovimientoBase}/0`;
                botonGuardarMovimiento.disabled = productoId === '' || warehouseSelect.value === '' || formularioMovimiento.dataset.hayProductos !== '1' || formularioMovimiento.dataset.hayAlmacenes !== '1';
            };

            const actualizarVariantesDisponibles = () => {
                const productoId = productSelect.value;
                let variantesDelProducto = 0;
                opcionesVariantes.forEach((opcion) => {
                    const perteneceAlProducto = opcion.dataset.productoId === productoId;
                    opcion.hidden = !perteneceAlProducto;
                    opcion.disabled = !perteneceAlProducto;
                    variantesDelProducto += perteneceAlProducto ? 1 : 0;
                });

                const requiereVariante = productoId !== '' && variantesDelProducto > 0;
                opcionProductoBase.hidden = requiereVariante;
                opcionProductoBase.disabled = requiereVariante;
                variantSelect.required = requiereVariante;
                variantSelect.disabled = productoId === '' || !requiereVariante;

                if (!requiereVariante || variantSelect.selectedOptions[0]?.dataset.productoId !== productoId) {
                    variantSelect.value = '';
                }
            };

            const actualizarEtiquetaCantidad = () => {
                const esAjuste = typeSelect.value === 'ajuste';
                quantityLabel.textContent = esAjuste ? 'Existencias contadas (saldo final)' : 'Cantidad del movimiento';
                quantityInput.min = esAjuste ? '0' : '1';
            };

            productSelect.addEventListener('change', actualizarRutaMovimiento);
            productSelect.addEventListener('change', actualizarVariantesDisponibles);
            warehouseSelect.addEventListener('change', actualizarRutaMovimiento);
            typeSelect.addEventListener('change', actualizarEtiquetaCantidad);
            actualizarRutaMovimiento();
            actualizarVariantesDisponibles();
            actualizarEtiquetaCantidad();

            page.querySelectorAll('[data-open-inventory-movement]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (button.dataset.inventoryProduct) {
                        productSelect.value = button.dataset.inventoryProduct;
                    }
                    if (button.dataset.movementType) {
                        typeSelect.value = button.dataset.movementType;
                    }
                    actualizarVariantesDisponibles();
                    actualizarRutaMovimiento();
                    actualizarEtiquetaCantidad();
                    dialog.showModal();
                });
            });

            document.querySelectorAll('[data-close-inventory-movement]').forEach((button) => button.addEventListener('click', () => dialog.close()));
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            page.querySelectorAll('[data-open-inventory-detail]').forEach((button) => button.addEventListener('click', () => {
                const row = button.closest('tr');
                const details = detailDialog.querySelector('[data-inventory-detail-fields]');
                const headings = [...row.closest('table').querySelectorAll('thead th')];
                const cells = [...row.querySelectorAll('td')];
                details.replaceChildren();
                cells.slice(0, headings.length - 1).forEach((cell, index) => {
                    const wrapper = document.createElement('div');
                    const term = document.createElement('dt');
                    const description = document.createElement('dd');
                    term.textContent = headings[index]?.textContent.trim() ?? `Dato ${index + 1}`;
                    description.textContent = cell.textContent.trim();
                    wrapper.append(term, description);
                    details.append(wrapper);
                });
                detailDialog.showModal();
            }));
            page.querySelectorAll('[data-open-movement-detail]').forEach((button) => button.addEventListener('click', () => {
                const details = detailDialog.querySelector('[data-inventory-detail-fields]');
                const campos = [
                    ['Tipo', button.dataset.tipo],
                    ['Cantidad', button.dataset.cantidad],
                    ['Fecha', button.dataset.fecha],
                    ['Usuario', button.dataset.usuario],
                    ['Producto', button.dataset.producto],
                    ['Variante', button.dataset.variante],
                    ['Almacén', button.dataset.almacen],
                    ['Motivo', button.dataset.notas],
                    ['Documento', button.dataset.documento],
                ];
                details.replaceChildren();
                campos.forEach(([etiqueta, valor]) => {
                    const wrapper = document.createElement('div');
                    const term = document.createElement('dt');
                    const description = document.createElement('dd');
                    term.textContent = etiqueta;
                    description.textContent = valor || '—';
                    wrapper.append(term, description);
                    details.append(wrapper);
                });
                detailDialog.showModal();
            }));
            detailDialog.querySelectorAll('[data-close-inventory-detail]').forEach((button) => button.addEventListener('click', () => detailDialog.close()));
            detailDialog.addEventListener('click', (event) => {
                if (event.target === detailDialog) {
                    detailDialog.close();
                }
            });
            <?php if($errors->any()): ?>
                dialog.showModal();
            <?php endif; ?>
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>