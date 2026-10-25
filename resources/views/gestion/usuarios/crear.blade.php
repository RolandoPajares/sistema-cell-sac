<form method="POST" action="{{ route('management.users.store') }}" class="form-card compact-form">
    @csrf
    <label>
        Nombre
        <input type="text" name="nombre" required maxlength="120">
    </label>

    <label>
        Correo
        <input type="email" name="correo" required maxlength="160">
    </label>

    <label>
        Rol
        <select name="rol" required>
            @foreach($roles as $role)
                <option value="{{ $role }}">{{ str_replace('_', ' ', $role) }}</option>
            @endforeach
        </select>
    </label>

    <label>
        Contraseña temporal
        <input type="password" name="password" minlength="12" required autocomplete="new-password">
    </label>

    <label>
        Documento (obligatorio para clientes)
        <input type="text" name="documento" maxlength="20">
    </label>

    <label>
        Razón social
        <input type="text" name="razon_social" maxlength="160">
    </label>

    <label>
        Teléfono
        <input type="text" name="telefono" maxlength="30">
    </label>

    <label>
        Ciudad
        <input type="text" name="ciudad" maxlength="80">
    </label>
    <button type="submit" class="btn btn-primary">Crear cuenta</button>
</form>
