<?php
use Illuminate\Support\Carbon;

$__env->startSection('title', 'Proveedores'); ?>
<?php $__env->startSection('body_class', 'modulo-proveedores'); ?>

<?php $__env->startSection('content'); ?>
    <?php
        $supplierCategories = static function ($supplier) {
            return $supplier->products
                ->map(static fn ($product) => $product->category?->nombre)
                ->filter()
                ->unique()
                ->values();
        };
$selectedCategories = $selectedSupplier ? $supplierCategories($selectedSupplier) : collect();
$purchaseCount = $selectedSupplier?->purchases_count ?? 0;
$receivedPurchases = (int) ($purchaseStatusCounts->firstWhere('estado', 'Recibida')?->total ?? 0);
$receivedPercent = $purchaseCount > 0 ? min(100, round(($receivedPurchases / $purchaseCount) * 100)) : 0;
?>

    <section class="proveedores-page" data-proveedores-page>
        <header class="proveedores-hero">
            <div>
                <span class="proveedores-eyebrow">Compras y logística</span>
                <h1>Proveedores <i class="bi bi-truck" aria-hidden="true"></i></h1>
                <p>Administra los proveedores vinculados al catálogo y a las compras.</p>
            </div>
            <div class="proveedores-hero-actions" aria-label="Acciones de proveedores">
                <details class="proveedores-export-menu"><summary class="proveedores-toolbar-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="proveedores-export-options"><a href="<?php echo e(route('management.suppliers.export', ['format' => 'pdf'] + request()->query())); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="<?php echo e(route('management.suppliers.export', ['format' => 'excel'] + request()->query())); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="<?php echo e(route('management.suppliers.export', ['format' => 'csv'] + request()->query())); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
                <button class="proveedores-primary-button" type="button" data-open-supplier-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo proveedor</button>
            </div>
        </header>

        <section class="proveedores-kpi-grid" aria-label="Resumen de proveedores">
            <article class="proveedores-kpi proveedores-kpi--blue">
                <span class="proveedores-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
                <div><small>Total de proveedores</small><strong><?php echo e(number_format($totalSuppliers)); ?></strong><span>Registrados en el sistema</span></div>
            </article>
            <article class="proveedores-kpi proveedores-kpi--green">
                <span class="proveedores-kpi-icon"><i class="bi bi-check-circle-fill" aria-hidden="true"></i></span>
                <div><small>Proveedores activos</small><strong><?php echo e(number_format($activeSuppliers)); ?></strong><span><?php echo e($totalSuppliers > 0 ? number_format(($activeSuppliers / $totalSuppliers) * 100, 0) : 0); ?>% del total</span></div>
            </article>
            <article class="proveedores-kpi proveedores-kpi--purple">
                <span class="proveedores-kpi-icon"><i class="bi bi-cart-check-fill" aria-hidden="true"></i></span>
                <div><small>Órdenes vinculadas</small><strong><?php echo e(number_format($linkedOrders)); ?></strong><span>Compras registradas</span></div>
            </article>
            <article class="proveedores-kpi proveedores-kpi--orange">
                <span class="proveedores-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Total comprado</small><strong>S/ <?php echo e(number_format($purchasedTotal, 2)); ?></strong><span>En órdenes activas</span></div>
            </article>
        </section>

        <nav class="proveedores-tabs" aria-label="Secciones de proveedores">
            <span class="proveedores-tab proveedores-tab--active">Lista de proveedores</span>
            <span class="proveedores-tab">Análisis y métricas</span>
        </nav>

        <form class="proveedores-toolbar" method="get" action="<?php echo e(route('management.suppliers')); ?>">
            <label class="proveedores-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span class="visually-hidden">Buscar proveedores</span>
                <input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar por nombre, RUC, categoría o ciudad...">
            </label>
            <label>
                <span class="visually-hidden">Filtrar por categoría</span>
                <select name="categoria">
                    <option value="">Todas las categorías</option>
                    <?php $__currentLoopData = $categories;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $category) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($category->id); ?>" <?php if (($filters['categoria'] ?? null) === $category->id) {
                            echo 'selected';
                        } ?>><?php echo e($category->nombre); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <label>
                <span class="visually-hidden">Filtrar por estado</span>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    <option value="activo" <?php if (($filters['estado'] ?? null) === 'activo') {
                        echo 'selected';
                    } ?>>Activos</option>
                    <option value="inactivo" <?php if (($filters['estado'] ?? null) === 'inactivo') {
                        echo 'selected';
                    } ?>>Inactivos</option>
                </select>
            </label>
            <label>
                <span class="visually-hidden">Filtrar por ciudad</span>
                <select name="ciudad">
                    <option value="">Todas las ciudades</option>
                    <?php $__currentLoopData = $cities;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $city) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($city); ?>" <?php if (($filters['ciudad'] ?? null) === $city) {
                            echo 'selected';
                        } ?>><?php echo e($city); ?></option>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </select>
            </label>
            <button class="proveedores-toolbar-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i><span>Filtros</span></button>
            <?php if (($filters['buscar'] ?? null) || ($filters['categoria'] ?? null) || ($filters['estado'] ?? null) || ($filters['ciudad'] ?? null)) { ?>
                <a class="proveedores-clear-button" href="<?php echo e(route('management.suppliers')); ?>">Limpiar filtros</a>
            <?php } ?>
        </form>

        <div class="proveedores-main-grid">
            <section class="proveedores-list-card">
                <header class="proveedores-card-header">
                    <div>
                        <h2>Proveedores (<?php echo e(number_format($suppliers->total())); ?>) <i class="bi bi-chevron-down" aria-hidden="true"></i></h2>
                        <p>Mostrando <?php echo e($suppliers->firstItem() ?? 0); ?> a <?php echo e($suppliers->lastItem() ?? 0); ?> proveedores</p>
                    </div>
                </header>

                <div class="proveedores-table-wrap">
                    <table class="proveedores-table">
                        <thead>
                            <tr><th><span class="proveedores-checkbox" aria-hidden="true"></span></th><th>#</th><th>Proveedor</th><th>RUC</th><th>Categoría</th><th>Ciudad</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true;
