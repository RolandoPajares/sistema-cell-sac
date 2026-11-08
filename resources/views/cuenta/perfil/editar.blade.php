@extends('layouts.panel.principal')

@section('body_class', 'modulo-cuenta-admin')

@section('title', 'Perfil')
@section('content')
    @php
        $mode = 'editar';
    @endphp
@php
    $user = auth()->user();
    $roleLabel = 'Usuario autorizado';
    $isAdministrator = $user?->can('gestion.seguridad.consultar') ?? false;
    $profileRoute = $isAdministrator ? 'management.account.profile' : 'account.profile';
    $passwordRoute = $isAdministrator ? 'management.account.password' : 'account.password';
    $accountHomeRoute = $isAdministrator ? 'management.account' : 'dashboard';
    $preferences = (array) ($user?->preferencias ?? []);
    $customer = $user?->relationLoaded('customer') ? $user->customer : null;
    $phone = $customer?->telefono ?: 'No registrado';
    $document = $customer?->documento ?: 'No registrado';
    $photoUrl = $user?->foto_perfil ? \Illuminate\Support\Facades\Storage::disk('public')->url($user->foto_perfil) : null;
    $profileValues = [$user?->nombre, $user?->correo, $user?->foto_perfil, $preferences['idioma'] ?? null];
    $profileCompletion = (int) round((count(array_filter($profileValues, static fn (mixed $value): bool => filled($value))) / count($profileValues)) * 100);
    $initials = mb_strtoupper(collect(preg_split('/\s+/', trim((string) $user?->nombre)) ?: [])->map(static fn (string $part): string => mb_substr($part, 0, 1))->take(2)->implode(''));
@endphp

