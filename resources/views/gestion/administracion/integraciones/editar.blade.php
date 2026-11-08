<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-{{ $mode }}-title">{{ $mode === 'crear' ? 'Nuevo' : 'Editar' }} {{ $module['record_singular'] ?? 'registro' }}</h2><p>{{ $mode === 'crear' ? 'Completa la información del nuevo registro.' : 'Actualiza la información del registro seleccionado.' }}</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-database-exclamation" aria-hidden="true"></i><div><strong>Formulario frontend</strong><p>Los campos están preparados visualmente. No se guardará información hasta conectar el módulo con una ruta y un endpoint autorizados.</p></div></div>
        <form data-pending-form>
            @include('gestion.' . $module['key'] . '.formulario', ['module' => $module, 'mode' => $mode])
        </form>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-primary" data-pending-notice="No se guardaron cambios: la integración backend de este módulo está pendiente."><i class="bi bi-save" aria-hidden="true"></i> Guardar cuando se integre</button></footer>
</div>
