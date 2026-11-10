<?php $__env->startSection('title', 'Categorías'); ?>
<?php $__env->startSection('body_class', 'modulo-categorias'); ?>

<?php
    $categoryIcon = static function (string $name): string {
        $normalized = mb_strtolower($name);

        return match (true) {
            str_contains($normalized, 'audio') => 'bi-headphones',
            str_contains($normalized, 'celular') || str_contains($normalized, 'smart') => 'bi-phone',
            str_contains($normalized, 'comput') || str_contains($normalized, 'laptop') => 'bi-laptop',
            str_contains($normalized, 'video') || str_contains($normalized, 'juego') => 'bi-controller',
            str_contains($normalized, 'wear') || str_contains($normalized, 'reloj') => 'bi-smartwatch',
            str_contains($normalized, 'hogar') => 'bi-house-heart',
            default => 'bi-box-seam',
        };
    };
$categoryQuery = request()->query();
?>

<?php $__env->startSection('content'); ?>
    <section class="categorias-page" data-categorias-page>
        <header class="categorias-hero">
            <div>
                <span class="categorias-eyebrow">Catálogo</span>
                <h1>Categorías <i class="bi bi-diagram-3" aria-hidden="true"></i></h1>
                <p>Organiza tus productos en categorías para una mejor gestión.</p>
            </div>
            <div class="categorias-actions">
                <details class="productos-export-menu categorias-export-menu">
                    <summary><i class="bi bi-download" aria-hidden="true"></i><span>Exportar</span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    <div class="productos-export-options categorias-export-options">
                        <a href="<?php echo e(route('management.categories.export', ['format' => 'pdf'] + $categoryQuery)); ?>"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span>Exportar PDF<small>Documento imprimible</small></span></a>
                        <a href="<?php echo e(route('management.categories.export', ['format' => 'excel'] + $categoryQuery)); ?>"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span>Exportar Excel<small>Archivo compatible .xlsx</small></span></a>
                        <a href="<?php echo e(route('management.categories.export', ['format' => 'csv'] + $categoryQuery)); ?>"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span>Exportar CSV<small>Datos delimitados</small></span></a>
                    </div>
                </details>
                <button class="categorias-primary-button" type="button" data-open-category-create><i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva categoría</button>
            </div>
        </header>

        <div class="categorias-kpi-grid">
            <article class="categorias-kpi categorias-kpi--blue">
                <span class="categorias-kpi-icon"><i class="bi bi-layers" aria-hidden="true"></i></span>
                <div><small>Total de categorías</small><strong><?php echo e(number_format($totalCategories)); ?></strong><span>Activas en el sistema</span></div>
            </article>
            <article class="categorias-kpi categorias-kpi--green">
                <span class="categorias-kpi-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                <div><small>Categorías activas</small><strong><?php echo e(number_format($activeCategories)); ?></strong><span>Disponibles para productos</span></div>
            </article>
            <article class="categorias-kpi categorias-kpi--purple">
                <span class="categorias-kpi-icon"><i class="bi bi-eye-slash" aria-hidden="true"></i></span>
                <div><small>Categorías inactivas</small><strong><?php echo e(number_format($inactiveCategories)); ?></strong><span>No visibles en catálogo</span></div>
            </article>
            <article class="categorias-kpi categorias-kpi--violet">
                <span class="categorias-kpi-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span>
                <div><small>Productos asignados</small><strong><?php echo e(number_format($assignedProducts)); ?></strong><span>En todas las categorías</span></div>
            </article>
        </div>

        <form class="categorias-filters" method="get" action="<?php echo e(route('management.categories')); ?>">
            <label class="categorias-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar categorías</span><input type="search" name="buscar" value="<?php echo e($filters['buscar'] ?? ''); ?>" placeholder="Buscar categorías..."></label>
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activa" <?php if (($filters['estado'] ?? null) === 'activa') {
                echo 'selected';
            } ?>>Activas</option><option value="inactiva" <?php if (($filters['estado'] ?? null) === 'inactiva') {
                echo 'selected';
            } ?>>Inactivas</option></select></label>
            <label><span>Ordenar por</span><select name="orden"><option value="nombre_asc" <?php if (($filters['orden'] ?? 'nombre_asc') === 'nombre_asc') {
                echo 'selected';
            } ?>>Nombre (A-Z)</option><option value="nombre_desc" <?php if (($filters['orden'] ?? '') === 'nombre_desc') {
                echo 'selected';
            } ?>>Nombre (Z-A)</option><option value="reciente" <?php if (($filters['orden'] ?? '') === 'reciente') {
                echo 'selected';
            } ?>>Más recientes</option></select></label>
            <a class="categorias-clear-button" href="<?php echo e(route('management.categories')); ?>"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> Limpiar filtros</a>
        </form>

        <section class="categorias-table-card">
            <div class="categorias-table-wrap">
                <table class="categorias-table">
                    <thead>
                        <tr><th>#</th><th>Imagen</th><th>Nombre</th><th>Descripción</th><th>Productos</th><th>Estado</th><th>Fecha de creación</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true;
