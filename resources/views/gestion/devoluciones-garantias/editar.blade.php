<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">Operaciones comerciales</span><h2 id="devoluciones-garantias-editar-title">Editar solicitud</h2><p>Actualiza los datos y el estado del caso seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <form method="POST" action="#" data-pending-backend-form>
        @csrf
        @method('PUT')
        <div class="gestion-pendiente-modal-body">
            @include('gestion.devoluciones-garantias.formulario', ['module' => $module, 'mode' => 'editar'])
        </div>
        <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="submit" class="gestion-pendiente-primary"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></footer>
    </form>
</div>
