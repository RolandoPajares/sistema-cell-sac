@extends('layouts.administracion')

@section('title', 'Roles y permisos')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Seguridad y delegación</span><h1>Roles y matriz de privilegios</h1><p>Los permisos se verifican en rutas y servicios. La matriz solo administra concesiones RBAC.</p></div></header>

        @unless ($ready)
            <div class="admin-alert admin-alert--error" role="status">Las tablas RBAC no están presentes en la base actual. Esta pantalla no crea ni simula roles hasta que se aplique la migración aprobada.</div>
        @else
            <div class="admin-page-actions"><a class="btn btn-outline-primary" href="{{ route('management.user_assignments') }}"><i class="bi bi-person-gear" aria-hidden="true"></i> Asignaciones de usuarios</a><a class="btn btn-outline-primary" href="{{ route('management.menus') }}"><i class="bi bi-list-nested" aria-hidden="true"></i> Configurar menús</a><a class="btn btn-outline-primary" href="{{ route('management.modules') }}"><i class="bi bi-grid" aria-hidden="true"></i> Módulos y pantallas</a></div>

            <form method="post" action="{{ route('management.roles_permissions.store') }}" class="form-card role-create-form">
                @csrf
                <h2>Crear rol configurable</h2>
                <div class="two-column">
                    <label>Código<input name="codigo" maxlength="40" pattern="[a-z][a-z0-9_]*" required placeholder="supervisor_sede"></label>
                    <label>Nombre<input name="nombre" maxlength="120" required></label>
                    <label>Alcance máximo<select name="alcance_permitido" required><option value="sede">Sede</option><option value="propio">Registros propios</option><option value="empresa">Empresa</option></select></label>
                    <label>Descripción<textarea name="descripcion" maxlength="255"></textarea></label>
                </div>
                <button class="btn btn-primary" type="submit">Crear rol sin permisos</button>
            </form>

            <div class="role-matrix-summary"><strong>{{ $roles->count() }} roles configurados</strong><span>{{ $permissionCount }} permisos activos disponibles</span></div>
            @foreach ($roles as $role)
                @php($rolePermissions = $role->permisos->keyBy('id'))
                <details class="form-card role-matrix-card" @if ($loop->first) open @endif>
                    <summary><span><strong>{{ $role->nombre }}</strong><small>{{ $role->codigo }} · alcance {{ $role->alcance_permitido }} · {{ $role->usuarios_count }} asignaciones globales</small></span><i class="bi bi-chevron-down" aria-hidden="true"></i></summary>
                    @if ($role->codigo === 'administrador')
                        <p class="admin-alert admin-alert--success">Rol global del sistema. Conserva privilegios completos y no puede editarse desde esta matriz para evitar bloquear la administración.</p>
                    @else
                        <form method="post" action="{{ route('management.roles_permissions.permissions', $role) }}">
                            @csrf @method('PUT')
                            <label>Alcance de todas las concesiones
                                <select name="alcance" required>
                                    <option value="empresa" @selected($role->alcance_permitido === 'empresa')>Empresa</option>
                                    <option value="sede" @selected($role->alcance_permitido === 'sede')>Sede</option>
                                    <option value="propio" @selected($role->alcance_permitido === 'propio')>Registros propios</option>
                                </select>
                            </label>
                            <div class="permission-modules">
                                @foreach ($permissions as $moduleCode => $modulePermissions)
                                    <fieldset class="permission-module">
                                        <legend>{{ $modulePermissions->first()->modulo_nombre }}</legend>
                                        <div class="permission-grid">
                                            @foreach ($modulePermissions as $permission)
                                                <label><input type="checkbox" name="permisos[]" value="{{ $permission->id }}" @checked($rolePermissions->has($permission->id))> {{ $permission->nombre }}</label>
                                            @endforeach
                                        </div>
                                    </fieldset>
                                @endforeach
                            </div>
                            <button class="btn btn-primary" type="submit">Guardar privilegios</button>
                        </form>
                    @endif
                </details>
            @endforeach
        @endunless
    </section>
@endsection
