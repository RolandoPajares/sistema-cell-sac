<?php $__env->startSection('body_class', 'modulo-comparador'); ?>
<?php $__env->startSection('title', 'Comparador inteligente'); ?>

<?php
    $selectedIds = collect(explode(',', (string) request('seleccion')))->filter()->values();
?>

<?php $__env->startSection('content'); ?>
    <section class="smart-page">
        <div class="comparator-reference-hero comparator-reference-hero--full">
            <div class="container comparator-reference-hero__inner">
                <span class="eyebrow">MD SmartCommerce</span>
                <h1><i class="bi bi-columns-gap" aria-hidden="true"></i> Comparador <span>inteligente</span></h1>
                <p>Selecciona hasta tres celulares y compara precio, disponibilidad y SmartScore por perfil.</p>
            </div>
        </div>
        <div class="container">
            <div class="comparator-steps" aria-label="Pasos del comparador"><span><b>1</b> Selecciona hasta 3 equipos</span><span><b>2</b> Compara características</span><span><b>3</b> Elige el ideal para ti</span></div>
            <form class="panel smart-form compare-picker" method="get" id="compareForm">
                <?php for($slot = 1; $slot <= 3; $slot++): ?>
                    <label>
                        Equipo <?php echo e($slot); ?>

                        <select class="compare-select">
                            <option value="">-- Opcional --</option>
                            <?php $__currentLoopData = $availableProducts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $option): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($option->id); ?>" <?php if((string) $selectedIds->get($slot - 1) === (string) $option->id): echo 'selected'; endif; ?>>
                                    <?php echo e($option->marca); ?> <?php echo e($option->nombre); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </label>
                <?php endfor; ?>
                <input type="hidden" name="seleccion" id="compareIds" value="<?php echo e($selectedIds->implode(',')); ?>">
                <button class="btn btn-primary" type="submit">Comparar ahora</button>
            </form>

            <?php if($products->isNotEmpty()): ?>
                <div class="comparison-grid">
                    <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php
    $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
    $path = ltrim((string) preg_replace('~^assets/~', '', $path ?: 'img/marca/logo.jpeg'), '/');
    $score = $product->intelligenceProfile?->puntuacion_valor ?? 70;
?>

<article class="panel comparison-card">
    <img class="comparison-product-photo" src="<?php echo e(asset('assets/cell-legacy/'.$path)); ?>" alt="<?php echo e($product->marca); ?> <?php echo e($product->nombre); ?>" loading="lazy">
    <span class="eyebrow"><?php echo e($product->marca ?: 'CELL SAC'); ?></span>
    <h2><?php echo e($product->nombre); ?></h2>
    <div class="price">S/ <?php echo e(number_format((float) $product->effective_price, 2)); ?></div>
    <p><?php echo e($product->category?->nombre ?: 'Equipo'); ?> · Stock <?php echo e((int) $product->existencias); ?></p>
    <div class="score-row">
        <span>SmartScore</span>
        <b><?php echo e((int) $score); ?>/100</b>
        <div class="scorebar"><i style="width: <?php echo e((int) $score); ?>%"></i></div>
    </div>
    <a class="btn btn-ghost" href="<?php echo e(route('catalog.show', $product)); ?>">Ver detalles</a>
</article>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            <?php else: ?>
                <div class="panel comparison-empty">
                    <i class="bi bi-columns-gap"></i>
                    <h2>Selecciona los equipos que quieres comparar</h2>
                    <p>Puedes elegir hasta tres productos del catálogo para revisar sus datos en paralelo.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>