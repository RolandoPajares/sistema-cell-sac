
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
<form method="post" class="form-card compact-form" action="{{ route('management.segments.store') }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
    @csrf
    <label>
        Nombre
        <input name="nombre" required maxlength="120">
    </label>

    <fieldset>
        <legend>Roles incluidos</legend>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach($roles as $role)
            <label class="check">
                <input type="checkbox" name="roles[]" value="{{ $role }}">
                {{ str_replace('_', ' ', $role) }}
            </label>
        @endforeach
    </fieldset>
    <button class="btn btn-primary">Crear segmento</button>
</form>
