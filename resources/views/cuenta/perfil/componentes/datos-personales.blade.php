<form method="post" action="{{ route($profileRoute) }}" class="tc-profile-card tc-profile-card--personal">
    @csrf @method('PUT')
    <header class="tc-profile-card-heading"><i class="bi bi-person" aria-hidden="true"></i><div><h2>Datos personales</h2><p>Actualiza únicamente los datos personales autorizados para tu cuenta.</p></div></header>
    @if (session('status'))
        <p class="tc-profile-form-feedback" role="status">{{ session('status') }}</p>
    @endif
    @if ($errors->any())
        <div class="tc-profile-form-error" role="alert">
            <strong>No se pudieron guardar los cambios.</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="tc-profile-fields">
        <label><span><i class="bi bi-person" aria-hidden="true"></i>Nombre completo <b>*</b></span><input name="nombre" value="{{ old('nombre', $user->nombre) }}" maxlength="120" required @error('nombre') aria-invalid="true" @enderror>@error('nombre')<small class="tc-profile-field-error">{{ $message }}</small>@enderror</label>
        <label><span><i class="bi bi-envelope" aria-hidden="true"></i>Correo electrónico <b>*</b></span><input type="email" name="correo" value="{{ old('correo', $user->correo) }}" maxlength="160" required inputmode="email" autocomplete="email" @error('correo') aria-invalid="true" @enderror>@error('correo')<small class="tc-profile-field-error">{{ $message }}</small>@enderror</label>
        <label><span><i class="bi bi-telephone" aria-hidden="true"></i>Teléfono</span><input name="telefono" value="{{ old('telefono', $phone === 'No registrado' ? '' : $phone) }}" maxlength="30" autocomplete="tel" placeholder="Ingresa tu teléfono" @error('telefono') aria-invalid="true" @enderror>@error('telefono')<small class="tc-profile-field-error">{{ $message }}</small>@enderror</label>
        <label><span><i class="bi bi-card-text" aria-hidden="true"></i>Documento</span><input name="documento" value="{{ old('documento', $document === 'No registrado' ? '' : $document) }}" maxlength="20" placeholder="Ingresa tu documento" @error('documento') aria-invalid="true" @enderror>@error('documento')<small class="tc-profile-field-error">{{ $message }}</small>@enderror</label>
        <label class="tc-profile-field--full"><span><i class="bi bi-building" aria-hidden="true"></i>Sede principal</span><input value="{{ $primarySite ?? 'No registrada' }}" readonly></label>
    </div>
    @include('cuenta.perfil.componentes.campos-dinamicos')
    <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button><a class="tc-profile-secondary" href="{{ route($accountHomeRoute) }}">Cancelar</a></footer>
</form>
