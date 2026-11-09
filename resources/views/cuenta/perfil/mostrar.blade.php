@extends('layouts.panel.principal')

@section('body_class', 'modulo-cuenta-admin')

@section('title', 'Perfil')
@section('content')
{{-- Contenedor principal del perfil autenticado. --}}
<section class="tc-profile-page">
    {{-- Migas de pan para regresar al inicio de la cuenta. --}}
    <nav class="tc-profile-breadcrumb" aria-label="Ruta de perfil">
        <a href="{{ route($accountHomeRoute) }}"><i class="bi bi-house" aria-hidden="true"></i> Inicio</a>
        <i class="bi bi-chevron-right" aria-hidden="true"></i>
        <span>Mi perfil</span>
    </nav>

    <header class="tc-profile-heading">
        <h1>Mi perfil</h1>
        <p>Administra tus datos personales, seguridad y preferencias de cuenta.</p>
    </header>

    {{-- Resumen visual, avatar y carga de fotografía. --}}
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
                <h2>{{ $user?->nombre }}</h2>
                <p><i class="bi bi-envelope" aria-hidden="true"></i>{{ $user?->correo }}</p>
                <div class="tc-profile-badges"><span class="tc-profile-role"><i class="bi bi-award" aria-hidden="true"></i>{{ $roleLabel }}</span><span class="tc-profile-active"><i class="bi bi-check-circle" aria-hidden="true"></i>{{ $user?->activo ? 'Cuenta activa' : 'Cuenta inactiva' }}</span></div>
            </div>
        </div>
        <div class="tc-profile-last-access"><i class="bi bi-calendar3" aria-hidden="true"></i><span><strong>Ãšltimo acceso</strong><small>SesiÃ³n actual validada</small></span></div>
        <div class="tc-profile-completion"><div><strong>Perfil completo <i class="bi bi-info-circle" aria-hidden="true"></i></strong><b>{{ $profileCompletion }}%</b></div><span><i style="width: {{ $profileCompletion }}%"></i></span></div>
        <form method="post" action="{{ route('management.account.avatar') }}" enctype="multipart/form-data" class="tc-profile-photo-form">
            @csrf @method('PUT')
            <label class="tc-profile-photo-button"><i class="bi bi-camera" aria-hidden="true"></i> Editar fotografÃ­a<input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp" required></label>
        </form>
    </section>

    {{-- Formularios y tarjetas de datos personales, seguridad, preferencias y sesiones. --}}
    <div class="tc-profile-grid">
        <form method="post" action="{{ route($profileRoute) }}" class="tc-profile-card tc-profile-card--personal">
            @csrf @method('PUT')
            <header class="tc-profile-card-heading"><i class="bi bi-person" aria-hidden="true"></i><div><h2>Datos personales</h2><p>Actualiza tu informaciÃ³n de contacto e identificaciÃ³n.</p></div></header>
            <div class="tc-profile-fields">
                <label><span><i class="bi bi-person" aria-hidden="true"></i>Nombre completo <b>*</b></span><input name="nombre" value="{{ old('nombre', $user?->nombre) }}" maxlength="120" required></label>
                <label><span><i class="bi bi-envelope" aria-hidden="true"></i>Correo electrÃ³nico <b>*</b></span><input type="email" name="correo" value="{{ old('correo', $user?->correo) }}" maxlength="160" required></label>
                <label><span><i class="bi bi-telephone" aria-hidden="true"></i>TelÃ©fono</span><input value="{{ $phone }}" readonly></label>
                <label><span><i class="bi bi-card-text" aria-hidden="true"></i>Documento</span><input value="{{ $document }}" readonly></label>
                <label class="tc-profile-field--full"><span><i class="bi bi-building" aria-hidden="true"></i>Sede principal</span><input value="No registrada" readonly></label>
            </div>
            <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button><a class="tc-profile-secondary" href="{{ route($accountHomeRoute) }}">Cancelar</a></footer>
        </form>

        <section class="tc-profile-card">
            <header class="tc-profile-card-heading"><i class="bi bi-shield-check" aria-hidden="true"></i><div><h2>Seguridad de la cuenta</h2><p>Gestiona tu contraseÃ±a y opciones de seguridad.</p></div></header>
            <div class="tc-profile-security-row"><i class="bi bi-key" aria-hidden="true"></i><div><strong>ContraseÃ±a</strong><small>Actualiza tu contraseÃ±a para mantener segura tu cuenta.</small></div><a class="tc-profile-outline" href="#tc-profile-password">Cambiar contraseÃ±a</a></div>
            <div class="tc-profile-security-row"><i class="bi bi-phone" aria-hidden="true"></i><div><strong>AutenticaciÃ³n de dos factores (2FA)</strong><small>La configuraciÃ³n avanzada se conserva en Seguridad de la cuenta.</small></div><a class="tc-profile-link" href="{{ route('management.account.security') }}">Consultar</a></div>
            <div class="tc-profile-security-row"><i class="bi bi-clock" aria-hidden="true"></i><div><strong>Estado de la sesiÃ³n</strong><small>Tu acceso actual se encuentra validado.</small></div><span class="tc-profile-status">Activa</span></div>
            <div class="tc-profile-security-row"><i class="bi bi-display" aria-hidden="true"></i><div><strong>Sesiones activas</strong><small>Consulta y cierra sesiones propias en otros dispositivos.</small></div><a class="tc-profile-outline" href="{{ route('management.account.sessions') }}">Gestionar</a></div>
        </section>

        <form method="post" action="{{ route('management.account.preferences.update') }}" class="tc-profile-card" id="tc-profile-preferences">
            @csrf @method('PUT')
            <header class="tc-profile-card-heading"><i class="bi bi-sliders" aria-hidden="true"></i><div><h2>Preferencias</h2><p>Personaliza tu experiencia en la plataforma.</p></div></header>
            <input type="hidden" name="idioma" value="es"><input type="hidden" name="menu_compacto" value="0">
            <label class="tc-profile-select"><span><i class="bi bi-globe" aria-hidden="true"></i>Idioma</span><select disabled><option>EspaÃ±ol (PerÃº)</option></select></label>
            <label class="tc-profile-select"><span><i class="bi bi-clock" aria-hidden="true"></i>Zona horaria</span><select disabled><option>(UTC-05:00) Lima, BogotÃ¡, Quito</option></select></label>
            <label class="tc-profile-switch"><span><i class="bi bi-layout-sidebar" aria-hidden="true"></i><b>MenÃº lateral compacto</b><small>Conserva la preferencia visual de navegaciÃ³n.</small></span><input type="checkbox" name="menu_compacto" value="1" @checked($preferences['menu_compacto'] ?? false)><i aria-hidden="true"></i></label>
            <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar preferencias</button></footer>
        </form>

        <section class="tc-profile-card">
            <header class="tc-profile-card-heading"><i class="bi bi-display" aria-hidden="true"></i><div><h2>Sesiones activas</h2><p>Gestiona los dispositivos donde tienes una sesiÃ³n iniciada.</p></div></header>
            <div class="tc-profile-session"><span><i class="bi bi-laptop" aria-hidden="true"></i></span><div><strong>Este dispositivo (actual)</strong><small><i class="bi bi-browser-chrome" aria-hidden="true"></i> Navegador actual</small><small><i class="bi bi-shield-check" aria-hidden="true"></i> SesiÃ³n protegida</small></div><b>Actual</b></div>
            <a class="tc-profile-sessions-link" href="{{ route('management.account.sessions') }}">Ver todas las sesiones <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>
    </div>

    {{-- Formulario independiente para actualizar la contraseña. --}}
    <form method="post" action="{{ route($passwordRoute) }}" class="tc-profile-password" id="tc-profile-password">
        @csrf @method('PUT')
        <header class="tc-profile-card-heading"><i class="bi bi-key" aria-hidden="true"></i><div><h2>Cambiar contraseÃ±a</h2><p>La sesiÃ³n se renovarÃ¡ despuÃ©s de actualizar la contraseÃ±a.</p></div></header>
        <div class="tc-profile-password-fields"><label><span>ContraseÃ±a actual</span><input type="password" name="password_actual" required></label><label><span>Nueva contraseÃ±a</span><input type="password" name="password" minlength="10" required></label><label><span>Confirmar contraseÃ±a</span><input type="password" name="password_confirmation" required></label></div>
        <button class="tc-profile-primary" type="submit"><i class="bi bi-key" aria-hidden="true"></i> Actualizar contraseÃ±a</button>
    </form>
    {{-- Modal de visualización de la fotografía de perfil. --}}
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
    {{-- JavaScript del diálogo de fotografía, cargado como asset compilado. --}}
    @vite('resources/js/cuenta/perfil.js')
@endPushOnce

@endsection