$__currentLoopData = $suppliers;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $supplier) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                                <?php
            $categoriesForSupplier = $supplierCategories($supplier);
    $supplierInitial = mb_strtoupper(mb_substr($supplier->nombre, 0, 1));
    $isSelected = $selectedSupplier?->id === $supplier->id;
    ?>
                                <tr class="<?php echo e($isSelected ? 'is-selected' : ''); ?>">
                                    <td><span class="proveedores-checkbox" aria-hidden="true"></span></td>
                                    <td class="proveedores-index"><?php echo e($suppliers->firstItem() + $loop->index); ?></td>
                                    <td>
                                        <a class="proveedores-identity" href="<?php echo e(request()->fullUrlWithQuery(['proveedor' => $supplier->id])); ?>">
                                            <span class="proveedores-avatar"><?php echo e($supplierInitial); ?></span>
                                            <span><strong><?php echo e($supplier->nombre); ?></strong><small><?php echo e($supplier->correo); ?></small></span>
                                        </a>
                                    </td>
                                    <td class="proveedores-muted"><?php echo e($supplier->ruc); ?></td>
                                    <td>
                                        <span class="proveedores-category-list">
                                            <?php $__empty_2 = true;
    $__currentLoopData = $categoriesForSupplier->take(2);
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $categoryName) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop();
        $__empty_2 = false; ?>
                                                <b><?php echo e($categoryName); ?></b>
                                            <?php } $__env->popLoop();
    $loop = $__env->getLastLoop();
    if ($__empty_2) { ?>
                                                <span>Sin categoría</span>
                                            <?php } ?>
                                        </span>
                                    </td>
                                    <td class="proveedores-muted"><?php echo e($supplier->ciudad ?: 'Sin ciudad'); ?></td>
                                    <td><span class="proveedores-status proveedores-status--<?php echo e($supplier->activo ? 'active' : 'inactive'); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($supplier->activo ? 'Activo' : 'Inactivo'); ?></span></td>
                                    <td class="proveedores-actions-cell">
                                        <a class="proveedores-action" href="<?php echo e(request()->fullUrlWithQuery(['proveedor' => $supplier->id])); ?>" title="Ver proveedor"><i class="bi bi-eye" aria-hidden="true"></i></a>
                                        <button class="proveedores-action" type="button" data-open-supplier-edit="<?php echo e($supplier->id); ?>" title="Editar proveedor"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                        <?php if ($supplier->activo) { ?>
                                            <form method="post" action="<?php echo e(route('management.suppliers.deactivate', $supplier)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <?php echo method_field('DELETE'); ?>
                                                <button class="proveedores-action proveedores-action--danger" type="submit" data-confirm="¿Desactivar este proveedor?" title="Desactivar proveedor"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                            </form>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                                <tr><td colspan="8" class="proveedores-empty"><i class="bi bi-truck" aria-hidden="true"></i><strong>No hay proveedores para mostrar.</strong><span>Prueba con otros filtros o registra un nuevo proveedor.</span></td></tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>

                <footer class="proveedores-pagination">
                    <span>Mostrando <?php echo e($suppliers->firstItem() ?? 0); ?> a <?php echo e($suppliers->lastItem() ?? 0); ?> de <?php echo e(number_format($suppliers->total())); ?> proveedores</span>
                    <div class="proveedores-pagination-controls">
                        <?php echo e($suppliers->onEachSide(1)->links()); ?>

                        <form method="get" action="<?php echo e(route('management.suppliers')); ?>" class="proveedores-per-page">
                            <?php $__currentLoopData = request()->except(['page', 'por_pagina']);
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $key => $value) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                                <?php if (is_scalar($value)) { ?><input type="hidden" name="<?php echo e($key); ?>" value="<?php echo e($value); ?>"><?php } ?>
                            <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                            <label for="proveedores-por-pagina">Proveedores por página</label>
                            <select id="proveedores-por-pagina" name="por_pagina" onchange="this.form.submit()">
                                <?php $__currentLoopData = [10, 20, 50];
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $option) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($option); ?>" <?php if (($filters['por_pagina'] ?? 10) === $option) {
                                        echo 'selected';
                                    } ?>><?php echo e($option); ?></option>
                                <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                    </div>
                </footer>
            </section>

            <aside class="proveedores-detail-column">
                <section class="proveedores-detail-card">
                    <header class="proveedores-card-header">
                        <h2>Detalle del proveedor <i class="bi bi-chevron-up" aria-hidden="true"></i></h2>
                    </header>
                    <?php if ($selectedSupplier) { ?>
                        <?php ($selectedInitial = mb_strtoupper(mb_substr($selectedSupplier->nombre, 0, 1))); ?>
                        <div class="proveedores-detail-identity">
                            <span class="proveedores-detail-avatar"><?php echo e($selectedInitial); ?></span>
                            <div><strong><?php echo e($selectedSupplier->nombre); ?></strong><span>RUC: <?php echo e($selectedSupplier->ruc); ?></span><small>Proveedor vinculado al catálogo.</small></div>
                            <span class="proveedores-status proveedores-status--<?php echo e($selectedSupplier->activo ? 'active' : 'inactive'); ?>"><?php echo e($selectedSupplier->activo ? 'Activo' : 'Inactivo'); ?></span>
                        </div>
                        <div class="proveedores-detail-actions">
                            <button class="proveedores-toolbar-button" type="button" data-open-supplier-edit="<?php echo e($selectedSupplier->id); ?>"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</button>
                            <a class="proveedores-more-button" href="#proveedores-info" aria-label="Ver información"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></a>
                        </div>
                        <nav class="proveedores-detail-tabs" aria-label="Detalle del proveedor"><span class="is-active">Información</span><span>Estadísticas</span><span>Órdenes</span><span>Productos</span></nav>
                        <div class="proveedores-detail-section" id="proveedores-info">
                            <h3>Información general</h3>
                            <dl class="proveedores-info-list">
                                <div><dt><i class="bi bi-person" aria-hidden="true"></i> Contacto</dt><dd>No registrado</dd></div>
                                <div><dt><i class="bi bi-envelope" aria-hidden="true"></i> Correo</dt><dd><?php echo e($selectedSupplier->correo); ?></dd></div>
                                <div><dt><i class="bi bi-telephone" aria-hidden="true"></i> Teléfono</dt><dd><?php echo e($selectedSupplier->telefono ?: 'No registrado'); ?></dd></div>
                                <div><dt><i class="bi bi-geo-alt" aria-hidden="true"></i> Ciudad</dt><dd><?php echo e($selectedSupplier->ciudad ?: 'No registrada'); ?></dd></div>
                                <div><dt><i class="bi bi-box-seam" aria-hidden="true"></i> Categorías</dt><dd class="proveedores-detail-tags"><?php $__empty_1 = true;
                        $__currentLoopData = $selectedCategories->take(3);
                        $__env->addLoop($__currentLoopData);
                        foreach ($__currentLoopData as $categoryName) {
                            $__env->incrementLoopIndices();
                            $loop = $__env->getLastLoop();
                            $__empty_1 = false; ?><span><?php echo e($categoryName); ?></span><?php } $__env->popLoop();
                        $loop = $__env->getLastLoop();
                        if ($__empty_1) { ?><span>Sin categorías</span><?php } ?></dd></div>
                            </dl>
                        </div>
                        <div class="proveedores-performance">
                            <header><h3>Rendimiento de compras</h3><small>Datos registrados</small></header>
                            <div class="proveedores-performance-grid">
                                <div class="proveedores-progress-ring" style="--proveedores-progress: <?php echo e($receivedPercent); ?>%"><strong><?php echo e($receivedPercent); ?>%</strong><span>Recibidas</span></div>
                                <div class="proveedores-performance-stats">
                                    <div><span><i class="bi bi-file-earmark-check-fill is-blue" aria-hidden="true"></i> Órdenes totales</span><b><?php echo e(number_format($purchaseCount)); ?></b></div>
                                    <div><span><i class="bi bi-cash-coin is-green" aria-hidden="true"></i> Total comprado</span><b>S/ <?php echo e(number_format((float) ($selectedSupplier->purchases_total ?? 0), 2)); ?></b></div>
                                    <div><span><i class="bi bi-calendar3 is-purple" aria-hidden="true"></i> Última orden</span><b><?php echo e($selectedSupplier->latest_purchase_date ? Carbon::parse($selectedSupplier->latest_purchase_date)->format('d/m/Y') : 'Sin compras'); ?></b></div>
                                </div>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="proveedores-detail-empty"><i class="bi bi-truck" aria-hidden="true"></i><strong>Selecciona un proveedor</strong><span>Su información aparecerá aquí.</span></div>
                    <?php } ?>
                </section>
            </aside>
        </div>

        <div class="proveedores-bottom-grid">
            <section class="proveedores-secondary-card">
                <header class="proveedores-card-header"><div><h2><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i> Productos sin existencias <span class="proveedores-alert-count"><?php echo e($lowStockProducts->count()); ?></span></h2><p>Productos con saldo físico igual a cero.</p></div><a href="<?php echo e(route('management.inventory', ['estado' => 'sin_existencias'])); ?>">Ver todas</a></header>
                <div class="proveedores-alert-list">
                    <?php $__empty_1 = true;
