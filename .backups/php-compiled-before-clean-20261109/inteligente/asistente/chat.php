<section class="assistant-chat panel" id="assistantChat" data-endpoint="<?php echo e(route('assistant.ask')); ?>">
    <div class="assistant-chat-head">
        <div class="assistant-avatar">MD</div>
        <div>
            <strong>MD Assistant</strong>
            <small><span class="assistant-online-dot"></span> Asistente del catÃ¡logo disponible</small>
        </div>
        <button type="button" class="btn btn-ghost assistant-clear" id="assistantClear">Nueva conversaciÃ³n</button>
    </div>

    <div class="assistant-messages" id="assistantMessages" aria-live="polite">
        <?php
            $role = 'bot';
            $name = 'MD Assistant';
        ?>
<article class="assistant-message assistant-message-<?php echo e($role); ?>">
    <div class="assistant-message-avatar"><?php echo e($role === 'user' ? 'TÃš' : 'MD'); ?></div>
    <div class="assistant-bubble">
        <?php if($name): ?><b><?php echo e($name); ?></b><?php endif; ?>

            <p>Â¡Hola! Puedo ayudarte a elegir un celular, buscar opciones por presupuesto, comparar necesidades o revisar productos disponibles. EscrÃ­beme, por ejemplo: <em>â€œquiero un celular para gaming menor a S/1500â€</em>.</p>

    </div>
</article>

    </div>

    <?php
    $suggestions ??= [
        ['icon' => 'bi-controller', 'label' => 'Gaming < S/1500', 'prompt' => 'Quiero un celular para gaming menor a S/1500'],
        ['icon' => 'bi-camera', 'label' => 'Buena cÃ¡mara', 'prompt' => 'RecomiÃ©ndame un celular con buena cÃ¡mara'],
        ['icon' => 'bi-cash-coin', 'label' => 'EconÃ³micos', 'prompt' => 'Â¿QuÃ© celulares econÃ³micos tienes?'],
        ['icon' => 'bi-mortarboard', 'label' => 'Para estudiar', 'prompt' => 'Necesito un celular para estudiar'],
    ];
?>

<div class="assistant-quick-actions" aria-label="Preguntas rÃ¡pidas">
    <?php $__currentLoopData = $suggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $suggestion): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <button type="button" data-assistant-prompt="<?php echo e($suggestion['prompt']); ?>"><i class="bi <?php echo e($suggestion['icon']); ?>"></i> <?php echo e($suggestion['label']); ?></button>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
    <small class="assistant-disclaimer">Las recomendaciones se generan con los productos y datos disponibles en el catÃ¡logo.</small>
</section>
