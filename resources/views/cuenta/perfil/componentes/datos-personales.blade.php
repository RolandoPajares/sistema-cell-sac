<form method="post" action="{{ route($profileRoute) }}" class="tc-profile-card tc-profile-card--personal">
    @csrf @method('PUT')
    <header class="tc-profile-card-heading"><i class="bi bi-person" aria-hidden="true"></i><div><h2>Datos personales</h2><p>Actualiza únicamente los datos personales autorizados para tu cuenta.</p></div></header>
    <div class="tc-profile-fields">
        <label><span><i class="bi bi-person" aria-hidden="true"></i>Nombre completo <b>*</b></span><input name="nombre" value="{{ old('nombre', $user->nombre) }}" maxlength="120" required></label>
        <label><span><i class="bi bi-envelope" aria-hidden="true"></i>Correo electrónico <b>*</b></span><input type="email" name="correo" value="{{ old('correo', $user->correo) }}" maxlength="160" required></label>
        <label><span><i class="bi bi-telephone" aria-hidden="true"></i>Teléfono</span><input value="{{ $phone }}" readonly></label>
        <label><span><i class="bi bi-card-text" aria-hidden="true"></i>Documento</span><input value="{{ $document }}" readonly></label>
        <label class="tc-profile-field--full"><span><i class="bi bi-building" aria-hidden="true"></i>Sede principal</span><input value="{{ $primarySite ?? 'No registrada' }}" readonly></label>
    </div>
    @include('cuenta.perfil.componentes.campos-dinamicos')
    <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button><a class="tc-profile-secondary" href="{{ route($accountHomeRoute) }}">Cancelar</a></footer>
</form>
