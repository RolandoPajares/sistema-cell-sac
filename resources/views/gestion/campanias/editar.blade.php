<form method="post" class="form-card compact-form" action="{{ route('management.campaigns.update', $campaign) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('gestion.campanias.formulario', ['campaign' => $campaign, 'segments' => $segments])
    <div class="campanias-form-actions">
        <button type="button" class="campanias-form-cancel" data-close-campaign-dialog>Cancelar</button>
        <button type="submit" class="campanias-form-submit"><i class="bi bi-check2-circle" aria-hidden="true"></i> Guardar cambios</button>
    </div>
</form>
