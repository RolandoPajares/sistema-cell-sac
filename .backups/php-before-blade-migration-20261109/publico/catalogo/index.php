<?php
use Illuminate\Support\Str;

$__env->startSection('body_class', 'modulo-catalog'); ?>
<?php $__env->startSection('title', 'Catálogo | MD Technology Cell'); ?>

<?php $__env->startSection('content'); ?>
<?php
$baseFilters = request()->except('page');
$selectedCategory = request('categoria');
$selectedBrand = request('marca');
?>
<section class="catalog-page">
    <div class="catalog-reference-hero">
        <div class="container catalog-reference-hero__inner">
            <div><span class="eyebrow">TECHNOLOGY CELL</span><h1>Encuentra tu próximo <span>celular ideal</span></h1><p>Las mejores marcas, precios vigentes y garantía real.</p><a class="btn btn-primary" href="#productos">Explorar catálogo <i class="bi bi-arrow-right"></i></a></div>
        </div>
    </div>
    <div class="container">
        <nav class="catalog-breadcrumbs" aria-label="Ruta de navegación">
            <a href="<?php echo e(route('home')); ?>">Inicio</a><span aria-hidden="true">›</span><span aria-current="page">Catálogo</span>
        </nav>

        <div class="catalog-layout">
            <aside class="catalog-sidebar" aria-label="Filtros del catálogo">
                <section class="catalog-filter-section">
                    <h2>Categorías</h2>
                    <nav class="catalog-filter-list" aria-label="Categorías">
                        <a class="<?php echo e($selectedCategory ? '' : 'is-active'); ?>" href="<?php echo e(route('catalog.index', collect($baseFilters)->except('categoria')->all())); ?>">Todas las categorías</a>
                        <?php $__currentLoopData = $categories;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $category) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <a class="<?php echo e((string) $selectedCategory === (string) $category->id ? 'is-active' : ''); ?>" href="<?php echo e(route('catalog.index', array_merge($baseFilters, ['categoria' => $category->id]))); ?>"><?php echo e($category->nombre); ?></a>
                        <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                    </nav>
                </section>

                <section class="catalog-filter-section">
                    <h2>Marca</h2>
                    <nav class="catalog-filter-list" aria-label="Marcas">
                        <a class="<?php echo e($selectedBrand ? '' : 'is-active'); ?>" href="<?php echo e(route('catalog.index', collect($baseFilters)->except('marca')->all())); ?>">Todas las marcas</a>
                        <?php $__currentLoopData = $brands;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $brand) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <a class="<?php echo e($selectedBrand === $brand ? 'is-active' : ''); ?>" href="<?php echo e(route('catalog.index', array_merge($baseFilters, ['marca' => $brand]))); ?>"><?php echo e($brand); ?></a>
                        <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                    </nav>
                </section>

                <form class="catalog-price-filter" method="get" action="<?php echo e(route('catalog.index')); ?>">
                    <h2>Rango de precio</h2>
                    <input type="hidden" name="q" value="<?php echo e($query); ?>">
                    <input
                        type="hidden"
                        name="marca"
                        value="<?php echo e($selectedBrand); ?>">
                    <input
                        type="hidden"
                        name="categoria"
                        value="<?php echo e($selectedCategory); ?>">
                    <input
                        type="hidden"
                        name="orden"
                        value="<?php echo e(request('orden')); ?>">
                    <label for="catalog-min-price">Mínimo</label>
                    <input
                        class="input"
                        id="catalog-min-price"
                        name="min"
                        type="number"
                        min="0"
                        step="0.01"
                        value="<?php echo e(request('min')); ?>">
                    <label for="catalog-max-price">Máximo</label>
                    <input
                        class="input"
                        id="catalog-max-price"
                        name="max"
                        type="number"
                        min="0"
                        step="0.01"
                        value="<?php echo e(request('max')); ?>">
                    <button class="btn btn-ghost" type="submit">Aplicar</button>
                </form>
            </aside>

            <div class="catalog-content" id="productos">
                <div class="catalog-toolbar">
                    <div class="catalog-heading">
                        <h1><?php echo e($selectedCategory ? $categories->firstWhere('id', (int) $selectedCategory)?->nombre : 'Catálogo'); ?></h1>
                        <p>Encuentra el celular ideal para ti.</p>
                    </div>
                    <form id="catalog-search-form" class="catalog-search" method="get" action="<?php echo e(route('catalog.index')); ?>" role="search">
                        <input type="hidden" name="marca" value="<?php echo e($selectedBrand); ?>">
                        <input type="hidden" name="categoria" value="<?php echo e($selectedCategory); ?>">
                        <input type="hidden" name="min" value="<?php echo e(request('min')); ?>">
                        <input type="hidden" name="max" value="<?php echo e(request('max')); ?>">
                        <label class="visually-hidden" for="catalog-search-input">Buscar productos</label>
                        <input class="input" id="catalog-search-input" name="q"
                            value="<?php echo e($query); ?>"
                            placeholder="Buscar celulares, marcas o modelos...">
                        <button class="btn btn-primary" type="submit" aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                        <label class="visually-hidden" for="catalog-sort">Ordenar por</label>
                        <select id="catalog-sort" name="orden" data-catalog-sort>
                            <option value="nuevo" <?php if (request('orden') === 'nuevo') {
                                echo 'selected';
                            } ?>>Más recientes</option>
                            <option value="precio_asc" <?php if (request('orden') === 'precio_asc') {
                                echo 'selected';
                            } ?>>Menor precio</option>
                            <option value="precio_desc" <?php if (request('orden') === 'precio_desc') {
                                echo 'selected';
                            } ?>>Mayor precio</option>
                            <option value="nombre" <?php if (request('orden') === 'nombre') {
                                echo 'selected';
                            } ?>>Nombre</option>
                        </select>
                    </form>
                </div>

                <div class="product-grid catalog-product-grid">
                    <?php $__empty_1 = true;
