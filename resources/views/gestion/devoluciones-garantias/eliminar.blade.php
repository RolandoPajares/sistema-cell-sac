<div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">Operaciones comerciales</span><h2 id="devoluciones-garantias-eliminar-title">Eliminar solicitud</h2><p>Confirma la eliminación del caso seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <form method="POST" action="#" data-pending-backend-form>
        @csrf
        @method('DELETE')
        <div class="gestion-pendiente-modal-body">
            <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Esta acción no se puede deshacer</strong><p>Se eliminará la solicitud <strong data-pending-detail="codigo">seleccionada</strong> del listado administrativo.</p></div></div>
        </div>
        <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="submit" class="gestion-pendiente-danger"><i class="bi bi-trash3" aria-hidden="true"></i> Eliminar solicitud</button></footer>
    </form>
</div>
