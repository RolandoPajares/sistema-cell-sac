<form method="post" class="form-card compact-form" action="{{ route('management.segments.store') }}">
    @csrf
    <label>
        Nombre
        <input name="nombre" required maxlength="120">
    </label>

    <fieldset>
        <legend>Roles incluidos</legend>
        @foreach($roles as $role)
            <label class="check">
                <input type="checkbox" name="roles[]" value="{{ $role }}">
                {{ str_replace('_', ' ', $role) }}
            </label>
        @endforeach
    </fieldset>
    <button class="btn btn-primary">Crear segmento</button>
</form>
