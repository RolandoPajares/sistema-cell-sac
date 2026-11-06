@extends('layouts.administracion')

@section('title', 'Matriz de privilegios')
@section('body_class', 'modulo-seguridad-rbac')

@section('content')
<section class="tc-rbac-page tc-privilege-matrix" data-matrix-page>
    <header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegación</span><h1>Matriz de privilegios</h1><p>Visualiza y administra permisos por rol, módulo y acción.</p></div></header>
    @unless ($ready)
        <div class="tc-rbac-alert" role="alert">El esquema RBAC no está disponible.</div>
    @else
        <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a class="is-active" href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>

        <section class="tc-matrix-kpis" aria-label="Resumen de privilegios">
            <article><span class="tc-matrix-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Roles configurados</small><strong>{{ $roles->count() }}</strong><em>Datos reales</em></div></article>
            <article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--purple"><i class="bi bi-box" aria-hidden="true"></i></span><div><small>Módulos activos</small><strong>{{ $moduleCount }}</strong><em>Catálogo RBAC</em></div></article>
            <article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--cyan"><i class="bi bi-display" aria-hidden="true"></i></span><div><small>Pantallas activas</small><strong>{{ $screenCount }}</strong><em>Rutas registradas</em></div></article>
            <article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--green"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><small>Permisos asignados</small><strong>{{ $assignedPermissionCount }}</strong><em>Relaciones activas</em></div></article>
        </section>

        <form class="tc-matrix-toolbar" method="get" action="{{ route('management.permissions_matrix') }}">
            <label class="tc-matrix-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar</span><input type="search" name="buscar" value="{{ request('buscar') }}" data-matrix-filter placeholder="Buscar por permiso o módulo…"></label>
            <label>Rol<select name="rol" onchange="this.form.submit()">@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($selectedRole?->id === $role->id)>{{ $role->nombre }}</option>@endforeach</select></label>
            <button class="tc-rbac-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button>
        </form>

        @if ($selectedRole)
            @php($selectedPermissionIds = $selectedRole->permisos->pluck('id')->map(fn ($id) => (int) $id)->all())
            <form method="post" action="{{ route('management.roles_permissions.permissions', $selectedRole) }}" data-permission-form>@csrf @method('PUT')<input type="hidden" name="alcance" value="{{ $selectedRole->alcance_permitido }}">
                <div class="tc-matrix-layout"><div class="tc-matrix-card"><div class="tc-matrix-scroll"><table class="tc-matrix-table"><thead><tr><th class="tc-matrix-resource-col">MÓDULO / PANTALLA</th>@foreach ($roles as $role)<th><span class="tc-matrix-role-chip tc-matrix-role-chip--{{ $loop->index % 5 }}"><i class="bi bi-shield" aria-hidden="true"></i>{{ $role->nombre }}</span><small>{{ $role->codigo }}</small></th>@endforeach</tr></thead><tbody>
                    @foreach ($permissions as $modulePermissions)
                        @php($module = $modulePermissions->first())
                        <tr class="tc-matrix-module-row" data-matrix-row data-search="{{ strtolower($module->modulo_nombre.' '.$module->modulo_codigo) }}"><th><i class="bi bi-chevron-down" aria-hidden="true"></i><i class="bi bi-folder2-open" aria-hidden="true"></i><strong>{{ $module->modulo_nombre }}</strong><span class="tc-rbac-status tc-rbac-status--active">Activo</span></th>@foreach ($roles as $role)<td><span class="tc-matrix-action-summary">{{ $role->permisos->whereIn('id', $modulePermissions->pluck('id'))->count() }} / {{ $modulePermissions->count() }}</span></td>@endforeach</tr>
                        @foreach ($modulePermissions->groupBy(fn ($permission) => $permission->pantalla_id ?: 'module-'.$permission->modulo_codigo) as $screenPermissions)
                            @php($firstPermission = $screenPermissions->first())
                            <tr data-matrix-row data-search="{{ strtolower(($firstPermission->pantalla_nombre ?: '').' '.$firstPermission->modulo_nombre.' '.$screenPermissions->pluck('nombre')->join(' ')) }}"><th class="tc-matrix-screen"><i class="bi bi-{{ $firstPermission->pantalla_id ? 'window' : 'folder' }}" aria-hidden="true"></i><span>{{ $firstPermission->pantalla_nombre ?: $firstPermission->modulo_nombre }}</span><small>{{ $screenPermissions->count() }} acciones disponibles</small></th>@foreach ($roles as $role)<td><div class="tc-matrix-toggles">@foreach ($screenPermissions as $permission)<label class="tc-matrix-toggle" title="{{ $permission->nombre }}"><input type="checkbox" name="permisos[]" value="{{ $permission->id }}" @checked(in_array((int) $permission->id, $selectedPermissionIds, true) && $role->id === $selectedRole->id) @disabled($role->id !== $selectedRole->id || $selectedRole->codigo === 'administrador')><span></span><b class="visually-hidden">{{ $permission->nombre }}</b></label>@endforeach</div></td>@endforeach</tr>
                        @endforeach
                    @endforeach
                </tbody></table></div><footer class="tc-matrix-footer"><span data-saved-state>Rol editable: <strong>{{ $selectedRole->nombre }}</strong>. Los demás roles se muestran como consulta.</span><button class="tc-rbac-primary" type="submit" @disabled($selectedRole->codigo === 'administrador')><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></footer></div>
                    <aside class="tc-matrix-legend"><h2><i class="bi bi-info-circle" aria-hidden="true"></i> Leyenda</h2><div><span class="tc-legend-switch is-on"></span><p><strong>Permiso habilitado</strong><small>El rol puede realizar la acción.</small></p></div><div><span class="tc-legend-switch"></span><p><strong>Permiso deshabilitado</strong><small>El rol no puede realizar la acción.</small></p></div><hr><h3>Acciones reales</h3>@foreach ($permissions->flatten()->pluck('accion')->unique()->take(8) as $action)<span class="tc-legend-action"><b>{{ strtoupper(mb_substr($action, 0, 1)) }}</b>{{ ucfirst($action) }}</span>@endforeach</aside>
                </div>
            </form>
        @else
            <div class="tc-rbac-alert">No hay roles activos disponibles.</div>
        @endif
    @endunless
</section>
@endsection

@push('scripts')
<script>
(() => { const page = document.querySelector('[data-matrix-page]'); if (!page) return; const filter = page.querySelector('[data-matrix-filter]'); filter?.addEventListener('input', () => { const value = filter.value.toLowerCase().trim(); page.querySelectorAll('[data-matrix-row]').forEach((row) => { row.hidden = value !== '' && !row.dataset.search.includes(value); }); }); page.querySelectorAll('[data-permission-form] input[type="checkbox"]').forEach((input) => input.addEventListener('change', () => page.querySelectorAll('[data-saved-state]').forEach((state) => { state.textContent = 'Hay cambios pendientes de guardar'; }))); })();
</script>
@endpush
