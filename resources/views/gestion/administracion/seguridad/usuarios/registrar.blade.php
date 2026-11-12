{{-- Formulario de creación de una cuenta de usuario. --}}
<form method="POST" action="{{ route('management.users.store') }}" class="form-card usuarios-form" data-user-form>
    @csrf

    {{-- Errores generales y errores específicos devueltos por la validación del servidor. --}}
    @if ($errors->any())
    <div class="admin-alert admin-alert--error usuarios-form-errors" role="alert">
        <strong>No se pudo registrar la cuenta.</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Datos necesarios para autenticar y clasificar la nueva cuenta. --}}
    <section class="usuarios-form-section">
        <header class="usuarios-form-section-header"><i class="bi bi-person-lock" aria-hidden="true"></i>
            <h3>Datos de acceso</h3><small>Los campos con <b>*</b> son obligatorios</small>
        </header>
        <div class="usuarios-form-grid">
            <label><span>Nombre completo <b>*</b></span><input type="text" name="nombre" required maxlength="120" value="{{ old('nombre') }}" placeholder="Ej. Juan Pérez García" autocomplete="name" @error('nombre') aria-invalid="true" @enderror>@error('nombre')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label><span>Correo electrónico <b>*</b></span><input type="email" name="correo" required maxlength="160" value="{{ old('correo') }}" placeholder="usuario@empresa.com" autocomplete="email" inputmode="email" @error('correo') aria-invalid="true" @enderror>@error('correo')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label><span>Rol del usuario <b>*</b></span><select name="rol" required data-user-role @error('rol') aria-invalid="true" @enderror>
                    <option value="">Seleccionar rol configurable</option>@foreach ($roles as $role)<option value="{{ $role }}" data-scope="{{ $roleScopes[$role] ?? '' }}" @selected(old('rol')===$role)>{{ $roleLabels[$role] ?? str_replace('_', ' ', $role) }}</option>@endforeach
                </select>@error('rol')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <div class="usuarios-form-note" data-user-site-field hidden><i class="bi bi-geo-alt" aria-hidden="true"></i> La sede interna autorizada se asigna automáticamente según el contexto de la empresa.</div>
            <label><span>Estado de la cuenta <b>*</b></span><select disabled>
                    <option>● Activo</option>
                </select></label>
            <label><span>Contraseña temporal <b>*</b></span><input type="password" name="password" minlength="12" required autocomplete="new-password" placeholder="Mínimo 12 caracteres" @error('password') aria-invalid="true" @enderror><small>Debe contener letras y números.</small>@error('password')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label><span>Confirmar contraseña <b>*</b></span><input type="password" name="password_confirmation" minlength="12" required autocomplete="new-password" placeholder="Repite la contraseña" @error('password_confirmation') aria-invalid="true" @enderror><small>Las contraseñas deben coincidir.</small>@error('password_confirmation')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
        </div>
    </section>

    {{-- Datos adicionales solicitados únicamente para cuentas de cliente. --}}
    <section class="usuarios-form-section" data-user-customer-section hidden>
        <header class="usuarios-form-section-header"><i class="bi bi-person-vcard" aria-hidden="true"></i>
            <h3>Información adicional</h3>
        </header>
        <div class="usuarios-form-note" data-user-role-note hidden><i class="bi bi-info-circle" aria-hidden="true"></i> Estos datos solo aplican a los roles de cliente.</div>
        <div class="usuarios-form-grid usuarios-form-grid--additional">
            <label data-user-customer-field><span>Tipo de documento <b>*</b></span><select name="tipo_documento" data-user-document-type required>
                    <option value="">Seleccionar tipo</option>
                    <option value="dni" @selected(old('tipo_documento') === 'dni')>DNI — persona natural</option>
                    <option value="ruc" @selected(old('tipo_documento') === 'ruc')>RUC — empresa o negocio</option>
                </select>@error('tipo_documento')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label data-user-customer-field><span data-user-document-label>Documento <b data-user-customer-required>*</b></span><input type="text" name="documento" maxlength="11" value="{{ old('documento') }}" placeholder="Selecciona DNI o RUC" inputmode="numeric" data-user-document @error('documento') aria-invalid="true" @enderror>@error('documento')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label data-user-customer-field data-user-ruc-field><span>Razón social <b>*</b></span><input type="text" name="razon_social" maxlength="160" value="{{ old('razon_social') }}" placeholder="Ej. Empresa S.A.C." @error('razon_social') aria-invalid="true" @enderror>@error('razon_social')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label data-user-customer-field><span>Teléfono</span><input type="text" name="telefono" maxlength="30" value="{{ old('telefono') }}" placeholder="+51 987 654 321" autocomplete="tel" @error('telefono') aria-invalid="true" @enderror>@error('telefono')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
            <label data-user-customer-field><span>Ciudad</span><input type="text" name="ciudad" maxlength="80" value="{{ old('ciudad') }}" placeholder="Lima" autocomplete="address-level2" @error('ciudad') aria-invalid="true" @enderror>@error('ciudad')<small class="usuarios-field-error">{{ $message }}</small>@enderror</label>
        </div>
    </section>

    {{-- Resumen visual de los roles configurables y preferencia de notificación. --}}
    <section class="usuarios-form-section usuarios-roles-section">
        <header class="usuarios-form-section-header"><i class="bi bi-shield-check" aria-hidden="true"></i>
            <h3>Roles disponibles</h3>
        </header>
        <div class="usuarios-role-chips">@foreach ($roles as $role)<span>{{ $roleLabels[$role] ?? str_replace('_', ' ', $role) }}</span>@endforeach</div>
        <label class="usuarios-notification"><input type="checkbox" name="notificar" value="1" checked><span>Notificar al usuario por correo cuando se habilite el envío seguro de invitaciones.</span></label>
    </section>

    {{-- Acciones finales del formulario y recordatorio de la relación entre rol y permisos. --}}
    <footer class="usuarios-dialog-footer usuarios-form-footer"><span><i class="bi bi-lock-fill" aria-hidden="true"></i> Los permisos dependen del rol asignado.</span>
        <div><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cancelar</button><button class="usuarios-submit-button" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear usuario</button></div>
    </footer>
</form>
