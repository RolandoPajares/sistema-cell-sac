@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-cuenta-admin')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Mi perfil')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- logica auxiliar de la vista que prepara valores para la presentacion. --}}
@php
    $rolActual = $roles->first();
    $nombrePartes = preg_split('/\s+/', trim((string) $user->nombre), 2) ?: [];
@endphp
<section class="perfil-referencia" data-profile-page>
    <nav class="perfil-referencia-breadcrumb" aria-label="Ruta de perfil">
        <a href="{{ route($accountHomeRoute) }}"><i class="bi bi-house" aria-hidden="true"></i> Inicio</a><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Mi perfil</span>
    </nav>

    <header class="perfil-referencia-heading"><i class="bi bi-person" aria-hidden="true"></i><div><h1>Mi perfil</h1><p>Administra tu información personal, seguridad y preferencias.</p></div></header>

    <div class="perfil-referencia-layout">
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('cuenta.perfil.componentes.foto-perfil')

        <main class="perfil-referencia-main">
            <div class="perfil-referencia-tabs" role="tablist" aria-label="Secciones del perfil">
                <button class="is-active" type="button" data-profile-tab="personal"><i class="bi bi-person" aria-hidden="true"></i> Información personal</button>
                <button type="button" data-profile-tab="seguridad"><i class="bi bi-lock" aria-hidden="true"></i> Seguridad</button>
                <button type="button" data-profile-tab="preferencias"><i class="bi bi-sliders" aria-hidden="true"></i> Preferencias</button>
                <button type="button" data-profile-tab="actividad"><i class="bi bi-activity" aria-hidden="true"></i> Actividad</button>
            </div>

            <section class="perfil-referencia-panel is-active" data-profile-panel="personal">
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
                @include('cuenta.perfil.componentes.datos-personales')
            </section>
            <section class="perfil-referencia-panel" data-profile-panel="seguridad" hidden>
                <section class="tc-profile-card"><header class="tc-profile-card-heading"><i class="bi bi-shield-check" aria-hidden="true"></i><div><h2>Seguridad de la cuenta</h2><p>Gestiona tu contraseña y opciones de seguridad.</p></div></header><div class="tc-profile-security-row"><i class="bi bi-key" aria-hidden="true"></i><div><strong>Contraseña</strong><small>Actualízala verificando la contraseña actual.</small></div><a class="tc-profile-outline" href="#tc-profile-password">Cambiar</a></div><div class="tc-profile-security-row"><i class="bi bi-shield-lock" aria-hidden="true"></i><div><strong>Seguridad avanzada</strong><small>Consulta las opciones disponibles para tu cuenta.</small></div><a class="tc-profile-link" href="{{ route($securityRoute) }}">Consultar</a></div><div class="tc-profile-security-row"><i class="bi bi-display" aria-hidden="true"></i><div><strong>Sesiones activas</strong><small>Consulta y cierra sesiones propias.</small></div><a class="tc-profile-outline" href="{{ route($sessionsRoute) }}">Gestionar</a></div></section>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                <form method="post" action="{{ route($passwordRoute) }}" class="tc-profile-password" id="tc-profile-password">@csrf @method('PUT')<header class="tc-profile-card-heading"><i class="bi bi-key" aria-hidden="true"></i><div><h2>Cambiar contraseña</h2><p>La sesión se renovará después de actualizarla.</p></div></header><div class="tc-profile-password-fields"><label><span>Contraseña actual</span><input type="password" name="password_actual" required></label><label><span>Nueva contraseña</span><input type="password" name="password" minlength="10" required></label><label><span>Confirmar contraseña</span><input type="password" name="password_confirmation" required></label></div><button class="tc-profile-primary" type="submit"><i class="bi bi-key" aria-hidden="true"></i> Actualizar contraseña</button></form>
            </section>
            <section class="perfil-referencia-panel" data-profile-panel="preferencias" hidden>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                <form method="post" action="{{ route($preferencesRoute) }}" class="tc-profile-card" id="tc-profile-preferences">@csrf @method('PUT')<header class="tc-profile-card-heading"><i class="bi bi-sliders" aria-hidden="true"></i><div><h2>Preferencias</h2><p>Personaliza tu experiencia en la plataforma.</p></div></header><input type="hidden" name="idioma" value="es"><input type="hidden" name="menu_compacto" value="0"><label class="tc-profile-switch"><span><b><i class="bi bi-layout-sidebar" aria-hidden="true"></i> Menú lateral compacto</b><small>Conserva tu preferencia visual de navegación.</small></span><input type="checkbox" name="menu_compacto" value="1" @checked($preferences['menu_compacto'] ?? false)><i aria-hidden="true"></i></label><footer class="tc-profile-card-actions"><button class="tc-profile-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar preferencias</button></footer></form>
            </section>
            <section class="perfil-referencia-panel" data-profile-panel="actividad" hidden>
                <section class="tc-profile-card"><header class="tc-profile-card-heading"><i class="bi bi-activity" aria-hidden="true"></i><div><h2>Actividad de la cuenta</h2><p>Consulta la actividad y las sesiones relacionadas con tu usuario.</p></div></header><div class="tc-profile-session"><span><i class="bi bi-laptop" aria-hidden="true"></i></span><div><strong>Este dispositivo (actual)</strong><small><i class="bi bi-shield-check" aria-hidden="true"></i> Sesión protegida</small></div><b>Actual</b></div><a class="tc-profile-sessions-link" href="{{ route($sessionsRoute) }}">Ver todas las sesiones <i class="bi bi-arrow-right" aria-hidden="true"></i></a></section></section>
        </main>

        <aside class="perfil-referencia-aside">
            <section class="perfil-rol-card"><header><i class="bi bi-crown" aria-hidden="true"></i><h2>Mi rol</h2></header><span class="perfil-rol-actual">{{ $rolActual?->nombre ?: 'Usuario autorizado' }}</span><p>{{ $rolActual?->descripcion ?: 'Acceso determinado por los roles y permisos vigentes.' }}</p></section>
            <section class="perfil-permisos-card"><header><i class="bi bi-shield-check" aria-hidden="true"></i><h2>Permisos principales</h2></header><ul>@forelse ($functions->take(6) as $function)<li><i class="bi bi-check-circle-fill" aria-hidden="true"></i>{{ $function->nombre }}</li>@empty<li class="is-empty">No hay permisos asignados</li>@endforelse</ul></section>
            <section class="perfil-cambio-rol"><header><i class="bi bi-eye" aria-hidden="true"></i><h2>Cambiar rol de vista <small>(solo ver)</small></h2></header><select aria-label="Rol de vista" data-profile-role-view>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($rolActual?->id === $role->id)>{{ $role->nombre }}{{ $role->id === $rolActual?->id ? ' (actual)' : '' }}</option>@endforeach</select><p>La vista refleja los permisos del rol vigente; cambiar este selector no modifica autorizaciones.</p></section>
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
            @include('cuenta.perfil.componentes.funciones-rol')
        </aside>
    </div>
</section>

@pushOnce('scripts')
    @vite('resources/js/cuenta/perfil/perfil.js')
@endPushOnce
@endsection
