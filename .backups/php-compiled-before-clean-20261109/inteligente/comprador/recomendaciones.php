<?php $__env->startSection('body_class', 'modulo-recomendador'); ?>
<?php $__env->startSection('title', 'SmartMatch'); ?>

<?php $__env->startSection('content'); ?>
    <section class="smartmatch-hero smartmatch-reference-hero">
        <div class="container smartmatch-hero-grid">
            <div class="smartmatch-hero-copy">
                <span class="smartmatch-etiqueta"><i class="bi bi-stars"></i> MD SmartCommerce</span>
                <h1>Encuentra tu <span>celular ideal</span> con SmartMatch</h1>
                <p>Indica tu presupuesto, para qué usarás el equipo y qué es lo más importante para ti. SmartMatch revisa el catálogo y te muestra los celulares que mejor encajan contigo.</p>
                <div class="smartmatch-pasos-mini">
                    <span><b>1</b> Presupuesto</span>
                    <span><b>2</b> Uso</span>
                    <span><b>3</b> Prioridad</span>
                </div>
            </div>
            <div class="smartmatch-hero-imagen">
                <img src="<?php echo e(asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png')); ?>" alt="Celulares y accesorios">
            </div>
        </div>
    </section>

    <section class="container smartmatch-contenido">
        <form class="smartmatch-form" method="get" id="buscar">
            <div class="smartmatch-form-titulo">
                <i class="bi bi-sliders"></i>
                <div>
                    <h2>Cuéntanos qué buscas</h2>
                    <p>Completa los tres datos y presiona el botón.</p>
                </div>
            </div>
            <div class="smartmatch-campos">
                <label>
                    <span><i class="bi bi-cash-coin"></i> Presupuesto máximo (S/)</span>
                    <input
                        class="input"
                        type="number"
                        name="presupuesto"
                        min="300"
                        step="50"
                        value="<?php echo e(request('presupuesto')); ?>"
                        placeholder="Ej. 1500"
                    >
                </label>

                <label>
                    <span><i class="bi bi-phone"></i> Uso principal</span>
                    <select name="uso">
                        <option value="">General</option>
                        <option value="study" <?php if(request('uso') === 'study'): echo 'selected'; endif; ?>>Estudio</option>
                        <option value="gaming" <?php if(request('uso') === 'gaming'): echo 'selected'; endif; ?>>Gaming</option>
                        <option value="camera" <?php if(request('uso') === 'camera'): echo 'selected'; endif; ?>>Fotografía</option>
                        <option value="work" <?php if(request('uso') === 'work'): echo 'selected'; endif; ?>>Trabajo</option>
                        <option value="social" <?php if(request('uso') === 'social'): echo 'selected'; endif; ?>>Redes sociales</option>
                    </select>
                </label>

                <label>
                    <span><i class="bi bi-star"></i> Prioridad</span>
                    <select name="prioridad">
                        <option value="">Equilibrada</option>
                        <option value="valor" <?php if(request('prioridad') === 'valor'): echo 'selected'; endif; ?>>Calidad/precio</option>
                        <option value="rendimiento" <?php if(request('prioridad') === 'rendimiento'): echo 'selected'; endif; ?>>Rendimiento</option>
                        <option value="bateria" <?php if(request('prioridad') === 'bateria'): echo 'selected'; endif; ?>>Batería</option>
                        <option value="camara" <?php if(request('prioridad') === 'camara'): echo 'selected'; endif; ?>>Cámara</option>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Encontrar mi celular</button>
            </div>
        </form>

        <div class="smartmatch-como">
            <article><i class="bi bi-wallet2"></i><h3>1. Tu presupuesto</h3><p>Solo se muestran equipos que no pasan el monto que indicas.</p></article>
            <article><i class="bi bi-controller"></i><h3>2. Tu forma de usarlo</h3><p>Estudio, juegos, fotos, trabajo o redes: cada uso tiene sus puntos fuertes.</p></article>
            <article><i class="bi bi-graph-up-arrow"></i><h3>3. Tu coincidencia</h3><p>Cada celular recibe un porcentaje para ayudarte a decidir.</p></article>
        </div>

        <div class="smartmatch-titulo-resultados">
            <h2>Te recomendamos estos equipos</h2>
            <p>Ordenados de mayor a menor coincidencia.</p>
        </div>

        <?php if($products->isNotEmpty()): ?>
            <div class="smartmatch-resultados">
                <?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
                        $path = ltrim((string) preg_replace('~^assets/~', '', $path ?: 'img/marca/logo.jpeg'), '/');
                        $position = $loop->iteration;
                    ?>

                    <article class="smartmatch-tarjeta">
                        <span class="smartmatch-puesto">#<?php echo e($position); ?></span>
                        <img class="smartmatch-product-photo" src="<?php echo e(asset('assets/cell-legacy/'.$path)); ?>" alt="<?php echo e($product->marca); ?> <?php echo e($product->nombre); ?>" loading="lazy">
                        <div class="smartmatch-tarjeta-arriba">
                            <div>
                                <small><?php echo e($product->marca ?: 'CELL SAC'); ?></small>
                                <h3><?php echo e($product->nombre); ?></h3>
                                <p class="smartmatch-precio">S/ <?php echo e(number_format((float) $product->effective_price, 2)); ?></p>
                            </div>
                            <div class="smartmatch-porcentaje"><b><?php echo e((int) $product->recommendation_score); ?>%</b><small>coincide</small></div>
                        </div>
                        <ul>
                            <li><i class="bi bi-check-circle-fill"></i> Precio dentro del catÃ¡logo vigente</li>
                            <li><i class="bi bi-check-circle-fill"></i> Stock disponible: <?php echo e($product->existencias); ?></li>
                            <li><i class="bi bi-check-circle-fill"></i> Puntaje calculado segÃºn tu selecciÃ³n</li>
                        </ul>
                        <div class="smartmatch-botones">
                            <a class="btn btn-primary" href="<?php echo e(route('catalog.show', $product)); ?>">Ver equipo</a>
                            <a class="btn btn-ghost" href="<?php echo e(route('comparison', ['seleccion' => $product->id])); ?>">Comparar</a>
                        </div>
                    </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php else: ?>
            <div class="smartmatch-vacio">
                <i class="bi bi-emoji-neutral"></i>
                <h2>No encontramos equipos para esa búsqueda</h2>
                <p>Prueba con un presupuesto un poco más alto o cambia la prioridad. También puedes revisar todo el catálogo.</p>
                <a class="btn btn-primary" href="<?php echo e(route('catalog.index')); ?>">Ver catálogo</a>
            </div>
        <?php endif; ?>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>