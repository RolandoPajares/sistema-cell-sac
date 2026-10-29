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
        <x-inteligencia.asistente.mensaje name="MD Assistant">
            <p>¡Hola! Puedo ayudarte a elegir un celular, buscar opciones por presupuesto, comparar necesidades o revisar productos disponibles. Escríbeme, por ejemplo: <em>“quiero un celular para gaming menor a S/1500”</em>.</p>
        </x-inteligencia.asistente.mensaje>
    </div>

    <x-inteligencia.asistente.sugerencias />

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