$__currentLoopData = $lowStockProducts;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $product) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                        <div class="proveedores-alert-row"><span class="proveedores-alert-icon"><i class="bi bi-exclamation-triangle-fill" aria-hidden="true"></i></span><span><strong>Sin existencias: <?php echo e($product->nombre); ?></strong><small><?php echo e(number_format((float) $product->existencias_alcance)); ?> unidades · <?php echo e($product->suppliers->first()?->nombre ?: 'Sin proveedor'); ?></small></span><time>Reponer</time><a href="<?php echo e(route('management.inventory', ['buscar' => $product->nombre])); ?>">Revisar</a></div>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                        <div class="proveedores-secondary-empty"><i class="bi bi-shield-check" aria-hidden="true"></i><strong>Sin alertas de abastecimiento</strong><span>El inventario de proveedores está al día.</span></div>
                    <?php } ?>
                </div>
            </section>

            <section class="proveedores-secondary-card">
                <header class="proveedores-card-header"><div><h2><i class="bi bi-clock-history" aria-hidden="true"></i> Actividad reciente</h2><p>Últimos movimientos de proveedores y compras.</p></div><a href="<?php echo e(route('management.audit')); ?>">Ver todas</a></header>
                <div class="proveedores-activity-list">
                    <?php $__empty_1 = true;
