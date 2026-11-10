@extends('layouts.panel.principal')

@section('body_class', 'modulo-cuenta-admin')
@section('title', 'Perfil')

@section('content')
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

    @include('cuenta.perfil.componentes.foto-perfil')

    <div class="tc-profile-grid">
        @include('cuenta.perfil.componentes.datos-personales')

        <section class="tc-profile-card">
            <header class="tc-profile-card-heading"><i class="bi bi-shield-check" aria-hidden="true"></i><div><h2>Seguridad de la cuenta</h2><p>Gestiona tu contraseña y opciones de seguridad.</p></div></header>
            <div class="tc-profile-security-row"><i class="bi bi-key" aria-hidden="true"></i><div><strong>Contraseña</strong><small>Actualízala verificando la contraseña actual.</small></div><a class="tc-profile-outline" href="#tc-profile-password">Cambiar</a></div>
            <div class="tc-profile-security-row"><i class="bi bi-shield-lock" aria-hidden="true"></i><div><strong>Seguridad avanzada</strong><small>Consulta las opciones disponibles para tu cuenta.</small></div><a class="tc-profile-link" href="{{ route($securityRoute) }}">Consultar</a></div>
            <div class="tc-profile-security-row"><i class="bi bi-display" aria-hidden="true"></i><div><strong>Sesiones activas</strong><small>Consulta y cierra sesiones propias.</small></div><a class="tc-profile-outline" href="{{ route($sessionsRoute) }}">Gestionar</a></div>
        </section>

        <form method="post" action="{{ route($preferencesRoute) }}" class="tc-profile-card" id="tc-profile-preferences">
            @csrf @method('PUT')
            <header class="tc-profile-card-heading"><i class="bi bi-sliders" aria-hidden="true"></i><div><h2>Preferencias</h2><p>Personaliza tu experiencia en la plataforma.</p></div></header>
            <input type="hidden" name="idioma" value="es"><input type="hidden" name="menu_compacto" value="0">
            <label class="tc-profile-switch"><span><b><i class="bi bi-layout-sidebar" aria-hidden="true"></i> Menú lateral compacto</b><small>Conserva tu preferencia visual de navegación.</small></span><input type="checkbox" name="menu_compacto" value="1" @checked($preferences['menu_compacto'] ?? false)><i aria-hidden="true"></i></label>
            <footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar preferencias</button></footer>
        </form>

        @include('cuenta.perfil.componentes.funciones-rol')

        <section class="tc-profile-card">
            <header class="tc-profile-card-heading"><i class="bi bi-display" aria-hidden="true"></i><div><h2>Sesiones activas</h2><p>Gestiona los dispositivos donde tienes una sesión iniciada.</p></div></header>
            <div class="tc-profile-session"><span><i class="bi bi-laptop" aria-hidden="true"></i></span><div><strong>Este dispositivo (actual)</strong><small><i class="bi bi-shield-check" aria-hidden="true"></i> Sesión protegida</small></div><b>Actual</b></div>
            <a class="tc-profile-sessions-link" href="{{ route($sessionsRoute) }}">Ver todas las sesiones <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </section>
    </div>

    <form method="post" action="{{ route($passwordRoute) }}" class="tc-profile-password" id="tc-profile-password">
        @csrf @method('PUT')
        <header class="tc-profile-card-heading"><i class="bi bi-key" aria-hidden="true"></i><div><h2>Cambiar contraseña</h2><p>La sesión se renovará después de actualizarla.</p></div></header>
        <div class="tc-profile-password-fields"><label><span>Contraseña actual</span><input type="password" name="password_actual" required></label><label><span>Nueva contraseña</span><input type="password" name="password" minlength="10" required></label><label><span>Confirmar contraseña</span><input type="password" name="password_confirmation" required></label></div>
        <button class="tc-profile-primary" type="submit"><i class="bi bi-key" aria-hidden="true"></i> Actualizar contraseña</button>
    </form>
</section>

@pushOnce('scripts')
    @vite('resources/js/cuenta/perfil/perfil.js')
@endPushOnce
@endsection
