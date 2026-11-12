<section class="assistant-chat panel" id="assistantChat" data-endpoint="{{ route('assistant.ask') }}">
    <div class="assistant-chat-head">
        <div class="assistant-avatar">MD</div>
        <div>
            <strong>MD Assistant</strong>
            <small><span class="assistant-online-dot"></span> Asistente del catálogo disponible</small>
        </div>
        <button type="button" class="btn btn-ghost assistant-clear" id="assistantClear">Nueva conversación</button>
    </div>

    <div class="assistant-messages" id="assistantMessages" aria-live="polite">
        @php
            $role = 'bot';
            $name = 'MD Assistant';
        @endphp
<article class="assistant-message assistant-message-{{ $role }}">
    <div class="assistant-message-avatar">{{ $role === 'user' ? 'TÚ' : 'MD' }}</div>
    <div class="assistant-bubble">
        @if($name)<b>{{ $name }}</b>@endif

            <p>¡Hola! Puedo ayudarte a elegir un celular, buscar opciones por presupuesto, comparar necesidades o revisar productos disponibles. Escríbeme, por ejemplo: <em>“quiero un celular para gaming menor a S/1500”</em>.</p>

    </div>
</article>

    </div>

    @php
    $suggestions ??= [
        ['icon' => 'bi-controller', 'label' => 'Gaming < S/1500', 'prompt' => 'Quiero un celular para gaming menor a S/1500'],
        ['icon' => 'bi-camera', 'label' => 'Buena cámara', 'prompt' => 'Recomiéndame un celular con buena cámara'],
        ['icon' => 'bi-cash-coin', 'label' => 'Económicos', 'prompt' => '¿Qué celulares económicos tienes?'],
        ['icon' => 'bi-mortarboard', 'label' => 'Para estudiar', 'prompt' => 'Necesito un celular para estudiar'],
    ];
@endphp

<div class="assistant-quick-actions" aria-label="Preguntas rápidas">
    @foreach($suggestions as $suggestion)
        <button type="button" data-assistant-prompt="{{ $suggestion['prompt'] }}"><i class="bi {{ $suggestion['icon'] }}"></i> {{ $suggestion['label'] }}</button>
    @endforeach
</div>


    <form class="assistant-composer" id="assistantForm">
        @csrf
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