$__currentLoopData = $recentActivity;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $activity) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                        <div class="proveedores-activity-row"><span class="proveedores-activity-icon proveedores-activity-icon--<?php echo e($activity['color']); ?>"><i class="bi bi-<?php echo e($activity['icon']); ?>" aria-hidden="true"></i></span><span><strong><?php echo e($activity['title']); ?></strong><small><?php echo e($activity['detail']); ?></small></span><time><?php echo e($activity['date']?->diffForHumans()); ?></time></div>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                        <div class="proveedores-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los movimientos aparecerán aquí.</span></div>
                    <?php } ?>
                </div>
            </section>
        </div>
    </section>

    <dialog class="proveedores-dialog" id="nuevo-proveedor" aria-labelledby="proveedor-crear-titulo">
        <div class="proveedores-dialog-card">
            <header class="proveedores-dialog-header"><div><span>Compras y logística</span><h2 id="proveedor-crear-titulo">Registrar proveedor</h2><p>Completa los datos del proveedor para vincularlo a tus compras.</p></div><button type="button" class="proveedores-dialog-close" data-close-supplier-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <div class="proveedores-dialog-body"><?php echo $__env->make('gestion.inventario.proveedores.crear', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
        </div>
    </dialog>

    <?php $__currentLoopData = $suppliers;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $supplier) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
        <dialog class="proveedores-dialog" id="editar-proveedor-<?php echo e($supplier->id); ?>" aria-labelledby="proveedor-editar-titulo-<?php echo e($supplier->id); ?>">
            <div class="proveedores-dialog-card">
                <header class="proveedores-dialog-header"><div><span>Compras y logística</span><h2 id="proveedor-editar-titulo-<?php echo e($supplier->id); ?>">Editar proveedor</h2><p>Actualiza la información de <?php echo e($supplier->nombre); ?>.</p></div><button type="button" class="proveedores-dialog-close" data-close-supplier-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="proveedores-dialog-body"><?php echo $__env->make('gestion.inventario.proveedores.editar', ['supplier' => $supplier], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
            </div>
        </dialog>
    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const page = document.querySelector('[data-proveedores-page]');
            const openDialog = (dialog) => {
                if (dialog && !dialog.open && typeof dialog.showModal === 'function') dialog.showModal();
            };
            page.querySelectorAll('[data-open-supplier-create]').forEach((button) => {
                button.addEventListener('click', () => openDialog(document.querySelector('#nuevo-proveedor')));
            });
            page.querySelectorAll('[data-open-supplier-edit]').forEach((button) => {
                button.addEventListener('click', () => openDialog(document.querySelector(`#editar-proveedor-${button.dataset.openSupplierEdit}`)));
            });
            document.querySelectorAll('[data-close-supplier-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            document.querySelectorAll('.proveedores-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            }));

            <?php if ($errors->any()) { ?>
                openDialog(document.querySelector('#nuevo-proveedor'));
            <?php } ?>
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>