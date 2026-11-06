@extends('layouts.administracion')

@section('title', 'Menús y submenús dinámicos')
@section('body_class', 'modulo-seguridad-rbac')

@section('content')
<section class="tc-rbac-page tc-menu-page" data-menu-page>
    <header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegación</span><h1>Menús y submenús dinámicos</h1><p>Configura la estructura visible del panel lateral según los permisos y roles.</p></div></header>
    @unless($ready)
        <div class="tc-rbac-alert" role="alert">Las tablas RBAC no están disponibles.</div>
    @else
        <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a class="is-active" href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>
        <section class="tc-menu-kpis" aria-label="Resumen de navegación"><article><i class="bi bi-list-ul" aria-hidden="true"></i><div><small>Menús activos</small><strong>{{ $activeMenus }}</strong><span>Catálogo real</span></div></article><article><i class="bi bi-layers" aria-hidden="true"></i><div><small>Submenús activos</small><strong>{{ $activeSubmenus }}</strong><span>Elementos visibles</span></div></article><article><i class="bi bi-eye-slash" aria-hidden="true"></i><div><small>Elementos ocultos</small><strong>{{ $hiddenElements }}</strong><span>Configuración actual</span></div></article><article><i class="bi bi-clock-history" aria-hidden="true"></i><div><small>Cambios recientes</small><strong>{{ $recentChanges }}</strong><span>Últimos 7 días</span></div></article></section>
        <section class="tc-menu-toolbar"><label class="tc-matrix-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar menú</span><input type="search" data-menu-filter placeholder="Buscar menú o submenú por nombre, ruta o código"></label><label>Estado<select data-menu-status><option value="">Todos</option><option value="active">Activos</option><option value="inactive">Inactivos</option></select></label></section>

        @php($selectedMenu = $menus->first())
        <div class="tc-menu-workspace">
            <section class="tc-rbac-card tc-menu-tree-card"><header class="tc-menu-card-head"><div><h2><i class="bi bi-diagram-3" aria-hidden="true"></i> Estructura de menús</h2><p>Orden y estado del catálogo existente.</p></div><span class="tc-rbac-summary">{{ $menus->count() }} menús</span></header><div class="tc-menu-tree-scroll"><table class="tc-menu-tree"><thead><tr><th>Menú / submenú</th><th>Orden</th><th>Estado</th><th>Visible</th><th></th></tr></thead><tbody>
                @forelse($menus as $menu)
                    <tr class="tc-menu-tree-row tc-menu-tree-row--root" data-menu-row data-tree-key="menu-{{ $menu->id }}" data-status="{{ $menu->habilitado ? 'active' : 'inactive' }}" data-search="{{ strtolower($menu->nombre.' '.$menu->codigo.' '.$menu->contexto) }}"><td><button type="button" class="tc-tree-toggle" data-tree-toggle="menu-{{ $menu->id }}" aria-expanded="true" aria-label="Contraer {{ $menu->nombre }}"><i class="bi bi-chevron-down" aria-hidden="true"></i></button><i class="bi bi-folder2-open" aria-hidden="true"></i><strong>{{ $menu->nombre }}</strong></td><td>{{ $loop->iteration }}</td><td><span class="tc-rbac-status tc-rbac-status--{{ $menu->habilitado ? 'active' : 'inactive' }}">{{ $menu->habilitado ? 'Activo' : 'Inactivo' }}</span></td><td><span class="tc-menu-switch {{ $menu->habilitado ? 'is-on' : '' }}" aria-label="{{ $menu->habilitado ? 'Visible' : 'Oculto' }}"></span></td><td><button type="button" class="tc-rbac-action tc-rbac-action--primary" data-open-dialog="editar-menu-{{ $menu->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar</button></td></tr>
                    @foreach($menu->elementos as $element)
                        <tr class="tc-menu-tree-row tc-menu-tree-row--group" data-menu-row data-tree-key="element-{{ $element->id }}" data-tree-parent="menu-{{ $menu->id }}" data-status="{{ $element->habilitado ? 'active' : 'inactive' }}" data-search="{{ strtolower($element->etiqueta) }}"><td><button type="button" class="tc-tree-toggle tc-tree-toggle--nested" data-tree-toggle="element-{{ $element->id }}" aria-expanded="true" aria-label="Contraer {{ $element->etiqueta }}"><i class="bi bi-chevron-down" aria-hidden="true"></i></button><span class="tc-menu-indent">↳</span><i class="bi bi-folder" aria-hidden="true"></i><strong>{{ $element->etiqueta }}</strong></td><td>{{ $element->orden }}</td><td><span class="tc-rbac-status tc-rbac-status--{{ $element->habilitado ? 'active' : 'inactive' }}">{{ $element->habilitado ? 'Activo' : 'Inactivo' }}</span></td><td><span class="tc-menu-switch {{ $element->habilitado ? 'is-on' : '' }}"></span></td><td>—</td></tr>
                        @foreach($element->elementosHijos as $child)
                            <tr class="tc-menu-tree-row tc-menu-tree-row--child" data-menu-row data-tree-parent="element-{{ $element->id }}" data-status="{{ $child->habilitado && $child->pantalla?->habilitada ? 'active' : 'inactive' }}" data-search="{{ strtolower($child->etiqueta.' '.($child->pantalla?->ruta ?: '')) }}"><td><span class="tc-menu-indent tc-menu-indent--child">↳</span><i class="bi bi-window" aria-hidden="true"></i><span>{{ $child->etiqueta }}</span><small>{{ $child->pantalla?->ruta ?: 'Sin ruta registrada' }}</small></td><td>{{ $child->orden }}</td><td><span class="tc-rbac-status tc-rbac-status--{{ $child->habilitado && $child->pantalla?->habilitada ? 'active' : 'inactive' }}">{{ $child->habilitado && $child->pantalla?->habilitada ? 'Activo' : 'Inactivo' }}</span></td><td><span class="tc-menu-switch {{ $child->habilitado && $child->pantalla?->habilitada ? 'is-on' : '' }}"></span></td><td><button type="button" class="tc-rbac-action" data-open-dialog="menu-element-{{ $child->id }}">Editar</button></td></tr>
                        @endforeach
                    @endforeach
                @empty
                    <tr><td colspan="5" class="tc-rbac-muted">No hay menús registrados.</td></tr>
                @endforelse
            </tbody></table></div></section>

            @if($selectedMenu)
                <aside class="tc-rbac-card tc-menu-preview-card"><header class="tc-menu-card-head"><div><h2><i class="bi bi-pencil-square" aria-hidden="true"></i> Configuración del menú</h2><p>Los cambios se realizan desde un modal.</p></div></header><div class="tc-menu-preview-body"><div class="tc-menu-preview-icon"><i class="bi bi-list-nested" aria-hidden="true"></i></div><h3>{{ $selectedMenu->nombre }}</h3><span class="tc-rbac-status tc-rbac-status--{{ $selectedMenu->habilitado ? 'active' : 'inactive' }}">{{ $selectedMenu->habilitado ? 'Activo' : 'Inactivo' }}</span><dl class="tc-rbac-detail-list"><div><dt>Código</dt><dd>{{ $selectedMenu->codigo }}</dd></div><div><dt>Contexto</dt><dd>{{ $selectedMenu->contexto }}</dd></div><div><dt>Grupos</dt><dd>{{ $selectedMenu->elementos->count() }}</dd></div><div><dt>Roles vinculados</dt><dd>{{ $selectedMenu->roles->count() }}</dd></div></dl><button type="button" class="tc-rbac-primary" data-open-dialog="editar-menu-{{ $selectedMenu->id }}"><i class="bi bi-pencil" aria-hidden="true"></i> Editar menú</button><button type="button" class="tc-rbac-secondary" data-open-dialog="menu-roles-{{ $selectedMenu->id }}"><i class="bi bi-people" aria-hidden="true"></i> Roles autorizados</button></div></aside>
            @endif
        </div>

        @foreach($menus as $menu)
            <dialog class="tc-rbac-dialog" id="editar-menu-{{ $menu->id }}" aria-labelledby="editar-menu-titulo-{{ $menu->id }}"><div class="tc-rbac-dialog-card"><header><div><span class="tc-rbac-dialog-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span><div><small>Configuración de navegación</small><h2 id="editar-menu-titulo-{{ $menu->id }}">Editar menú</h2></div></div><button type="button" class="tc-rbac-dialog-close" data-close-dialog aria-label="Cerrar">×</button></header><form method="post" action="{{ route('management.menus.update', $menu) }}" class="tc-rbac-dialog-body">@csrf @method('PATCH')<p class="tc-rbac-info">Solo se editan campos existentes en el catálogo real. Las rutas y permisos permanecen vinculados a pantallas registradas.</p><label>Nombre visible<input type="text" name="nombre" value="{{ $menu->nombre }}" maxlength="140" required></label><label class="tc-menu-status-field"><input type="hidden" name="habilitado" value="0"><input type="checkbox" name="habilitado" value="1" @checked($menu->habilitado)> Mostrar menú activo</label><dl class="tc-rbac-detail-list"><div><dt>Código</dt><dd>{{ $menu->codigo }}</dd></div><div><dt>Contexto</dt><dd>{{ $menu->contexto }}</dd></div></dl><footer><button type="button" class="tc-rbac-secondary" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar cambios</button></footer></form></div></dialog>
            <dialog class="tc-rbac-dialog" id="menu-roles-{{ $menu->id }}"><div class="tc-rbac-dialog-card"><header><h2>Roles autorizados</h2><button type="button" data-close-dialog>×</button></header><form method="post" action="{{ route('management.menus.roles', $menu) }}" class="tc-rbac-dialog-body">@csrf @method('PUT')<div class="tc-rbac-choice-grid">@foreach($roles as $role)<label class="tc-rbac-choice"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($menu->roles->contains('id', $role->id))><span>{{ $role->nombre }}</span></label>@endforeach</div><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar roles</button></footer></form></div></dialog>
            @foreach($menu->elementos as $element)
                @foreach($element->elementosHijos as $child)
                    <dialog class="tc-rbac-dialog" id="menu-element-{{ $child->id }}"><div class="tc-rbac-dialog-card"><header><h2>Editar {{ $child->etiqueta }}</h2><button type="button" data-close-dialog>×</button></header><form method="post" action="{{ route('management.menus.elements.update', $child) }}" class="tc-rbac-dialog-body">@csrf @method('PATCH')<label>Orden<input type="number" name="orden" value="{{ $child->orden }}" min="0" max="10000" required></label><label class="tc-menu-status-field"><input type="hidden" name="habilitado" value="0"><input type="checkbox" name="habilitado" value="1" @checked($child->habilitado)> Elemento habilitado</label><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar cambios</button></footer></form></div></dialog>
                @endforeach
            @endforeach
        @endforeach
    @endunless
