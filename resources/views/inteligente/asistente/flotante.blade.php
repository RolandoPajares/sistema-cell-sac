
<aside class="assistant-float" data-admin-assistant aria-label="Asistente IA" hidden>
    <button class="assistant-float-launcher" type="button" data-admin-assistant-toggle aria-controls="adminAssistantPanel" aria-expanded="false">
        <span class="assistant-float-launcher-icon"><i class="bi bi-stars" aria-hidden="true"></i></span>
        <span><strong>Asistente IA</strong><small>Ayuda contextual de Technology Cell</small></span>
    </button>

    <section class="assistant-float-panel" id="adminAssistantPanel" data-admin-assistant-panel hidden aria-labelledby="adminAssistantTitle">
        <header class="assistant-float-header">
            <div class="assistant-float-title">
                <span class="assistant-float-avatar"><i class="bi bi-stars" aria-hidden="true"></i></span>
                <span><strong id="adminAssistantTitle">Asistente IA</strong><small><i class="bi bi-circle-fill" aria-hidden="true"></i> En mantenimiento</small></span>
            </div>
            <div class="assistant-float-actions">
                <button type="button" data-admin-assistant-minimize aria-label="Minimizar asistente"><i class="bi bi-dash-lg" aria-hidden="true"></i></button>
                <button type="button" data-admin-assistant-close aria-label="Cerrar asistente"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </div>
        </header>

        <div class="assistant-float-context"><i class="bi bi-layers" aria-hidden="true"></i> Disponible para todos los roles</div>
        <div class="assistant-float-messages" data-admin-assistant-messages aria-live="polite">
            <article><span><i class="bi bi-stars" aria-hidden="true"></i></span>
                <p>El Asistente IA se encuentra en mantenimiento. La conexión con la API estará disponible próximamente.</p>
            </article>
        </div>
        <div class="assistant-float-prompts">
            <button type="button" data-admin-assistant-prompt="¿Qué funciones tiene el asistente?"><i class="bi bi-question-circle" aria-hidden="true"></i> ¿Qué puedes hacer?</button>
            <button type="button" data-admin-assistant-prompt="Ayuda con mi módulo"><i class="bi bi-person-workspace" aria-hidden="true"></i> Ayuda con mi módulo</button>
        </div>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form class="assistant-float-form" data-admin-assistant-form>
            <input type="text" name="consulta" maxlength="300" placeholder="Escribe tu consulta..." aria-label="Escribe tu consulta" autocomplete="off">
            <button type="submit" aria-label="Enviar consulta"><i class="bi bi-send-fill" aria-hidden="true"></i></button>
        </form>
        <span class="assistant-float-resizer assistant-float-resizer--e" data-assistant-resize="e" aria-hidden="true"></span>
        <span class="assistant-float-resizer assistant-float-resizer--s" data-assistant-resize="s" aria-hidden="true"></span>
        <span class="assistant-float-resizer assistant-float-resizer--w" data-assistant-resize="w" aria-hidden="true"></span>
        <span class="assistant-float-resizer assistant-float-resizer--n" data-assistant-resize="n" aria-hidden="true"></span>
        <span class="assistant-float-resizer assistant-float-resizer--se" data-assistant-resize="se" aria-hidden="true"></span>
    </section>
</aside>