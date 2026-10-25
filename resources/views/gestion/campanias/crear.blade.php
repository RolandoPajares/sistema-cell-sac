<form method="POST" action="{{ route('management.campaigns.store') }}" class="form-card">
    @csrf

    <h2>Nueva campaña</h2>

    @include('gestion.campanias.formulario', [
        'campaign' => null,
        'segments' => $segments
    ])

    <button type="submit" class="btn btn-primary">
        Guardar campaña
    </button>
</form>