$__currentLoopData = $categories;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $category) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e((($categories->currentPage() - 1) * $categories->perPage()) + $loop->iteration); ?></td>
                                <td><span class="categorias-image"><i class="bi <?php echo e($categoryIcon($category->nombre)); ?>" aria-hidden="true"></i></span></td>
                                <td class="categorias-name"><strong><?php echo e($category->nombre); ?></strong></td>
                                <td class="categorias-description"><?php echo e($category->descripcion ?: 'Sin descripción registrada.'); ?></td>
                                <td><span class="categorias-products-count"><?php echo e(number_format($category->products_count)); ?></span></td>
                                <td><span class="categorias-status categorias-status--<?php echo e($category->activo ? 'active' : 'inactive'); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($category->activo ? 'Activa' : 'Inactiva'); ?></span></td>
                                <td class="categorias-date"><?php echo e($category->creado_en?->format('d/m/Y') ?: '—'); ?><small><?php echo e($category->creado_en?->format('H:i')); ?></small></td>
                                <td class="categorias-row-actions">
                                    <details class="categorias-edit-details" data-category-dialog="editar-categoria-<?php echo e($category->id); ?>">
                                        <summary class="categorias-icon-button" title="Editar categoría" aria-label="Editar <?php echo e($category->nombre); ?>"><i class="bi bi-pencil" aria-hidden="true"></i></summary>
                                        <div class="categorias-edit-popover"><?php echo $__env->make('gestion.inventario.categorias.editar', ['category' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?></div>
                                    </details>
                                    <button class="categorias-icon-button" type="button" title="Ver información" aria-label="Ver información de <?php echo e($category->nombre); ?>" data-open-category-view data-category-view-dialog="ver-categoria-<?php echo e($category->id); ?>"><i class="bi bi-eye" aria-hidden="true"></i></button>
                                    <?php if ($category->activo) { ?>
                                        <form method="post" action="<?php echo e(route('management.categories.deactivate', $category)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button class="categorias-icon-button categorias-icon-button--danger" type="submit" title="Desactivar categoría" aria-label="Desactivar <?php echo e($category->nombre); ?>" data-confirm="¿Desactivar esta categoría?"><i class="bi bi-trash3" aria-hidden="true"></i></button>
                                        </form>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                            <?php if (! ($filters['buscar'] || $filters['estado'])) { ?>
                                <tr><td class="categorias-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay categorías registradas.</strong><span>Registra la primera categoría para organizar tus productos.</span></td></tr>
                            <?php } else { ?>
                            <tr><td class="categorias-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay categorías que coincidan con los filtros.</strong><span>Prueba con otros criterios de búsqueda.</span></td></tr>
                            <?php } ?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <footer class="categorias-pagination">
                <span>Mostrando <?php echo e($categories->firstItem() ?? 0); ?> a <?php echo e($categories->lastItem() ?? 0); ?> de <?php echo e($categories->total()); ?> categorías</span>
                <div class="categorias-pagination-controls"><?php echo e($categories->onEachSide(1)->links()); ?><span class="categorias-per-page">7 por página <i class="bi bi-chevron-down" aria-hidden="true"></i></span></div>
            </footer>
        </section>
    </section>

    <?php $__currentLoopData = $categories;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $category) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
        <dialog class="categorias-dialog categorias-edit-dialog" id="editar-categoria-<?php echo e($category->id); ?>" aria-labelledby="categoria-editar-titulo-<?php echo e($category->id); ?>">
            <div class="categorias-dialog-card">
                <header class="categorias-dialog-header"><div><span class="categorias-eyebrow">Catálogo</span><h2 id="categoria-editar-titulo-<?php echo e($category->id); ?>">Editar categoría</h2><p>Actualiza la información de <?php echo e($category->nombre); ?>.</p></div><button class="categorias-dialog-close" type="button" data-close-category-edit aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <?php echo $__env->make('gestion.inventario.categorias.editar', ['category' => $category], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </dialog>
    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>

    <?php $__currentLoopData = $categories;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $category) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
        <dialog class="categorias-dialog categorias-view-dialog" id="ver-categoria-<?php echo e($category->id); ?>" aria-labelledby="categoria-detalle-titulo-<?php echo e($category->id); ?>">
            <div class="categorias-dialog-card">
                <header class="categorias-dialog-header"><div><span class="categorias-eyebrow">Catálogo</span><h2 id="categoria-detalle-titulo-<?php echo e($category->id); ?>">Información de categoría</h2><p>Resumen de <?php echo e($category->nombre); ?>.</p></div><button class="categorias-dialog-close" type="button" data-close-category-view aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="categorias-view-body">
                    <div class="categorias-view-heading"><span class="categorias-view-icon"><i class="bi <?php echo e($categoryIcon($category->nombre)); ?>" aria-hidden="true"></i></span><div><h3><?php echo e($category->nombre); ?></h3><span class="categorias-status categorias-status--<?php echo e($category->activo ? 'active' : 'inactive'); ?>"><i class="bi bi-circle-fill" aria-hidden="true"></i><?php echo e($category->activo ? 'Activa' : 'Inactiva'); ?></span></div></div>
                    <div class="categorias-view-grid"><div><small>Productos asignados</small><strong><?php echo e(number_format($category->products_count)); ?></strong></div><div><small>Fecha de creación</small><strong><?php echo e($category->creado_en?->format('d/m/Y H:i') ?: 'Sin fecha'); ?></strong></div></div>
                    <section class="categorias-view-description"><small>Descripción</small><p><?php echo e($category->descripcion ?: 'Sin descripción registrada.'); ?></p></section>
                </div>
                <footer class="categorias-form-actions categorias-view-actions"><button class="categorias-button categorias-button--secondary" type="button" data-close-category-view>Cerrar</button><a class="categorias-button categorias-button--primary" href="<?php echo e(route('management.products', ['categoria' => $category->id])); ?>"><i class="bi bi-box-seam" aria-hidden="true"></i> Ver productos</a></footer>
            </div>
        </dialog>
    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>

    <dialog class="categorias-dialog" id="nueva-categoria" aria-labelledby="categoria-crear-titulo">
        <div class="categorias-dialog-card">
            <header class="categorias-dialog-header"><div><span class="categorias-eyebrow">Catálogo</span><h2 id="categoria-crear-titulo">Nueva categoría</h2><p>Registra una categoría para organizar tus productos.</p></div><button class="categorias-dialog-close" type="button" data-close-category-create aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            <?php echo $__env->make('gestion.inventario.categorias.crear', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </dialog>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        (() => {
            const page = document.querySelector('[data-categorias-page]');
            const dialog = document.querySelector('#nueva-categoria');

            if (!page || !dialog) {
                return;
            }

            page.querySelectorAll('[data-open-category-create]').forEach((button) => {
                button.addEventListener('click', () => dialog.showModal());
            });
            dialog.querySelector('[data-close-category-create]')?.addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) {
                    dialog.close();
                }
            });

            page.querySelectorAll('[data-category-dialog]').forEach((trigger) => {
                const editDialog = document.getElementById(trigger.dataset.categoryDialog);

                trigger.querySelector('summary')?.addEventListener('click', (event) => {
                    event.preventDefault();
                    editDialog?.showModal();
                });
            });
            document.querySelectorAll('.categorias-edit-dialog').forEach((editDialog) => {
                editDialog.querySelector('[data-close-category-edit]')?.addEventListener('click', () => editDialog.close());
                editDialog.addEventListener('click', (event) => {
                    if (event.target === editDialog) {
                        editDialog.close();
                    }
                });
            });
            page.querySelectorAll('[data-open-category-view]').forEach((button) => {
                const viewDialog = document.getElementById(button.dataset.categoryViewDialog);

                button.addEventListener('click', () => viewDialog?.showModal());
            });
            document.querySelectorAll('.categorias-view-dialog').forEach((viewDialog) => {
                viewDialog.querySelectorAll('[data-close-category-view]').forEach((button) => button.addEventListener('click', () => viewDialog.close()));
                viewDialog.addEventListener('click', (event) => {
                    if (event.target === viewDialog) {
                        viewDialog.close();
                    }
                });
            });

            <?php if ($errors->any()) { ?>
                dialog.showModal();
            <?php } ?>
        })();
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>