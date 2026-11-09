<?php $__env->startSection('body_class', 'modulo-asistente'); ?>
<?php $__env->startSection('title', 'MD Assistant'); ?>

<?php $__env->startSection('content'); ?>
    <section class="smart-page">
        <div class="container narrow">
            <div class="smart-hero">
                <span class="eyebrow">Asistente virtual del catÃ¡logo</span>
                <h1><i class="bi bi-robot" aria-hidden="true"></i> MD Assistant</h1>
                <p>Conversa con el asistente de forma continua. Puedes preguntar por precios, presupuesto, gaming, cÃ¡mara, baterÃ­a, estudio, trabajo o pedir recomendaciones del catÃ¡logo.</p>
            </div>
            <?php echo $__env->make('inteligente.asistente.chat', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

        </div>
    </section>



<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.tienda.principal', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>