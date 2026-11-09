<?php $__env->startSection('title', 'Configuración de la tienda virtual'); ?>
<?php $__env->startSection('body_class', 'modulo-ecommerce-marketing'); ?>
<?php $__env->startSection('content'); ?>
<section class="tc-em" data-ecommerce-tienda>
    <header class="tc-em__header"><div><span class="tc-em__eyebrow"><i class="bi bi-shop"></i> Ecommerce y Marketing</span><h1>Configuración de la tienda virtual <i class="bi bi-gear"></i></h1><p>Consulta la información empresarial y los datos de tienda que están disponibles en MariaDB.</p></div><div class="tc-em__actions"><a class="tc-em__button" href="<?php echo e(route('catalog.index')); ?>"><i class="bi bi-eye"></i> Ver tienda <i class="bi bi-box-arrow-up-right"></i></a><button type="button" class="tc-em__button tc-em__button--primary" data-em-store-open data-em-store-section="general" <?php if (! ($configuracionTiendaDisponible && $puedeActualizarConfiguracion)) {
        echo 'disabled';
    } ?> title="<?php echo e($configuracionTiendaDisponible ? 'Editar preferencias guardadas por empresa' : 'El esquema no contiene una entidad para guardar esta configuración'); ?>"><i class="bi bi-sliders"></i> Editar configuración</button></div></header>
    <?php if (session('status')) { ?><div class="tc-em__notice" role="status"><i class="bi bi-check-circle"></i><strong>Operación registrada</strong><span><?php echo e(session('status')); ?></span></div><?php } ?>
    <?php if ($errors->any()) { ?><div class="tc-em__notice" role="alert"><i class="bi bi-exclamation-circle"></i><strong>No se guardó la configuración</strong><span><?php echo e($errors->first()); ?></span></div><?php } ?>
    <div class="tc-em__notice"><i class="bi bi-info-circle"></i><strong>Datos de MariaDB</strong><span>La empresa, moneda, catálogo y medios de pago se consultan desde la base. Las preferencias se guardan por empresa cuando la estructura está disponible.</span></div>
    <div class="tc-em__store-tabs" role="tablist" aria-label="Secciones de configuración"><?php $__currentLoopData = $secciones;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $seccion) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?><button type="button" role="tab" aria-selected="<?php echo e($loop->first ? 'true' : 'false'); ?>" class="<?php echo e($loop->first ? 'is-active' : ''); ?>" data-em-store-tab="<?php echo e($seccion['clave']); ?>"><i class="bi <?php echo e($seccion['icono']); ?>"></i><?php echo e($seccion['titulo']); ?></button><?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?></div>
    <div class="tc-em__store-grid"><div class="tc-em__store-left">
        <?php $__currentLoopData = $secciones;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $seccion) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
            <article class="tc-em__card tc-em__settings-card" data-em-store-panel="<?php echo e($seccion['clave']); ?>" <?php if (! $loop->first) { ?> hidden <?php } ?>><h2><i class="bi <?php echo e($seccion['icono']); ?>"></i><?php echo e($seccion['resumen']); ?></h2><p>Información consultada desde las entidades disponibles en la base de datos.</p><dl><?php $__currentLoopData = $seccion['filas'];
    $__env->addLoop($__currentLoopData);
    foreach ($__currentLoopData as $fila) {
        $__env->incrementLoopIndices();
        $loop = $__env->getLastLoop(); ?><div><dt><?php echo e($fila[0]); ?></dt><dd><?php echo e($fila[1]); ?></dd></div><?php } $__env->popLoop();
    $loop = $__env->getLastLoop(); ?></dl><button type="button" class="tc-em__button tc-em__button--primary" data-em-store-open data-em-store-section="<?php echo e($seccion['clave']); ?>" <?php if (! ($puedeActualizarConfiguracion && $seccion['clave'] !== 'integraciones')) {
        echo 'disabled';
    } ?> title="<?php echo e($seccion['clave'] === 'integraciones' ? 'Las integraciones externas no se modifican desde esta pantalla' : 'Guardar preferencias en MariaDB por empresa'); ?>"><i class="bi bi-pencil"></i> Editar <?php echo e(mb_strtolower($seccion['titulo'])); ?></button></article>
        <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
        <div class="tc-em__store-minicards"><article class="tc-em__card"><h2><i class="bi bi-box-seam"></i> Catálogo publicado</h2><p><?php echo e(number_format($productosActivosCount)); ?> productos activos y <?php echo e(number_format($categoriasActivasCount)); ?> categorías activas en la empresa.</p></article><article class="tc-em__card"><h2><i class="bi bi-coin"></i> Moneda y precios</h2><p>Moneda base registrada: <?php echo e($empresa->moneda_base); ?>. <?php echo e($configuracionTiendaDisponible ? 'La configuración de tienda está habilitada.' : 'El precio mayorista requiere la estructura SQL propuesta.'); ?></p></article></div>
    </div><aside class="tc-em__store-right"><article class="tc-em__card tc-em__preview"><h2><i class="bi bi-eye"></i> Vista previa del catálogo</h2><div class="tc-em__preview-browser"><div class="tc-em__preview-top"><strong><?php echo e($nombreTienda); ?></strong><span>Inicio &nbsp; Productos &nbsp; Promociones</span><i class="bi bi-cart"></i></div><div class="tc-em__preview-hero"><div><strong>Catálogo de productos</strong><span><?php if ($productosVista->isNotEmpty()) { ?><?php echo e($productosVista->first()->nombre); ?><?php } else { ?> No hay productos activos registrados <?php } ?></span><em><?php if ($productosVista->isNotEmpty()) { ?><?php echo e($empresa->moneda_base); ?> <?php echo e(number_format((float) ($productosVista->first()->precio_oferta ?? $productosVista->first()->precio), 2, '.', ',')); ?><?php } else { ?> Sin precio registrado <?php } ?></em></div><div class="tc-em__phones" aria-hidden="true"><i></i><i></i></div></div></div><div class="tc-em__preview-dots"><i></i><i></i><i></i></div></article><article class="tc-em__card tc-em__quick-settings"><h2><i class="bi bi-sliders"></i> Resumen de tienda</h2><div><i class="bi bi-building"></i><span><strong>Empresa</strong><small><?php echo e($empresa->razon_social); ?></small></span></div><div><i class="bi bi-box-seam"></i><span><strong>Productos activos</strong><small><?php echo e(number_format($productosActivosCount)); ?> registros</small></span></div><div><i class="bi bi-palette"></i><span><strong>Moneda base</strong><small><?php echo e($empresa->moneda_base); ?></small></span></div></article></aside></div>
    <?php echo $__env->make('gestion.marketing.ecommerce-marketing.componentes.modal-tienda', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
</section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.panel.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>