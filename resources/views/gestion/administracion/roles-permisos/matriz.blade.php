@extends('layouts.panel.principal')

@section('title', 'Matriz de privilegios')
@section('body_class', 'modulo-seguridad-rbac')

@section('content')
@php
    $allPermissions = $permissions->flatten();
    $actions = $allPermissions->pluck('accion')->filter()->unique()->sort()->values();
    $selectedPermissionIds = $selectedRole?->permisos->pluck('id')->map(fn ($id): int => (int) $id)->all() ?? [];
@endphp
<section class="tc-rbac-page tc-privilege-matrix" data-matrix-page
    data-current-permissions="@json($selectedPermissionIds)"
    data-template-permissions="@json($recommendedPermissionIds)"
    data-preview-items="@json($previewItems->values())"
    data-role-code="{{ $selectedRole?->codigo }}">
    <header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegación</span><h1>Matriz de privilegios</h1><p>Visualiza y administra permisos reales por rol, módulo, pantalla y acción.</p></div></header>
    @unless ($ready)
        <div class="tc-rbac-alert" role="alert">El esquema RBAC no está disponible.</div>
    @else
        <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a class="is-active" href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>
        <section class="tc-matrix-kpis" aria-label="Resumen de privilegios"><article><span class="tc-matrix-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Roles configurados</small><strong>{{ $roles->count() }}</strong><em>Datos reales</em></div></article><article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--purple"><i class="bi bi-box" aria-hidden="true"></i></span><div><small>Módulos activos</small><strong>{{ $moduleCount }}</strong><em>Catálogo RBAC</em></div></article><article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--cyan"><i class="bi bi-display" aria-hidden="true"></i></span><div><small>Pantallas activas</small><strong>{{ $screenCount }}</strong><em>Rutas registradas</em></div></article><article><span class="tc-matrix-kpi-icon tc-matrix-kpi-icon--green"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><small>Asignaciones activas</small><strong data-assigned-count>{{ $selectedRole?->permisos->count() ?? 0 }}</strong><em>{{ $assignedPermissionCount }} en todos los roles</em></div></article></section>
        <form class="tc-matrix-toolbar" method="get" action="{{ route('management.permissions_matrix') }}" data-role-selector-form>
            <label class="tc-matrix-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar permiso</span><input type="search" name="buscar" value="{{ request('buscar') }}" data-matrix-filter placeholder="Buscar por permiso, pantalla o módulo…"></label>
            <label>Filtrar módulo<select data-module-filter><option value="">Todos los módulos</option>@foreach ($permissions as $modulePermissions)<option value="{{ $modulePermissions->first()->modulo_codigo }}">{{ $modulePermissions->first()->modulo_nombre }}</option>@endforeach</select></label>
            <label>Rol<select name="rol" data-role-selector>@foreach ($roles as $role)<option value="{{ $role->id }}" @selected($selectedRole?->id === $role->id)>{{ $role->nombre }}</option>@endforeach</select></label>
            <button class="tc-rbac-secondary" type="button" data-apply-template><i class="bi bi-magic" aria-hidden="true"></i> Aplicar plantilla recomendada</button><button class="tc-rbac-secondary" type="button" data-preview-menu><i class="bi bi-eye" aria-hidden="true"></i> Vista previa del menú</button>
        </form>
        @if ($selectedRole)
            <form method="post" action="{{ route('management.roles_permissions.permissions', $selectedRole) }}" data-permission-form>@csrf @method('PUT')<input type="hidden" name="alcance" value="{{ $selectedRole->alcance_permitido }}">
                <div class="tc-matrix-layout tc-matrix-layout--full"><div class="tc-matrix-card"><div class="tc-matrix-scroll"><table class="tc-matrix-table"><thead><tr><th class="tc-matrix-resource-col">MÓDULO / PANTALLA</th>@foreach ($actions as $action)<th>{{ ucfirst($action) }}</th>@endforeach</tr></thead><tbody>
                    @foreach ($permissions as $modulePermissions)
                        @php($module = $modulePermissions->first())
                        <tr class="tc-matrix-module-row" data-matrix-row data-module-code="{{ $module->modulo_codigo }}" data-search="{{ strtolower($module->modulo_nombre.' '.$module->modulo_codigo) }}"><th><button type="button" class="tc-tree-toggle" data-select-module="{{ $module->modulo_codigo }}" aria-label="Seleccionar permisos de {{ $module->modulo_nombre }}"><i class="bi bi-check2-square" aria-hidden="true"></i></button><i class="bi bi-folder2-open" aria-hidden="true"></i><strong>{{ $module->modulo_nombre }}</strong><span class="tc-rbac-status tc-rbac-status--active">{{ $modulePermissions->count() }} operaciones reales</span></th>@foreach ($actions as $action) @php($permission = $modulePermissions->firstWhere('accion', $action))<td>@if ($permission)<label class="tc-matrix-toggle" title="{{ $permission->nombre }}" aria-label="{{ $permission->nombre }}"><input type="checkbox" name="permisos[]" value="{{ $permission->id }}" data-permission-id="{{ $permission->id }}" data-module="{{ $module->modulo_codigo }}" @checked(in_array((int) $permission->id, $selectedPermissionIds, true)) @disabled($selectedRole->codigo === 'administrador' || $module->modulo_codigo === 'seguridad')><span></span><b class="visually-hidden">{{ $permission->nombre }}</b></label>@else<span aria-label="Operación no registrada">—</span>@endif</td>@endforeach</tr>
                        @foreach ($modulePermissions->filter(fn ($permission): bool => $permission->pantalla_id !== null)->groupBy('pantalla_id') as $screenPermissions)
                            @php($firstPermission = $screenPermissions->first())
                            <tr data-matrix-row data-module-code="{{ $module->modulo_codigo }}" data-search="{{ strtolower(($firstPermission->pantalla_nombre ?: '').' '.$firstPermission->modulo_nombre.' '.$screenPermissions->pluck('nombre')->join(' ')) }}"><th class="tc-matrix-screen"><i class="bi bi-{{ $firstPermission->pantalla_id ? 'window' : 'folder' }}" aria-hidden="true"></i><span>{{ $firstPermission->pantalla_nombre ?: $firstPermission->modulo_nombre }}</span><small>{{ $screenPermissions->count() }} acciones disponibles</small></th>@foreach ($actions as $action) @php($permission = $screenPermissions->firstWhere('accion', $action))<td>@if ($permission)<label class="tc-matrix-toggle" title="{{ $permission->nombre }}" aria-label="{{ $permission->nombre }}"><input type="checkbox" name="permisos[]" value="{{ $permission->id }}" data-permission-id="{{ $permission->id }}" data-module="{{ $module->modulo_codigo }}" @checked(in_array((int) $permission->id, $selectedPermissionIds, true)) @disabled($selectedRole->codigo === 'administrador' || $module->modulo_codigo === 'seguridad')><span></span><b class="visually-hidden">{{ $permission->nombre }}</b></label>@else<span aria-label="Operación no registrada">—</span>@endif</td>@endforeach</tr>
                        @endforeach
                    @endforeach
                </tbody></table></div><footer class="tc-matrix-footer"><span data-saved-state>Rol editable: <strong>{{ $selectedRole->nombre }}</strong>. Los cambios permanecen pendientes hasta guardar. Seguridad y Delegación se conserva protegida.</span><div><button class="tc-rbac-secondary" type="button" data-discard-changes>Descartar cambios</button><button class="tc-rbac-primary" type="submit" data-save-changes @disabled($selectedRole->codigo === 'administrador')><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></div></footer></div><aside class="tc-matrix-legend"><h2><i class="bi bi-info-circle" aria-hidden="true"></i> Leyenda</h2><div><span class="tc-legend-switch is-on"></span><p><strong>Permiso habilitado</strong><small>El rol puede realizar la acción.</small></p></div><div><span class="tc-legend-switch"></span><p><strong>Permiso deshabilitado</strong><small>El rol no puede realizar la acción.</small></p></div><hr><h3>Acciones registradas</h3>@foreach ($actions as $action)<span class="tc-legend-action"><b>{{ strtoupper(mb_substr($action, 0, 1)) }}</b>{{ ucfirst($action) }}</span>@endforeach</aside></div>
            </form>
        @else
            <div class="tc-rbac-alert">No hay roles activos disponibles.</div>
        @endif
    @endunless
    <dialog class="tc-rbac-dialog" data-preview-dialog aria-labelledby="preview-menu-title"><div class="tc-rbac-dialog-card"><header><div><span class="tc-rbac-dialog-icon"><i class="bi bi-list-nested" aria-hidden="true"></i></span><div><small>Previsualización dinámica</small><h2 id="preview-menu-title">Menú del rol seleccionado</h2></div></div><button type="button" class="tc-rbac-dialog-close" data-close-preview aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="tc-rbac-dialog-body"><p class="tc-rbac-info">La vista se calcula con las casillas actualmente seleccionadas y el catálogo real de menús y pantallas.</p><div data-preview-content></div><footer><button type="button" class="tc-rbac-secondary" data-close-preview>Cerrar</button></footer></div></div></dialog>
