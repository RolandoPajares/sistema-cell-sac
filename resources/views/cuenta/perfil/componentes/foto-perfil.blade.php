<section class="tc-profile-summary" aria-label="Resumen del perfil">
    <div class="tc-profile-identity">
        <button type="button" class="tc-profile-avatar-button" data-profile-photo-open aria-label="Ver foto de perfil">
            @if ($photoUrl)
                <img class="tc-profile-avatar tc-profile-avatar--image" src="{{ $photoUrl }}" alt="Fotografía de {{ $user->nombre }}">
            @else
                <span class="tc-profile-avatar">{{ $initials ?: 'U' }}</span>
            @endif
        </button>
        <span class="tc-profile-avatar-status"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
        <div class="tc-profile-identity-copy">
            <h2>{{ $user->nombre }}</h2>
            <p><i class="bi bi-envelope" aria-hidden="true"></i>{{ $user->correo }}</p>
            <div class="tc-profile-badges"><span class="tc-profile-role"><i class="bi bi-award" aria-hidden="true"></i>{{ $roleLabel }}</span><span class="tc-profile-active"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $user->activo ? 'Cuenta activa' : 'Cuenta inactiva' }}</span></div>
        </div>
    </div>
    <div class="tc-profile-last-access"><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Último acceso</strong><small>Sesión actual validada</small></span></div>
    <div class="tc-profile-completion"><div><strong>Perfil completo <i class="bi bi-info-circle" aria-hidden="true"></i></strong><b>{{ $profileCompletion }}%</b></div><span><i style="width: {{ $profileCompletion }}%"></i></span></div>
    <div class="tc-profile-photo-actions">
        <form method="post" action="{{ route($avatarRoute) }}" enctype="multipart/form-data" class="tc-profile-photo-form">
            @csrf @method('PUT')
            <label class="tc-profile-photo-button"><i class="bi bi-camera" aria-hidden="true"></i> Cambiar fotografía<input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp" data-profile-photo-input required></label>
        </form>
        @if ($photoUrl)
            <form method="post" action="{{ route($avatarDeleteRoute) }}" class="tc-profile-photo-delete-form">
                @csrf @method('DELETE')
                <button class="tc-profile-secondary" type="submit">Eliminar</button>
            </form>
        @endif
    </div>
</section>

<dialog class="tc-profile-photo-dialog" data-profile-photo-dialog aria-labelledby="tc-profile-photo-title">
    <div class="tc-profile-photo-dialog-card">
        <header><h2 id="tc-profile-photo-title">Foto de perfil</h2><button type="button" data-profile-photo-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
        @if ($photoUrl)
            <img src="{{ $photoUrl }}" alt="Foto de {{ $user->nombre }}">
        @else
            <div class="tc-profile-photo-dialog-placeholder"><span class="tc-profile-avatar">{{ $initials ?: 'U' }}</span><p>Aún no has cargado una foto de perfil.</p></div>
        @endif
        <footer><button type="button" class="tc-profile-secondary" data-profile-photo-close>Cerrar</button></footer>
    </div>
</dialog>
