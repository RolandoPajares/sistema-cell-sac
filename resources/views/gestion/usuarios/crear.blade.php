<form method="POST" action="{{ route('management.users.store') }}" class="form-card usuarios-form" data-user-form>
    @csrf

    <section class="usuarios-form-section">
        <header class="usuarios-form-section-header"><i class="bi bi-person-lock" aria-hidden="true"></i><h3>Datos de acceso</h3><small>Los campos con <b>*</b> son obligatorios</small></header>
        <div class="usuarios-form-grid">
            <label><span>Nombre completo <b>*</b></span><input type="text" name="nombre" required maxlength="120" value="{{ old('nombre') }}" placeholder="Ej. Juan Pérez García" autocomplete="name"></label>
            <label><span>Correo electrónico <b>*</b></span><input type="email" name="correo" required maxlength="160" value="{{ old('correo') }}" placeholder="usuario@empresa.com" autocomplete="email"></label>
            <label><span>Rol del usuario <b>*</b></span><select name="rol" required data-user-role><option value="">Seleccionar uno de los 7 roles</option>@foreach ($roles as $role)<option value="{{ $role }}" @selected(old('rol') === $role)>{{ $roleLabels[$role] ?? str_replace('_', ' ', $role) }}</option>@endforeach</select></label>
            <label><span>Estado de la cuenta <b>*</b></span><select disabled><option>● Activo</option></select></label>
            <label><span>Contraseña temporal <b>*</b></span><input type="password" name="password" minlength="12" required autocomplete="new-password" placeholder="Mínimo 12 caracteres"><small>Debe contener letras y números.</small></label>
            <label><span>Confirmar contraseña <b>*</b></span><input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password" placeholder="Repite la contraseña"><small>Las contraseñas deben coincidir.</small></label>
        </div>
    </section>

    <section class="usuarios-form-section">
        <header class="usuarios-form-section-header"><i class="bi bi-person-vcard" aria-hidden="true"></i><h3>Información adicional</h3></header>
        <div class="usuarios-form-note" data-user-role-note hidden><i class="bi bi-info-circle" aria-hidden="true"></i> El documento y la razón social se requieren únicamente para los roles de cliente.</div>
        <div class="usuarios-form-grid usuarios-form-grid--additional">
            <label><span>Documento (DNI / RUC)</span><input type="text" name="documento" maxlength="20" value="{{ old('documento') }}" placeholder="N.º de documento" data-user-document></label>
            <label><span>Razón social</span><input type="text" name="razon_social" maxlength="160" value="{{ old('razon_social') }}" placeholder="Ej. Empresa S.A.C."></label>
            <label><span>Teléfono</span><input type="text" name="telefono" maxlength="30" value="{{ old('telefono') }}" placeholder="+51 987 654 321" autocomplete="tel"></label>
            <label><span>Ciudad</span><input type="text" name="ciudad" maxlength="80" value="{{ old('ciudad') }}" placeholder="Lima" autocomplete="address-level2"></label>
        </div>
    </section>

    <section class="usuarios-form-section usuarios-roles-section">
        <header class="usuarios-form-section-header"><i class="bi bi-shield-check" aria-hidden="true"></i><h3>Roles disponibles</h3></header>
        <div class="usuarios-role-chips">@foreach ($roles as $role)<span>{{ $roleLabels[$role] ?? str_replace('_', ' ', $role) }}</span>@endforeach</div>
        <label class="usuarios-notification"><input type="checkbox" name="notificar" value="1" checked><span>Notificar al usuario por correo cuando se habilite el envío seguro de invitaciones.</span></label>
    </section>

    <footer class="usuarios-dialog-footer usuarios-form-footer"><span><i class="bi bi-lock-fill" aria-hidden="true"></i> Los permisos dependen del rol asignado.</span><div><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cancelar</button><button class="usuarios-submit-button" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear usuario</button></div></footer>
</form>
