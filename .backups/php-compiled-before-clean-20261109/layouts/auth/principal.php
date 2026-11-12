<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
        <meta name="description" content="Celulares y accesorios originales de MD Technology Digital Cell.">
        <title><?php echo $__env->yieldContent('title', 'MD Technology Cell'); ?></title>

        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/layouts/auth/principal.css', 'resources/js/layouts/auth/principal.js']); ?>
        <link rel="stylesheet" href="<?php echo e(asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css')); ?>">
        <?php echo $__env->yieldPushContent('styles'); ?>
    </head>
    <body class="<?php echo $__env->yieldContent('body_class'); ?>">
        <?php echo $__env->make('layouts.auth.encabezado', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php if (! (request()->routeIs('home'))): ?>
            <?php
    $campaigns = collect($publicCampaigns ?? []);
    $principalCampaign = $campaigns->firstWhere('ubicacion', 'emergente') ?? $campaigns->first();
    $contextualCampaign = $campaigns->first(fn ($campaign): bool => $principalCampaign && $campaign->id !== $principalCampaign->id);
    $advertisingContext = request()->routeIs('home') ? 'inicio' : 'general';
?>

<?php if($principalCampaign): ?>
    <div class="publicidad-dinamica publicidad-<?php echo e($advertisingContext); ?>" id="publicidadDinamica" data-contexto="<?php echo e($advertisingContext); ?>" data-perfil="visitante" data-limite="4" data-espera="12000">
        <div class="publicidad-modal-fondo js-publicidad-modal" hidden>
            <section class="publicidad-modal" role="dialog" aria-modal="true" aria-labelledby="publicidadTitulo" data-track-url="<?php echo e(route('campaigns.track', $principalCampaign)); ?>">
                <button class="publicidad-cerrar js-publicidad-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
                <div class="publicidad-modal-arte">
                    <span><i class="bi bi-lightning-charge-fill"></i></span>
                    <b>MD</b><strong>SMART DEALS</strong>
                    <small>Una mejor compra empieza con una buena recomendación.</small>
                </div>
                <div class="publicidad-modal-contenido">
                    <span class="publicidad-etiqueta"><i class="bi bi-lightning-charge-fill"></i> <?php echo e($principalCampaign->nombre); ?></span>
                    <h2 id="publicidadTitulo"><?php echo e($principalCampaign->titulo); ?></h2>
                    <p><?php echo e($principalCampaign->descripcion); ?></p>
                    <?php if($principalCampaign->precio_oferta): ?>
                        <div class="publicidad-precio"><del>S/ <?php echo e(number_format((float) $principalCampaign->precio_anterior, 2)); ?></del><strong>S/ <?php echo e(number_format((float) $principalCampaign->precio_oferta, 2)); ?></strong></div>
                    <?php endif; ?>
                    <div class="publicidad-beneficios"><span><i class="bi bi-check-circle-fill"></i> Fácil de usar</span><span><i class="bi bi-check-circle-fill"></i> Sin compromiso</span></div>
                    <a class="btn btn-primary js-publicidad-accion" href="<?php echo e(route('catalog.index')); ?>"><?php echo e($principalCampaign->texto_boton); ?> <i class="bi bi-arrow-right"></i></a>
                    <button class="publicidad-despues js-publicidad-cerrar" type="button">Seguir navegando</button>
                </div>
            </section>
        </div>

        <?php if($contextualCampaign): ?>
            <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden>
                <button class="publicidad-cerrar js-contextual-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
                <span class="publicidad-contextual-icono"><i class="bi bi-gift-fill"></i></span>
                <div><small><?php echo e($contextualCampaign->nombre); ?></small><b><?php echo e($contextualCampaign->titulo); ?></b><p><?php echo e($contextualCampaign->descripcion); ?></p></div>
                <a class="btn btn-primary" href="<?php echo e(route('catalog.index')); ?>"><?php echo e($contextualCampaign->texto_boton); ?></a>
            </aside>
        <?php else: ?>
            <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden></aside>
        <?php endif; ?>

        <button class="publicidad-lanzador js-publicidad-lanzador" type="button" aria-label="Ver oferta disponible" title="Ver oferta"><i class="bi bi-gift-fill"></i><span>Oferta</span></button>
    </div>
<?php endif; ?>

        <?php endif; ?>

        <?php echo $__env->make('layouts.auth.mensajes', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <main><?php echo $__env->yieldContent('content'); ?></main>

        <?php echo $__env->make('layouts.tienda.pie-pagina', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
        <?php echo $__env->make('inteligente.asistente.flotante', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        <?php echo $__env->yieldPushContent('scripts'); ?>
    </body>
</html>