</section>
@endsection

@push('scripts')
<script>
(() => {
    const page = document.querySelector('[data-menu-page]');
    if (!page) return;

    const open = (id) => {
        const dialog = document.getElementById(id);
        if (dialog && !dialog.open) dialog.showModal();
    };

    page.querySelectorAll('[data-open-dialog]').forEach((button) => {
        button.addEventListener('click', () => open(button.dataset.openDialog));
    });

    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => button.closest('dialog')?.close());
    });

    document.querySelectorAll('.tc-rbac-dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) dialog.close();
        });
    });

    const treeRows = [...page.querySelectorAll('[data-tree-key]')];
    const descendants = (key) => [...page.querySelectorAll(`[data-tree-parent="${key}"]`)];
    const setExpanded = (key, expanded) => {
        descendants(key).forEach((row) => {
            row.hidden = !expanded;
            if (!expanded && row.dataset.treeKey) setExpanded(row.dataset.treeKey, false);
        });
    };

    page.querySelectorAll('[data-tree-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const expanded = button.getAttribute('aria-expanded') !== 'true';
            button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            const icon = button.querySelector('i');
            icon?.classList.toggle('bi-chevron-down', expanded);
            icon?.classList.toggle('bi-chevron-right', !expanded);
            setExpanded(button.dataset.treeToggle, expanded);
        });
    });

    const filter = page.querySelector('[data-menu-filter]');
    const status = page.querySelector('[data-menu-status]');
    const apply = () => {
        const value = (filter?.value || '').toLowerCase().trim();
        const state = status?.value || '';
        page.querySelectorAll('[data-menu-row]').forEach((row) => {
            row.hidden = (value !== '' && !row.dataset.search.includes(value))
                || (state !== '' && row.dataset.status !== state);
        });
    };
    filter?.addEventListener('input', apply);
    status?.addEventListener('change', apply);
})();
</script>
@endpush
