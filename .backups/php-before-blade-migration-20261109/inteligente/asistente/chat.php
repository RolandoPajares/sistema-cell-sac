<section class="assistant-chat panel" id="assistantChat" data-endpoint="<?php echo e(route('assistant.ask')); ?>">
    <div class="assistant-chat-head">
        <div class="assistant-avatar">MD</div>
        <div>
            <strong>MD Assistant</strong>
            <small><span class="assistant-online-dot"></span> Asistente del catálogo disponible</small>
        </div>
        <button type="button" class="btn btn-ghost assistant-clear" id="assistantClear">Nueva conversación</button>
    </div>

    <div class="assistant-messages" id="assistantMessages" aria-live="polite">
        <?php
            $role = 'bot';
$name = 'MD Assistant';
?>
<article class="assistant-message assistant-message-<?php echo e($role); ?>">
    <div class="assistant-message-avatar"><?php echo e($role === 'user' ? 'TÚ' : 'MD'); ?></div>
    <div class="assistant-bubble">
        <?php if ($name) { ?><b><?php echo e($name); ?></b><?php } ?>

            <p>¡Hola! Puedo ayudarte a elegir un celular, buscar opciones por presupuesto, comparar necesidades o revisar productos disponibles. Escríbeme, por ejemplo: <em>“quiero un celular para gaming menor a S/1500”</em>.</p>

    </div>
</article>

    </div>

    <?php
    $suggestions ??= [
['icon' => 'bi-controller', 'label' => 'Gaming < S/1500', 'prompt' => 'Quiero un celular para gaming menor a S/1500'],
['icon' => 'bi-camera', 'label' => 'Buena cámara', 'prompt' => 'Recomiéndame un celular con buena cámara'],
['icon' => 'bi-cash-coin', 'label' => 'Económicos', 'prompt' => '¿Qué celulares económicos tienes?'],
['icon' => 'bi-mortarboard', 'label' => 'Para estudiar', 'prompt' => 'Necesito un celular para estudiar'],
    ];
?>

<div class="assistant-quick-actions" aria-label="Preguntas rápidas">
    <?php $__currentLoopData = $suggestions;
$__env->addLoop($__currentLoopData);
foreach ($__currentLoopData as $suggestion) {
    $__env->incrementLoopIndices();
    $loop = $__env->getLastLoop(); ?>
        <button type="button" data-assistant-prompt="<?php echo e($suggestion['prompt']); ?>"><i class="bi <?php echo e($suggestion['icon']); ?>"></i> <?php echo e($suggestion['label']); ?></button>
    <?php } $__env->popLoop();
$loop = $__env->getLastLoop(); ?>
</div>


    <form class="assistant-composer" id="assistantForm">
        <?php echo csrf_field(); ?>
        <input
            class="input"
            id="assistantInput"
            name="message"
            autocomplete="off"
            maxlength="300"
            placeholder="Escribe otro mensaje..."
            required
        >
        <button class="btn btn-primary" id="assistantSend" type="submit">
            <i class="bi bi-send-fill"></i>
            Enviar
        </button>
    </form>
    <small class="assistant-disclaimer">Las recomendaciones se generan con los productos y datos disponibles en el catálogo.</small>
</section>
