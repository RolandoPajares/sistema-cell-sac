<?php $__env->startSection('body_class', 'modulo-producto'); ?>
<?php $__env->startSection('title', $product->nombre.' | MD Technology Cell'); ?>

<?php $__env->startSection('content'); ?>
<section class="product-page-light" data-product-detail data-product-id="<?php echo e($product->id); ?>" data-variant-data='<?php echo json_encode($variantData, 15, 512) ?>' data-authenticated="<?php echo e(auth()->check() ? '1' : '0'); ?>" data-login-url="<?php echo e(route('login')); ?>" data-favorite-url="<?php echo e(route('catalog.favorite', $product)); ?>" data-review-url="<?php echo e(route('catalog.review.store', $product)); ?>" data-favorite-initial="<?php echo e($isFavorite ? '1' : '0'); ?>" data-rating-initial="<?php echo e($userRating); ?>">
    <div class="container product-detail-container">
        <nav class="product-breadcrumbs" aria-label="Ruta de navegación">
            <a href="<?php echo e(route('home')); ?>"><i class="bi bi-house" aria-hidden="true"></i> Inicio</a><span aria-hidden="true">/</span>
            <a href="<?php echo e(route('catalog.index')); ?>">Catálogo</a><span aria-hidden="true">/</span>
            <a href="<?php echo e(route('catalog.index', ['categoria' => $product->categoria_id])); ?>"><?php echo e($categoryName); ?></a><span aria-hidden="true">/</span>
            <span aria-current="page"><?php echo e($product->nombre); ?></span>
        </nav>

        <div class="product-detail-modern">
            <div class="detail-gallery" aria-label="Imágenes del producto">
                <div class="gallery-thumbs" data-gallery-thumbs <?php if (! $primaryImage) { ?> hidden <?php } ?>>
                    <?php $__currentLoopData = $imageItems;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $index => $image) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
                        <button class="gallery-thumb <?php echo e($index === 0 ? 'active' : ''); ?>" type="button" data-src="<?php echo e($image['url']); ?>" aria-label="Ver imagen <?php echo e($index + 1); ?>" <?php if ($index === 0) { ?> aria-current="true" <?php } ?>><img src="<?php echo e($image['url']); ?>" alt="" loading="lazy"></button>
                    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
                </div>
                <div class="detail-main-image" data-zoom-source>
                    <?php if ($primaryImage) { ?><img data-main-product-image src="<?php echo e($primaryImage); ?>" alt="<?php echo e($product->marca); ?> <?php echo e($product->nombre); ?>"><?php } ?>
                    <div class="detail-product-placeholder detail-product-placeholder--<?php echo e($visualType); ?>" data-image-placeholder <?php if ($primaryImage) { ?> hidden <?php } ?> role="img" aria-label="Imagen no disponible"><span class="detail-product-icon"><strong><?php echo e($product->marca); ?></strong><i class="bi <?php echo e($visualType === 'audio' ? 'bi-headphones' : ($visualType === 'celular' ? 'bi-phone' : 'bi-box-seam')); ?>" aria-hidden="true"></i></span><span>Imagen no disponible</span></div>
                    <div class="detail-gallery-actions"><button type="button" class="detail-icon-button" data-favorite-toggle aria-pressed="false" title="Añadir a favoritos"><i class="bi bi-heart" aria-hidden="true"></i><span class="visually-hidden" data-favorite-label>Añadir a favoritos</span></button><button type="button" class="detail-icon-button" data-zoom-open title="Ampliar imagen"><i class="bi bi-zoom-in" aria-hidden="true"></i><span class="visually-hidden">Ampliar imagen</span></button></div>
                    <button class="gallery-arrow prev" type="button" data-gallery-prev aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button class="gallery-arrow next" type="button" data-gallery-next aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <span class="gallery-counter" data-gallery-counter><?php echo e($imageItems->count()); ?> imágenes</span>
                </div>
            </div>

            <div class="detail-info">
                <div class="product-detail-meta"><strong><?php echo e($product->marca); ?></strong><span><?php echo e($categoryName); ?></span><?php if ($product->etiqueta) { ?><span class="product-detail-badge"><?php echo e($product->etiqueta); ?></span><?php } ?></div>
                <h1><?php echo e($product->nombre); ?></h1>
                <div class="product-detail-rating"><div class="rating-stars" data-rating-widget role="radiogroup" aria-label="Valorar producto"><?php for ($rating = 1; $rating <= 5; $rating++) { ?><button type="button" data-rating-value="<?php echo e($rating); ?>" role="radio" aria-checked="<?php echo e($rating === (int) $userRating ? 'true' : 'false'); ?>" aria-label="<?php echo e($rating); ?> de 5 estrellas"><i class="bi <?php echo e($rating <= (int) $averageRating ? 'bi-star-fill' : 'bi-star'); ?>" aria-hidden="true"></i></button><?php } ?></div><span class="rating-muted" data-rating-summary><?php echo e($reviewCount > 0 ? 'Promedio: '.number_format($averageRating, 1).'/5 ('.$reviewCount.' reseñas)' : 'Sin valoración registrada'); ?></span><span class="product-detail-sku">SKU: <strong data-current-sku><?php echo e($product->variants->first()?->codigo_sku ?: 'No registrado'); ?></strong></span></div>
                <div class="product-detail-price"><strong data-current-price>S/ <?php echo e(number_format($salePrice, 2)); ?></strong><?php if ($discount > 0) { ?><div class="product-detail-old-price"><del>S/ <?php echo e(number_format($originalPrice, 2)); ?></del><span class="product-detail-discount">-<?php echo e($discount); ?>%</span></div><?php } ?></div>
                <p class="product-detail-stock <?php echo e($product->existencias_disponibles > 0 ? 'is-available' : 'is-unavailable'); ?>" data-current-stock data-stock="<?php echo e($product->existencias_disponibles); ?>"><i class="bi <?php echo e($product->existencias_disponibles > 0 ? 'bi-check-circle' : 'bi-x-circle'); ?>" aria-hidden="true"></i><?php echo e($product->existencias_disponibles > 0 ? $product->existencias_disponibles.' unidades disponibles' : 'Sin stock'); ?></p>

                <?php if ($product->variants->isNotEmpty()) { ?>
                    <fieldset class="product-variant-selector"><legend>Variante</legend><div class="variant-options" role="radiogroup" aria-label="Variantes disponibles">
                        <?php $__currentLoopData = $product->variants;
                    $__env->addLoop($__currentLoopData);
                    foreach ($__currentLoopData as $index => $variant) {
                        $__env->incrementLoopIndices();
                        $loop = $__env->getLastLoop(); ?>
                            <label class="variant-option <?php echo e($index === 0 ? 'is-selected' : ''); ?> <?php echo e(($variant->existencias_disponibles ?? $variant->existencias) <= 0 ? 'is-disabled' : ''); ?>"><input type="radio" name="variante_visual" value="<?php echo e($variant->id); ?>" data-variant-option <?php if ($index === 0) {
                                echo 'checked';
                            } ?> <?php if (($variant->existencias_disponibles ?? $variant->existencias) <= 0) {
                                echo 'disabled';
                            } ?>><span><?php echo e($variant->nombre_color); ?></span></label>
                        <?php } $__env->popLoop();
                    $loop = $__env->getLastLoop(); ?>
                    </div></fieldset>
                <?php } ?>

                <form class="product-detail-cart" action="<?php echo e(route('cart.add', $product)); ?>" method="post" data-product-form>
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="variante_id" value="<?php echo e($product->variants->first()?->id); ?>" data-selected-variant>
                    <div class="quantity-control"><label for="cantidad-producto">Cantidad</label><div><button type="button" data-quantity-minus aria-label="Disminuir cantidad">−</button><input id="cantidad-producto" type="number" name="cantidad" value="1" min="1" max="<?php echo e(max(1, $product->existencias_disponibles)); ?>" inputmode="numeric" data-quantity-input><button type="button" data-quantity-plus aria-label="Aumentar cantidad">+</button></div></div>
                    <?php if (auth()->guard()->check()) { ?>
                        <div class="product-detail-actions"><button class="product-detail-buy product-detail-buy--primary" type="submit" <?php if ($product->existencias_disponibles <= 0) {
                            echo 'disabled';
                        } ?> data-add-to-cart><i class="bi bi-cart3" aria-hidden="true"></i> Agregar al carrito</button><button class="product-detail-buy product-detail-buy--success" type="submit" name="comprar_ahora" value="1" <?php if ($product->existencias_disponibles <= 0) {
                            echo 'disabled';
                        } ?>><i class="bi bi-lightning-charge" aria-hidden="true"></i> Comprar ahora</button></div>
                    <?php } else { ?>
                        <a class="product-detail-buy product-detail-buy--success" href="<?php echo e(route('login')); ?>"><i class="bi bi-cart3" aria-hidden="true"></i> Comprar</a>
                    <?php } ?>
                    <p class="product-detail-form-message" data-form-message role="status" hidden></p>
                </form>
                <button type="button" class="product-favorite-disabled" data-favorite-toggle aria-pressed="<?php echo e($isFavorite ? 'true' : 'false'); ?>"><i class="bi <?php echo e($isFavorite ? 'bi-heart-fill' : 'bi-heart'); ?>" aria-hidden="true"></i> <span data-favorite-label><?php echo e($isFavorite ? 'Quitar de favoritos' : 'Añadir a favoritos'); ?></span><small data-favorite-status><?php echo e(auth()->check() ? 'Se guarda en tu cuenta' : 'Inicia sesión para guardar'); ?></small></button>
                <div class="product-trust-grid"><div><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Producto original</strong><small>Información del catálogo</small></span></div><div><i class="bi bi-lock" aria-hidden="true"></i><span><strong>Pago seguro</strong><small>Flujo protegido por sesión</small></span></div><div><i class="bi bi-headset" aria-hidden="true"></i><span><strong>Soporte técnico</strong><small>Atención Technology Cell</small></span></div></div>
            </div>
        </div>

        <div class="product-delivery-panel"><i class="bi bi-truck" aria-hidden="true"></i><div><strong>Entrega y disponibilidad</strong><span>Consulta la sede y el costo de envío al confirmar el pedido.</span></div><b>Configuración de envío no registrada</b></div>

        <section class="product-tabs" data-product-tabs><div class="product-tab-list" role="tablist" aria-label="Información del producto"><button type="button" role="tab" aria-selected="true" data-tab-target="description">Descripción</button><button type="button" role="tab" aria-selected="false" data-tab-target="specifications">Especificaciones</button><button type="button" role="tab" aria-selected="false" data-tab-target="reviews">Reseñas (<?php echo e($reviewCount); ?>)</button><button type="button" role="tab" aria-selected="false" data-tab-target="shipping">Envíos y garantía</button></div>
            <div class="product-tab-panel is-active" data-tab-panel="description" role="tabpanel"><h2>Descripción</h2><?php if ($product->descripcion) { ?><p><?php echo nl2br(e($product->descripcion)); ?></p><?php } else { ?><p class="empty-state">Este producto no tiene una descripción registrada.</p><?php } ?></div>
            <div class="product-tab-panel" data-tab-panel="specifications" role="tabpanel" hidden><h2>Especificaciones principales</h2><dl class="specification-table"><div><dt>Marca</dt><dd><?php echo e($product->marca); ?></dd></div><div><dt>Categoría</dt><dd><?php echo e($categoryName); ?></dd></div><div><dt>SKU</dt><dd><?php echo e($product->variants->first()?->codigo_sku ?: 'No registrado'); ?></dd></div><div><dt>Color</dt><dd><?php echo e($product->color ?: ($product->variants->first()?->nombre_color ?: 'No registrado')); ?></dd></div><div><dt>Almacenamiento / tipo</dt><dd><?php echo e($product->almacenamiento ?: 'No registrado'); ?></dd></div><?php $__currentLoopData = $product->features;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $feature) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><div><dt><?php echo e($feature->nombre); ?></dt><dd><?php echo e($feature->valor); ?></dd></div><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></dl></div>
            <div class="product-tab-panel" data-tab-panel="reviews" role="tabpanel" hidden><h2>Reseñas</h2><div class="review-widget"><strong>Valora este producto</strong><div class="review-widget-stars" data-review-widget role="radiogroup" aria-label="Selecciona tu valoración"><?php for ($rating = 1; $rating <= 5; $rating++) { ?><button type="button" data-rating-value="<?php echo e($rating); ?>" role="radio" aria-checked="<?php echo e($rating === (int) $userRating ? 'true' : 'false'); ?>" aria-label="<?php echo e($rating); ?> de 5 estrellas"><i class="bi <?php echo e($rating <= (int) $userRating ? 'bi-star-fill' : 'bi-star'); ?>" aria-hidden="true"></i></button><?php } ?></div><p class="empty-state" data-review-message><?php echo e(auth()->check() ? ($userRating ? 'Tu valoración actual se guarda en tu cuenta.' : 'Selecciona una estrella para guardar tu valoración.') : 'Inicia sesión para guardar tu valoración en la base de datos.'); ?></p></div></div>
            <div class="product-tab-panel" data-tab-panel="shipping" role="tabpanel" hidden><h2>Envíos y garantía</h2><p class="empty-state">No existe una configuración persistida de envío o garantía para este producto. No se muestran condiciones simuladas.</p></div>
        </section>

        <section class="related-products" aria-labelledby="related-products-title"><div class="section-head"><div><span class="eyebrow">También puedes revisar</span><h2 id="related-products-title">Productos relacionados</h2></div><a href="<?php echo e(route('catalog.index', ['categoria' => $product->categoria_id])); ?>">Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div><?php if ($relatedProducts->isNotEmpty()) { ?><div class="related-grid"><?php $__currentLoopData = $relatedProducts;
            $__env->addLoop($__currentLoopData);
            foreach ($__currentLoopData as $related) {
                $__env->incrementLoopIndices();
                $loop = $__env->getLastLoop(); ?><article class="related-card"><a href="<?php echo e(route('catalog.show', $related)); ?>" class="related-card-image"><?php if ($related->url_imagen_principal) { ?><img src="<?php echo e($related->url_imagen_principal); ?>" alt="<?php echo e($related->marca); ?> <?php echo e($related->nombre); ?>" loading="lazy"><?php } else { ?><i class="bi bi-box-seam" aria-hidden="true"></i><?php } ?></a><div><small><?php echo e($related->marca); ?> · <?php echo e($related->category?->nombre ?: $related->categoria); ?></small><h3><a href="<?php echo e(route('catalog.show', $related)); ?>"><?php echo e($related->nombre); ?></a></h3><strong>S/ <?php echo e(number_format((float) ($related->precio_oferta ?? $related->precio), 2)); ?></strong><span class="related-stock <?php echo e($related->existencias_disponibles > 0 ? 'is-available' : 'is-unavailable'); ?>"><?php echo e($related->existencias_disponibles > 0 ? $related->existencias_disponibles.' disponibles' : 'Sin stock'); ?></span></div></article><?php } $__env->popLoop();
            $loop = $__env->getLastLoop(); ?></div><?php } else { ?><p class="empty-state related-empty">No hay productos relacionados disponibles.</p><?php } ?></section>
    </div>
</section>

<dialog class="product-zoom-dialog" data-zoom-dialog aria-labelledby="product-zoom-title"><div class="product-zoom-header"><strong id="product-zoom-title">Vista ampliada</strong><button type="button" data-zoom-close aria-label="Cerrar ampliación"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div><img data-zoom-image src="<?php echo e($primaryImage ?? ''); ?>" alt="<?php echo e($product->nombre); ?>"></dialog>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>