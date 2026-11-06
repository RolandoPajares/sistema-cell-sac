@extends('layouts.administracion')

@section('title', 'Matriz de privilegios')
@section('body_class', 'modulo-seguridad-rbac')

@section('content')
<section class="tc-rbac-page" data-matrix-page>
    <header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegación</span><h1>Matriz de privilegios</h1><p>Asigna y revoca permisos visualmente por módulo y pantalla.</p></div><span class="tc-rbac-summary"><strong>{{ $permissionCount }}</strong> permisos activos en MariaDB</span></header>
    @unless ($ready)
        <div class="tc-rbac-alert" role="alert">El esquema RBAC no está disponible.</div>
    @else
        <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a class="is-active" href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>
        <form class="tc-rbac-matrix-toolbar" method="get" action="{{ route('management.permissions_matrix') }}"><label>Rol<select name="rol" onchange="this.form.submit()">
            @foreach ($roles as $role)
                <option value="{{ $role->id }}" @selected($selectedRole?->id === $role->id)>{{ $role->nombre }}</option>
            @endforeach
        </select></label><label class="tc-rbac-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" data-matrix-filter placeholder="Buscar módulo o pantalla"></label><span class="tc-rbac-summary">Rol seleccionado: <strong>{{ $selectedRole?->nombre ?: 'Sin rol' }}</strong></span></form>
        @if ($selectedRole)
            @php($rolePermissions = $selectedRole->permisos->keyBy('id'))
            <article class="tc-rbac-card tc-rbac-matrix-card"><header><div><h2>{{ $selectedRole->nombre }}</h2><small>{{ $selectedRole->codigo }} · alcance {{ $selectedRole->alcance_permitido }}</small></div><span data-saved-state>Sin cambios pendientes</span></header>
                <form method="post" action="{{ route('management.roles_permissions.permissions', $selectedRole) }}" data-permission-form>@csrf @method('PUT')<input type="hidden" name="alcance" value="{{ $selectedRole->alcance_permitido }}"><div class="tc-rbac-matrix-wrap"><table class="tc-rbac-matrix"><thead><tr><th>Módulo / pantalla</th><th>Ver</th><th>Crear</th><th>Editar</th><th>Eliminar</th><th>Otras acciones reales</th></tr></thead><tbody>
                    @foreach ($permissions as $modulePermissions)
                        @foreach ($modulePermissions->groupBy(fn ($permission) => $permission->pantalla_id ?: 'module-'.$permission->modulo_codigo) as $screenPermissions)
                            @php($firstPermission = $screenPermissions->first())
                            @php($otherPermissions = $screenPermissions->filter(fn ($item) => ! in_array($item->accion, ['consultar', 'crear', 'actualizar', 'eliminar'], true)))
                            <tr data-matrix-row data-search="{{ strtolower($firstPermission->pantalla_nombre.' '.$firstPermission->modulo_nombre) }}"><th><span>{{ $firstPermission->pantalla_nombre ?: $firstPermission->modulo_nombre }}</span><small>{{ $firstPermission->modulo_nombre }}</small></th>
                                @foreach (['consultar', 'crear', 'actualizar', 'eliminar'] as $action)
                                    @php($permission = $screenPermissions->firstWhere('accion', $action))
                                    <td>@if ($permission)<label class="tc-rbac-check"><input type="checkbox" name="permisos[]" value="{{ $permission->id }}" @checked($rolePermissions->has($permission->id))><span aria-hidden="true"></span><b class="visually-hidden">{{ $permission->nombre }}</b></label>@else<span class="tc-rbac-na">—</span>@endif</td>
                                @endforeach
                                <td>
                                    @foreach ($otherPermissions as $permission)
                                        <label class="tc-rbac-check tc-rbac-check--other"><input type="checkbox" name="permisos[]" value="{{ $permission->id }}" @checked($rolePermissions->has($permission->id))><span aria-hidden="true"></span><b>{{ ucfirst($permission->accion) }}</b></label>
                                    @endforeach
                                    @if ($otherPermissions->isEmpty())<span class="tc-rbac-na">—</span>@endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody></table></div><footer class="tc-rbac-form-footer"><span data-saved-state>Sin cambios pendientes</span><button class="tc-rbac-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></footer></form>
            </article>
        @else
            <div class="tc-rbac-alert">No hay roles activos disponibles.</div>
        @endif
    @endunless
</section>
@endsection

@push('scripts')
<script>
    (() => { const page = document.querySelector('[data-matrix-page]'); if (!page) return; const filter = page.querySelector('[data-matrix-filter]'); filter?.addEventListener('input', () => { const value = filter.value.toLowerCase().trim(); page.querySelectorAll('[data-matrix-row]').forEach((row) => { row.hidden = value !== '' && !row.dataset.search.includes(value); }); }); page.querySelectorAll('[data-permission-form] input[type=checkbox]').forEach((input) => input.addEventListener('change', () => input.form.querySelectorAll('[data-saved-state]').forEach((state) => { state.textContent = 'Cambios pendientes de guardar'; }))); })();
</script>
@endpush
