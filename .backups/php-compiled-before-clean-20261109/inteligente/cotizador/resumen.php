<?php $__env->startSection('title', 'Portal mayorista'); ?>
<?php $__env->startSection('body_class', 'modulo-mayorista'); ?>

<?php
    $urlImagenPrincipal = static fn ($producto): string => $producto->url_imagen_principal
        ?: asset('assets/cell-legacy/img/marca/logo.jpeg');
?>

<?php $__env->startSection('content'); ?>
    <section class="mayorista-hero">
        <div class="container mayorista-hero-grid">
            <div class="mayorista-copy">
                <span class="eyebrow">Portal mayorista · Empresas que impulsan el futuro</span>
                <h1>Haz crecer tu negocio con <em>MD Technology Cell</em></h1>
                <p>Accede a precios mayoristas, compra por volumen y recibe asesoría personalizada para tu empresa. Tecnología original, stock consultable y respaldo de las mejores marcas.</p>
                <div class="hero-actions">
                    <a class="btn btn-primary" href="#solicitar-cotizacion">Solicitar cotización <i class="bi bi-arrow-right"></i></a>
                    <a class="btn btn-ghost" href="#catalogo-b2b"><i class="bi bi-grid"></i> Ver catálogo mayorista</a>
                </div>
                <div class="mayorista-beneficios">
                    <?php $__currentLoopData = $benefits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $benefit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <span><i class="bi <?php echo e($benefit['icon']); ?>"></i><?php echo e($benefit['label']); ?></span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <div class="mayorista-ilustracion">
                <span>Tu aliado mayorista<br>en tecnología</span>
                <div class="cajas-mayorista"><i class="bi bi-box-seam"></i><i class="bi bi-laptop"></i><i class="bi bi-phone"></i><i class="bi bi-earbuds"></i></div>
                <ul>
                    <li><i class="bi bi-graph-up-arrow"></i> Más negocios, más posibilidades</li>
                    <li><i class="bi bi-building-check"></i> Equipamos empresas</li>
                    <li><i class="bi bi-tags"></i> Precios especiales</li>
                    <li><i class="bi bi-headset"></i> Asesoría dedicada</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="container marcas-mayorista">
        <b>Las mejores marcas<br>para tu negocio</b>
        <?php $__currentLoopData = ['SAMSUNG', 'Apple', 'oppo', 'XIAOMI', 'HONOR', 'JBL', 'Beats']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $brand): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <span><?php echo e($brand); ?></span>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </section>

    <section class="container mayorista-cuerpo">
        <div class="mayorista-principal">
            <header class="section-head">
                <div><span class="eyebrow">Herramientas B2B</span><h2>Potencia tu negocio</h2></div>
            </header>
            <div class="herramientas-b2b">
                <a href="<?php echo e(route('recommendations')); ?>"><i class="bi bi-lightbulb"></i><b>SmartMatch B2B</b><small>Encuentra el mix ideal para tu negocio.</small></a>
                <a href="<?php echo e(route('comparison')); ?>"><i class="bi bi-columns-gap"></i><b>Comparador inteligente</b><small>Compara precios y toma mejores decisiones.</small></a>
                <a href="<?php echo e(route('assistant')); ?>"><i class="bi bi-robot"></i><b>MD Assistant B2B</b><small>Asesoría especializada en compras empresariales.</small></a>
                <a href="#solicitar-cotizacion"><i class="bi bi-file-earmark-text"></i><b>Cotizaciones rápidas</b><small>Genera y recibe tu propuesta en minutos.</small></a>
            </div>

            <header class="section-head" id="catalogo-b2b">
                <div><span class="eyebrow">Precios especiales por volumen</span><h2>Productos destacados para mayoristas</h2></div>
                <a href="<?php echo e(route('catalog.index')); ?>">Ver todo el catálogo <i class="bi bi-arrow-right"></i></a>
            </header>
            <div class="productos-mayorista">
                <?php $__empty_1 = true; $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <article>
                        <span>Disponible</span>
                        <img class="mayorista-product-photo" src="<?php echo e($urlImagenPrincipal($product)); ?>" alt="<?php echo e($product->marca); ?> <?php echo e($product->nombre); ?>" loading="lazy">
                        <small><?php echo e($product->marca); ?></small>
                        <h3><?php echo e($product->nombre); ?></h3>
                        <p>1–9 und. <b>S/ <?php echo e(number_format((float) $product->effective_price, 2)); ?></b><br>10–49 und. <b>S/ <?php echo e(number_format($product->wholesale_price_10, 2)); ?></b><br>50+ und. <b>S/ <?php echo e(number_format($product->wholesale_price_50, 2)); ?></b></p>
                        <a href="<?php echo e(route('catalog.show', $product)); ?>">Ver producto</a>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p>No hay celulares activos disponibles.</p>
                <?php endif; ?>
            </div>
        </div>

        <aside class="cotizacion-rapida" id="solicitar-cotizacion">
            <h2>Solicita tu cotización mayorista</h2>
            <p>Cuéntanos sobre tu empresa y te enviaremos una propuesta personalizada.</p>
            <form action="<?php echo e(route('pages.contact.store')); ?>" method="post">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="nombre" value="<?php echo e(auth()->user()?->nombre ?: 'Solicitud mayorista'); ?>">
                <input type="hidden" name="contacto" value="<?php echo e(auth()->user()?->correo ?: 'Portal mayorista'); ?>">
                <label>
                    <i class="bi bi-buildings"></i>
                    <input
                        name="empresa"
                        placeholder="Nombre de la empresa"
                        required
                    >
                </label>

                <label>
                    <i class="bi bi-card-text"></i>
                    <input
                        name="ruc"
                        placeholder="RUC de la empresa"
                    >
                </label>

                <label>
                    <i class="bi bi-geo-alt"></i>
                    <input
                        name="ciudad"
                        placeholder="Ciudad"
                    >
                </label>

                <label>
                    <i class="bi bi-box-seam"></i>
                    <input
                        name="productos"
                        placeholder="Productos de interés"
                        required
                    >
                </label>
                <textarea
                    name="mensaje"
                    hidden
                >Solicitud de cotización mayorista para <?php echo e(auth()->user()?->nombre ?: 'cliente'); ?>.</textarea>
                <button class="btn btn-primary" type="submit">
                    Enviar solicitud
                    <i class="bi bi-arrow-right"></i>
                </button>
            </form>
            <small><i class="bi bi-shield-check"></i> Nos pondremos en contacto en menos de 24 horas.</small>
            <a class="asesor-b2b" href="<?php echo e(route('assistant')); ?>"><i class="bi bi-people"></i><span><b>Un equipo dedicado a tu crecimiento</b>Asesoría, mejores condiciones y soporte continuo.</span></a>
        </aside>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>