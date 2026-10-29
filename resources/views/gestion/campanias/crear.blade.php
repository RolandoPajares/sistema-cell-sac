<form method="POST" action="{{ route('management.campaigns.store') }}" class="form-card" enctype="multipart/form-data">
    @csrf

    <h2>Nueva campaña</h2>

    @include('gestion.campanias.formulario', [
        'campaign' => null,
        'segments' => $segments
    ])

    <div class="campanias-form-actions">
        <button type="button" class="campanias-form-cancel" data-close-campaign-dialog>Cancelar</button>
        <button type="submit" class="campanias-form-submit"><i class="bi bi-send" aria-hidden="true"></i> Guardar campaña</button>
    </div>
</form>
