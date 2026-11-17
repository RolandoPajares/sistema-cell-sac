@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Matriz de privilegios')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-seguridad-rbac')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="tc-rbac-page tc-privilege-matrix" data-matrix-page data-current-permissions="{{ json_encode($selectedPermissionIds) }}" data-preview-items="{{ json_encode($previewItems->values()) }}" data-role-id="{{ $selectedRole?->id }}">
    <header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegaci&oacute;n</span><h1>Matriz de privilegios</h1><p>Administraci&oacute;n din&aacute;mica de roles, m&oacute;dulos y permisos.</p></div><span class="tc-matrix-security-icon" aria-hidden="true"><i class="bi bi-shield-check"></i></span></header>
    <div class="tc-matrix-toast-host" data-matrix-toast-host aria-live="polite" aria-relevant="additions"></div>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @unless ($ready)
        <div class="tc-rbac-alert" role="alert">El esquema RBAC no est&aacute; disponible.</div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
    @else
        <section class="tc-matrix-kpis" aria-label="Resumen de privilegios"><article><span class="tc-matrix-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Roles configurados</small><strong>{{ $roles->count() }}</strong><em>Roles activos</em></div></article><article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--purple"><i class="bi bi-layers" aria-hidden="true"></i></span><div><small>M&oacute;dulos</small><strong>{{ $moduleCount }}</strong><em>Cat&aacute;logo RBAC</em></div></article><article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--cyan"><i class="bi bi-display" aria-hidden="true"></i></span><div><small>Pantallas</small><strong>{{ $screenCount }}</strong><em>Cat&aacute;logo RBAC</em></div></article><article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--green"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><small>Asignaciones activas</small><strong data-assigned-count>{{ $assignedPermissionCount }}</strong><em>En roles activos</em></div></article></section>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form class="tc-matrix-toolbar" method="get" action="{{ route('management.permissions_matrix') }}" data-role-selector-form>
            <label class="tc-matrix-field"><span>Rol</span><select name="rol" data-role-selector><option value="">Seleccionar rol</option>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($selectedRole?->id === $role->id)>{{ $role->nombre }}</option>@endforeach</select></label>
            <label class="tc-matrix-field"><span>Alcance</span><select disabled aria-label="Alcance del rol"><option>{{ $selectedRole ? ucfirst($selectedRole->alcance_permitido) : '' }}</option></select></label>
            <label class="tc-matrix-field tc-matrix-search"><span>Buscar</span><span class="tc-matrix-input"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="{{ request('buscar') }}" data-matrix-filter placeholder="Buscar m&oacute;dulo o pantalla..."></span></label>
        </form>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if ($selectedRole)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
            <form method="post" action="{{ route('management.roles_permissions.permissions', $selectedRole) }}" data-permission-form data-ajax-submit="1">@csrf @method('PUT')<input type="hidden" name="alcance" value="{{ $selectedRole->alcance_permitido }}">
                <div class="tc-matrix-permissions-card"><header class="tc-matrix-card-header"><div><h2 class="tc-matrix-card-kicker">Secciones del sistema ({{ $matrixGroups->count() }})</h2></div><div class="tc-matrix-card-actions"><label class="tc-matrix-module-filter"><span class="visually-hidden">Filtrar por secci&oacute;n</span><select data-module-selector aria-label="Filtrar por secci&oacute;n"><option value="">Todas las secciones</option>@foreach ($matrixGroups as $group)<option value="{{ $group['codigo'] }}">{{ $group['nombre'] }}</option>@endforeach</select></label><button class="tc-rbac-secondary tc-matrix-preview-button" type="button" data-preview-menu><i class="bi bi-eye" aria-hidden="true"></i> Vista previa del menú</button><button class="tc-rbac-secondary" type="button" data-expand-all><i class="bi bi-arrows-expand" aria-hidden="true"></i> Expandir todo</button><button class="tc-rbac-secondary" type="button" data-collapse-all><i class="bi bi-arrows-collapse" aria-hidden="true"></i> Contraer todo</button></div></header>
                    <div class="tc-matrix-permission-store" hidden aria-hidden="true">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                        @foreach ($matrixModules as $module)
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @foreach ($module['permissions'] as $permission)
                                <input type="checkbox" name="permisos[]" value="{{ $permission['id'] }}" data-permission-id="{{ $permission['id'] }}" data-permission-module="{{ $module['codigo'] }}" data-permission-screen="" data-permission-action="{{ $permission['accion'] }}" data-permission-label="{{ $permission['nombre'] }}" @checked(in_array($permission['id'], $selectedPermissionIds, true))>
                            @endforeach
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @foreach ($module['screens'] as $screen)
                                <span data-permission-screen-record data-permission-module="{{ $module['codigo'] }}" data-permission-screen="{{ $screen['id'] }}" data-permission-screen-name="{{ $screen['nombre'] }}"></span>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                                @foreach ($screen['permissions'] as $permission)
                                    <input type="checkbox" name="permisos[]" value="{{ $permission['id'] }}" data-permission-id="{{ $permission['id'] }}" data-permission-module="{{ $module['codigo'] }}" data-permission-screen="{{ $screen['id'] }}" data-permission-screen-name="{{ $screen['nombre'] }}" data-permission-action="{{ $permission['accion'] }}" data-permission-label="{{ $permission['nombre'] }}" @checked(in_array($permission['id'], $selectedPermissionIds, true))>
                                @endforeach
                            @endforeach
                        @endforeach
                    </div>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @forelse ($matrixGroups as $group)
                        <article class="tc-matrix-module-panel" data-module-panel data-module-code="{{ $group['codigo'] }}" data-search="{{ strtolower($group['nombre'].' '.$group['codigo']) }}"><header class="tc-matrix-module-header" data-module-header><button type="button" class="tc-matrix-collapse" data-toggle-module aria-expanded="false" aria-label="Mostrar {{ $group['nombre'] }}"><i class="bi bi-chevron-down" aria-hidden="true"></i></button><div class="tc-matrix-module-heading"><span class="tc-matrix-module-icon"><i class="bi bi-grid-3x3-gap" aria-hidden="true"></i></span><div><h3>{{ $group['nombre'] }}</h3></div></div><i class="tc-matrix-module-chevron bi bi-chevron-down" aria-hidden="true"></i></header><div class="tc-matrix-module-body" hidden>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @forelse ($group['items'] as $item)
                                <section class="tc-matrix-screen-group" data-matrix-row data-module-code="{{ $group['codigo'] }}" data-module-id="{{ $item['module_id'] }}" data-permission-module="{{ $item['module_code'] }}" data-screen-id="{{ $item['screen_id'] }}" data-screen-name="{{ $item['nombre'] }}" data-module-name="{{ $item['module_name'] }}" data-search="{{ strtolower($group['nombre'].' '.$item['nombre'].' '.$item['screen_name'].' '.$item['module_name']) }}"><header><span class="tc-matrix-screen-label"><i class="bi bi-window" aria-hidden="true"></i><span><strong>{{ $item['nombre'] }}</strong><small>{{ $item['permission_count'] }} acciones registradas · {{ $item['module_name'] }}</small></span></span><button type="button" class="tc-matrix-screen-configure" data-open-permission-modal><i class="bi bi-sliders" aria-hidden="true"></i> Configurar</button></header></section>
                            @empty
                                <p class="tc-matrix-empty-line">No hay pantallas activas registradas en esta secci&oacute;n.</p>
                            @endforelse
                        </div></article>
                    @empty
                        <div class="tc-rbac-empty">No hay secciones configuradas en el men&uacute; del sistema.</div>
                    @endforelse
                    <footer class="tc-matrix-footer"><span data-saved-state>Rol editable: <strong>{{ $selectedRole->nombre }}</strong>. Los cambios permanecen pendientes hasta guardar.</span><div><button class="tc-rbac-secondary" type="button" data-discard-changes>Descartar cambios</button><button class="tc-rbac-primary" type="submit" data-save-changes>Guardar cambios</button></div></footer>
                </div>
            </form>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            <div class="tc-rbac-alert">No hay roles registrados.</div>
        @endif
    @endunless
    <dialog class="tc-rbac-dialog" data-preview-dialog aria-labelledby="preview-menu-title"><div class="tc-rbac-dialog-card"><header><h2 id="preview-menu-title">Men&uacute; del rol seleccionado</h2><button type="button" data-close-preview aria-label="Cerrar">&times;</button></header><div class="tc-rbac-dialog-body"><p>La vista previa usa men&uacute;s, pantallas y permisos persistidos.</p><div data-preview-content></div></div></div></dialog>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
    @if ($selectedRole)
        <dialog class="tc-rbac-dialog tc-rbac-permission-dialog" data-permission-dialog aria-labelledby="permission-dialog-title"><div class="tc-permission-dialog-form"><header class="tc-permission-dialog-header"><div><span class="tc-permission-dialog-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><h2 id="permission-dialog-title">Asignar privilegios</h2><p>Configura los permisos del módulo seleccionado.</p></div></div><button type="button" data-close-permission-dialog aria-label="Cerrar">&times;</button></header><div class="tc-matrix-toast-host tc-matrix-toast-host--dialog" data-matrix-toast-dialog aria-live="polite" aria-relevant="additions"></div><div class="tc-rbac-dialog-body"><div class="tc-permission-dialog-context"><div><small>M&oacute;dulo seleccionado</small><strong data-permission-module-name></strong></div><div><small>Rol</small><strong>{{ $selectedRole->nombre }}</strong></div><div><small>Alcance</small><strong>{{ ucfirst($selectedRole->alcance_permitido) }}</strong></div></div><nav class="tc-permission-dialog-tabs" role="tablist" aria-label="Secciones de permisos"><button type="button" role="tab" aria-selected="true" data-permission-tab="permissions"><i class="bi bi-display" aria-hidden="true"></i> Pantallas del módulo</button><button type="button" role="tab" aria-selected="false" data-permission-tab="summary"><i class="bi bi-list-check" aria-hidden="true"></i> Resumen de permisos</button></nav><section class="tc-permission-dialog-pane" role="tabpanel" data-permission-pane="permissions"><div class="tc-permission-dialog-screen"><i class="bi bi-window" aria-hidden="true"></i><strong data-permission-screen-name></strong></div><div class="tc-permission-dialog-list" data-permission-list></div></section><section class="tc-permission-dialog-pane" role="tabpanel" data-permission-pane="summary" hidden><div data-permission-summary></div></section></div><footer class="tc-permission-dialog-footer"><button type="button" class="tc-rbac-secondary" data-close-permission-dialog>Cancelar</button><button type="button" class="tc-rbac-primary" data-save-screen-permissions><i class="bi bi-floppy" aria-hidden="true"></i> Guardar cambios</button></footer></div></dialog>
    @endif
</section>
@endsection

{{-- bloque de estilos o scripts para el lugar reservado por la plantilla. --}}
@push('scripts')
@vite('resources/js/gestion/administracion/seguridad/roles-permisos/matriz.js')
@endpush
