<form method="post" class="form-card compact-form" action="{{ route('management.campaigns.update', $campaign) }}">
    @csrf
    @method('PUT')
    @include('gestion.campanias.formulario', ['campaign' => $campaign, 'segments' => $segments])
    <button class="btn btn-primary">Actualizar campaña</button>
</form>