</section>
@endsection

@push('scripts')
<script>
(() => {
    /**
     * Gestiona la matriz de permisos sin persistir cambios hasta confirmar el formulario.
     * @@returns {void} Configura los eventos y el estado visual de la matriz.
     */
    const page = document.querySelector('[data-matrix-page]');
    if (!page) return;
    /** Mantiene protegidas las asignaciones del módulo de Seguridad y Delegación. */
    page.querySelectorAll('input[data-module="seguridad"]').forEach((input) => { input.disabled = true; });
    page.querySelector('[data-select-module="seguridad"]')?.setAttribute('disabled', 'disabled');
    const form = page.querySelector('[data-permission-form]');
    const checkboxes = () => [...page.querySelectorAll('input[data-permission-id]:not(:disabled)')];
    const ids = () => checkboxes().filter((input) => input.checked).map((input) => Number(input.dataset.permissionId));
    const original = new Set(JSON.parse(page.dataset.currentPermissions || '[]').map(Number));
    const template = new Set(JSON.parse(page.dataset.templatePermissions || '[]').map(Number));
    const catalog = JSON.parse(page.dataset.previewItems || '[]');
    const roleSelector = page.querySelector('[data-role-selector]');

    /** @@returns {void} Actualiza el contador y el estado pendiente de la matriz. */
    const refreshState = () => {
        const selected = new Set(ids());
        const changed = selected.size !== original.size || [...selected].some((id) => !original.has(id));
        page.querySelector('[data-assigned-count]').textContent = String(selected.size);
        const state = page.querySelector('[data-saved-state]');
        if (state) state.textContent = changed ? 'Hay cambios pendientes de guardar.' : `Rol editable: ${roleSelector?.selectedOptions[0]?.text || ''}. Sin cambios pendientes.`;
    };

    /** @@returns {void} Aplica la búsqueda y el filtro de módulo sobre filas del catálogo. */
    const filterRows = () => {
        const text = (page.querySelector('[data-matrix-filter]')?.value || '').toLowerCase().trim();
        const module = page.querySelector('[data-module-filter]')?.value || '';
        page.querySelectorAll('[data-matrix-row]').forEach((row) => { row.hidden = (text !== '' && !row.dataset.search.includes(text)) || (module !== '' && row.dataset.moduleCode !== module); });
    };

    /**
     * Compara la selección actual con una propuesta de permisos.
     * @@param {Set<number>} proposal Permisos propuestos.
     * @@returns Objeto con permisos a agregar, retirar y conservar.
     */
    const diff = (proposal) => { const current = new Set(ids()); return { agregar: [...proposal].filter((id) => !current.has(id)), retirar: [...current].filter((id) => !proposal.has(id)), conservar: [...current].filter((id) => proposal.has(id)) }; };

    /**
     * Obtiene nombres reales para mostrar una diferencia de permisos.
     * @@param {number[]} permissionIds Identificadores de permisos.
     * @@returns {string} Etiquetas de los permisos.
     */
    const labels = (permissionIds) => permissionIds.map((id) => page.querySelector(`input[value="${id}"]`)?.closest('label')?.title || `Permiso #${id}`).join(', ') || 'ninguno';

    page.querySelector('[data-matrix-filter]')?.addEventListener('input', filterRows);
    page.querySelector('[data-module-filter]')?.addEventListener('change', filterRows);
    checkboxes().forEach((input) => input.addEventListener('change', refreshState));
    page.querySelectorAll('[data-select-module]').forEach((button) => button.addEventListener('click', () => { const inputs = checkboxes().filter((input) => input.dataset.module === button.dataset.selectModule); const shouldCheck = inputs.some((input) => !input.checked); inputs.forEach((input) => { input.checked = shouldCheck; }); refreshState(); }));
    page.querySelector('[data-apply-template]')?.addEventListener('click', () => { const changes = diff(template); if (!window.confirm(`La plantilla propone agregar: ${labels(changes.agregar)}; retirar: ${labels(changes.retirar)}; conservar: ${labels(changes.conservar)}. ¿Deseas cargarla como cambio pendiente?`)) return; checkboxes().forEach((input) => { input.checked = template.has(Number(input.dataset.permissionId)); }); refreshState(); });
    page.querySelector('[data-discard-changes]')?.addEventListener('click', () => { checkboxes().forEach((input) => { input.checked = original.has(Number(input.dataset.permissionId)); }); refreshState(); });
    form?.addEventListener('submit', (event) => { const changes = diff(new Set(ids())); if ((changes.agregar.length || changes.retirar.length) && !window.confirm(`Se agregarán ${changes.agregar.length} permisos y se retirarán ${changes.retirar.length}. ¿Confirmas guardar la transacción?`)) event.preventDefault(); });

    /** @@returns {void} Renderiza el menú real filtrado por la selección actual. */
    const renderPreview = () => { const allowed = new Set(ids()); const visible = catalog.filter((item) => item.tipo !== 'grupo' && item.permiso_id && allowed.has(Number(item.permiso_id))); const content = page.querySelector('[data-preview-content]'); if (!content) return; content.innerHTML = visible.length ? `<ul>${visible.map((item) => `<li><strong>${item.etiqueta}</strong>${item.ruta ? ` <small>· ${item.ruta}</small>` : ''}</li>`).join('')}</ul>` : '<p class="tc-rbac-muted">No hay pantallas autorizadas con la selección actual.</p>'; };
    page.querySelector('[data-preview-menu]')?.addEventListener('click', () => { renderPreview(); page.querySelector('[data-preview-dialog]')?.showModal(); });
    page.querySelectorAll('[data-close-preview]').forEach((button) => button.addEventListener('click', () => page.querySelector('[data-preview-dialog]')?.close()));
    roleSelector?.addEventListener('change', (event) => { const selected = new Set(ids()); if (selected.size !== original.size || [...selected].some((id) => !original.has(id))) { if (!window.confirm('Hay cambios pendientes. ¿Deseas descartarlos y cambiar de rol?')) { event.preventDefault(); roleSelector.value = '{{ $selectedRole?->id }}'; return; } } roleSelector.form.submit(); });
    refreshState();
})();
</script>
@endpush