<section class="tc-profile-page">
    <nav class="tc-profile-breadcrumb" aria-label="Ruta de perfil">
        <a href="{{ route($accountHomeRoute) }}"><i class="bi bi-house" aria-hidden="true"></i> Inicio</a>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
        <span>Mi perfil</span>
    </nav>

    <header class="tc-profile-heading">
        <h1>Mi perfil</h1>
        <p>Administra tus datos personales, seguridad y preferencias de cuenta.</p>
    </header>

    <section class="tc-profile-summary" aria-label="Resumen del perfil">
        <div class="tc-profile-identity">
            <button type="button" class="tc-profile-avatar-button" data-profile-photo-open aria-label="Ver foto de perfil">
            @if ($photoUrl)
                <img class="tc-profile-avatar tc-profile-avatar--image" src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($user->foto_perfil) }}" alt="Fotografía de {{ $user->nombre }}">
            @else
                <span class="tc-profile-avatar">{{ $initials ?: 'U' }}</span>
            @endif
            </button>
            <span class="tc-profile-avatar-status"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
            <div class="tc-profile-identity-copy">
                <h2>{{ $user?->nombre }}</h2>
                <p><i class="bi bi-envelope" aria-hidden="true"></i>{{ $user?->correo }}</p>
                <div class="tc-profile-badges"><span class="tc-profile-role"><i class="bi bi-award" aria-hidden="true"></i>{{ $roleLabel }}</span><span class="tc-profile-active"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $user?->activo ? 'Cuenta activa' : 'Cuenta inactiva' }}</span></div>
            </div>
        </div>
        <div class="tc-profile-last-access"><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Último acceso</strong><small>Sesión actual validada</small></span></div>
        <div class="tc-profile-completion"><div><strong>Perfil completo <i class="bi bi-info-circle" aria-hidden="true"></i></strong><b>{{ $profileCompletion }}%</b></div><span><i style="width: {{ $profileCompletion }}%"></i></span></div>
        <form method="post" action="{{ route('management.account.avatar') }}" enctype="multipart/form-data" class="tc-profile-photo-form">
            @csrf @method('PUT')
            <label class="tc-profile-photo-button"><i class="bi bi-camera" aria-hidden="true"></i> Editar fotografía<input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp" required></label>
        </form>
    </section>

    <div class="tc-profile-grid">
        <form method="post" action="{{ route($profileRoute) }}" class="tc-profile-card tc-profile-card--personal">
            @csrf @method('PUT')
            <header class="tc-profile-card-heading"><i class="bi bi-person" aria-hidden="true"></i><div><h2>Datos personales</h2><p>Actualiza tu información de contacto e identificación.</p></div></header>
            <div class="tc-profile-fields">
                <label><span><i class="bi bi-person" aria-hidden="true"></i>Nombre completo <b>*</b></span><input name="nombre" value="{{ old('nombre', $user?->nombre) }}" maxlength="120" required></label>
                <label><span><i class="bi bi-envelope" aria-hidden="true"></i>Correo electrónico <b>*</b></span><input type="email" name="correo" value="{{ old('correo', $user?->correo) }}" maxlength="160" required></label>
                <label><span><i class="bi bi-telephone" aria-hidden="true"></i>Teléfono</span><input value="{{ $phone }}" readonly></label>
                <label><span><i class="bi bi-card-text" aria-hidden="true"></i>Documento</span><input value="{{ $document }}" readonly></label>
                <label class="tc-profile-field--full"><span><i class="bi bi-building" aria-hidden="true"></i>Sede principal</span><input value="No registrada" readonly></label>
            </div>
            <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button><a class="tc-profile-secondary" href="{{ route($accountHomeRoute) }}">Cancelar</a></footer>
        </form>

        <section class="tc-profile-card">
            <header class="tc-profile-card-heading"><i class="bi bi-shield-check" aria-hidden="true"></i><div><h2>Seguridad de la cuenta</h2><p>Gestiona tu contraseña y opciones de seguridad.</p></div></header>
            <div class="tc-profile-security-row"><i class="bi bi-key" aria-hidden="true"></i><div><strong>Contraseña</strong><small>Actualiza tu contraseña para mantener segura tu cuenta.</small></div><a class="tc-profile-outline" href="#tc-profile-password">Cambiar contraseña</a></div>
            <div class="tc-profile-security-row"><i class="bi bi-phone" aria-hidden="true"></i><div><strong>Autenticación de dos factores (2FA)</strong><small>La configuración avanzada se conserva en Seguridad de la cuenta.</small></div><a class="tc-profile-link" href="{{ route('management.account.security') }}">Consultar</a></div>
            <div class="tc-profile-security-row"><i class="bi bi-clock" aria-hidden="true"></i><div><strong>Estado de la sesión</strong><small>Tu acceso actual se encuentra validado.</small></div><span class="tc-profile-status">Activa</span></div>
            <div class="tc-profile-security-row"><i class="bi bi-display" aria-hidden="true"></i><div><strong>Sesiones activas</strong><small>Consulta y cierra sesiones propias en otros dispositivos.</small></div><a class="tc-profile-outline" href="{{ route('management.account.sessions') }}">Gestionar</a></div>
        </section>

        <form method="post" action="{{ route('management.account.preferences.update') }}" class="tc-profile-card" id="tc-profile-preferences">
            @csrf @method('PUT')
            <header class="tc-profile-card-heading"><i class="bi bi-sliders" aria-hidden="true"></i><div><h2>Preferencias</h2><p>Personaliza tu experiencia en la plataforma.</p></div></header>
            <input type="hidden" name="idioma" value="es"><input type="hidden" name="menu_compacto" value="0">
            <label class="tc-profile-select"><span><i class="bi bi-globe" aria-hidden="true"></i>Idioma</span><select disabled><option>Español (Perú)</option></select></label>
            <label class="tc-profile-select"><span><i class="bi bi-clock" aria-hidden="true"></i>Zona horaria</span><select disabled><option>(UTC-05:00) Lima, Bogotá, Quito</option></select></label>
            <label class="tc-profile-switch"><span><i class="bi bi-layout-sidebar" aria-hidden="true"></i><b>Menú lateral compacto</b><small>Conserva la preferencia visual de navegación.</small></span><input type="checkbox" name="menu_compacto" value="1" @checked($preferences['menu_compacto'] ?? false)><i aria-hidden="true"></i></label>
            <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar preferencias</button></footer>
        </form>

        <section class="tc-profile-card">
            <header class="tc-profile-card-heading"><i class="bi bi-display" aria-hidden="true"></i><div><h2>Sesiones activas</h2><p>Gestiona los dispositivos donde tienes una sesión iniciada.</p></div></header>
            <div class="tc-profile-session"><span><i class="bi bi-laptop" aria-hidden="true"></i></span><div><strong>Este dispositivo (actual)</strong><small><i class="bi bi-browser-chrome" aria-hidden="true"></i> Navegador actual</small><small><i class="bi bi-shield-check" aria-hidden="true"></i> Sesión protegida</small></div><b>Actual</b></div>
            <a class="tc-profile-sessions-link" href="{{ route('management.account.sessions') }}">Ver todas las sesiones <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>
    </div>

    <form method="post" action="{{ route($passwordRoute) }}" class="tc-profile-password" id="tc-profile-password">
        @csrf @method('PUT')
        <header class="tc-profile-card-heading"><i class="bi bi-key" aria-hidden="true"></i><div><h2>Cambiar contraseña</h2><p>La sesión se renovará después de actualizar la contraseña.</p></div></header>
        <div class="tc-profile-password-fields"><label><span>Contraseña actual</span><input type="password" name="password_actual" required></label><label><span>Nueva contraseña</span><input type="password" name="password" minlength="10" required></label><label><span>Confirmar contraseña</span><input type="password" name="password_confirmation" required></label></div>
        <button class="tc-profile-primary" type="submit"><i class="bi bi-key" aria-hidden="true"></i> Actualizar contraseña</button>
    </form>
    <dialog class="tc-profile-photo-dialog" data-profile-photo-dialog aria-labelledby="tc-profile-photo-title">
        <div class="tc-profile-photo-dialog-card">
            <header><h2 id="tc-profile-photo-title">Foto de perfil</h2><button type="button" data-profile-photo-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
            @if ($photoUrl)
                <img src="{{ $photoUrl }}" alt="Foto de {{ $user->nombre }}">
            @else
                <div class="tc-profile-photo-dialog-placeholder"><span class="tc-profile-avatar">{{ $initials ?: 'U' }}</span><p>Aun no has cargado una foto de perfil.</p></div>
            @endif
            <footer><button type="button" class="tc-profile-secondary" data-profile-photo-close>Cerrar</button></footer>
        </div>
    </dialog>
</section>

@pushOnce('scripts')
    <script>
        (() => {
            const dialog = document.querySelector('[data-profile-photo-dialog]');
            const openButton = document.querySelector('[data-profile-photo-open]');
            if (!dialog || !openButton) return;

            openButton.addEventListener('click', () => dialog.showModal());
            dialog.querySelectorAll('[data-profile-photo-close]').forEach((button) => {
                button.addEventListener('click', () => dialog.close());
            });
            dialog.addEventListener('click', (event) => {
                if (event.target === dialog) dialog.close();
            });
        })();
    </script>
@endPushOnce

@endsection