$__currentLoopData = $products;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $product) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop();
    $__empty_1 = false; ?>
                    <?php
        $urlImagenPrincipal = $product->url_imagen_principal;
    $salePrice = (float) ($product->precio_oferta ?? $product->precio);
    $originalPrice = (float) ($product->precio_original ?? $product->precio);
    $discount = $originalPrice > $salePrice && $originalPrice > 0 ? round((1 - ($salePrice / $originalPrice)) * 100) : 0;
    ?>

                    <article class="product-card catalog-product-card">
                        <div class="product-art catalog-product-art">
                            <?php if ($product->etiqueta || $discount > 0) { ?><span class="badge"><?php echo e($product->etiqueta ?: 'Oferta'); ?></span><?php } ?>
                            <?php if ($urlImagenPrincipal) { ?><img class="catalog-product-photo" src="<?php echo e($urlImagenPrincipal); ?>" alt="<?php echo e($product->marca); ?> <?php echo e($product->nombre); ?>" loading="lazy"><?php } else { ?><div class="catalog-accessory-shape" role="img" aria-label="Imagen no disponible"><i class="bi bi-box-seam"></i></div><?php } ?>
                        </div>
                        <div class="product-body catalog-product-body">
                            <div class="catalog-product-meta"><span><?php echo e($product->marca); ?></span><span><?php echo e($product->category?->nombre ?: $product->categoria); ?></span></div>
                            <h2><?php echo e($product->nombre); ?></h2>
                            <div class="catalog-product-specs"><?php echo e(trim(($product->almacenamiento ? $product->almacenamiento.' · ' : '').Str::limit(strip_tags((string) $product->descripcion), 38))); ?></div>
                            <div class="catalog-product-pricing"><?php if ($discount > 0) { ?><div><del>S/ <?php echo e(number_format($originalPrice, 2)); ?></del><span>-<?php echo e($discount); ?>%</span></div><?php } ?></div>
                            <p class="price catalog-product-sale-price">S/ <?php echo e(number_format($salePrice, 2)); ?></p>
                            <div class="stock catalog-product-stock"><?php echo e($product->existencias_disponibles > 0 ? $product->existencias_disponibles.' unidades disponibles' : 'Agotado'); ?></div>
                            <div class="catalog-product-actions">
                                <?php if (auth()->guard()->check()) { ?>
                                <form action="<?php echo e(route('cart.add', $product)); ?>" method="post">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="cantidad" value="1">
                                    <button class="btn btn-primary catalog-buy-button" type="submit" <?php if ($product->existencias_disponibles <= 0) {
                                        echo 'disabled';
                                    } ?>>
                                        <i class="bi bi-cart3" aria-hidden="true"></i>
                                        <?php echo e($product->existencias_disponibles > 0 ? 'Comprar' : 'Agotado'); ?>

                                    </button>
                                </form>
                                <?php } else { ?>
                                <a class="btn btn-primary catalog-buy-button" href="<?php echo e(route('login')); ?>"><i class="bi bi-cart3" aria-hidden="true"></i> Comprar</a>
                                <?php } ?>
                                <a class="btn btn-ghost catalog-details-button" href="<?php echo e(route('catalog.show', $product)); ?>">Ver detalles</a>
                            </div>
                        </div>
                    </article>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop();
if ($__empty_1) { ?>
                    <div class="empty panel catalog-empty">No encontramos productos con esos filtros.</div>
                    <?php } ?>
                </div>
                <div class="pagination">
                    <?php echo e($products->onEachSide(1)->links('publico.catalogo.paginacion')); ?>

                </div>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>