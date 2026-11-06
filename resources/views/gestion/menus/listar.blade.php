@extends('layouts.administracion')
@section('title', 'Menús y submenús dinámicos')
@section('body_class', 'modulo-seguridad-rbac')
@section('content')
<section class="tc-rbac-page" data-menu-page>
<header class="tc-rbac-hero"><div><span class="tc-rbac-eyebrow">Seguridad y delegación</span><h1>Menús y submenús dinámicos</h1><p>Configura la navegación lateral desde el catálogo real.</p></div><a class="tc-rbac-primary" href="{{ route('management.modules') }}">Ver pantallas</a></header>
@unless($ready)<div class="tc-rbac-alert" role="alert">Las tablas RBAC no están disponibles.</div>@else
<nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a class="is-active" href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>
<section class="tc-rbac-toolbar"><label class="tc-rbac-search"><i class="bi bi-search"></i><input type="search" data-menu-filter placeholder="Buscar opción de menú"></label><span class="tc-rbac-summary"><strong>{{ $menus->count() }}</strong> navegaciones configuradas</span></section>
@foreach($menus as $menu)
@php($children = $menu->elementos->flatMap(fn($element) => $element->elementosHijos))
<section class="tc-rbac-card tc-rbac-menu-card" data-menu-row data-search="{{ strtolower($menu->nombre.' '.$menu->codigo.' '.$children->pluck('etiqueta')->join(' ')) }}"><header class="tc-rbac-menu-header"><div><h2>{{ $menu->nombre }}</h2><small>{{ $menu->codigo }} · {{ $menu->contexto }}</small></div><button type="button" class="tc-rbac-action tc-rbac-action--primary" data-open-dialog="menu-roles-{{ $menu->id }}">Roles autorizados</button></header><div class="tc-rbac-table-wrap"><table class="tc-rbac-role-table"><thead><tr><th>Orden</th><th>Opción</th><th>Ruta</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
@foreach($menu->elementos as $element)
<tr><td>{{ $element->orden }}</td><td><strong>{{ $element->etiqueta }}</strong></td><td>Grupo</td><td>{{ $element->habilitado ? 'Activo' : 'Inactivo' }}</td><td>—</td></tr>
@foreach($element->elementosHijos as $child)
<tr><td>{{ $child->orden }}</td><td>↳ {{ $child->etiqueta }}</td><td><code>{{ $child->pantalla?->ruta ?: 'Sin ruta' }}</code></td><td>{{ $child->habilitado && $child->pantalla?->habilitada ? 'Activo' : 'Inactivo' }}</td><td><button type="button" class="tc-rbac-action" data-open-dialog="menu-element-{{ $child->id }}">Editar</button></td></tr>
@endforeach
@endforeach
</tbody></table></div></section>
<dialog class="tc-rbac-dialog" id="menu-roles-{{ $menu->id }}"><div class="tc-rbac-dialog-card"><header><h2>{{ $menu->nombre }}</h2><button type="button" data-close-dialog>×</button></header><form method="post" action="{{ route('management.menus.roles', $menu) }}" class="tc-rbac-dialog-body">@csrf @method('PUT')<div class="tc-rbac-choice-grid">@foreach($roles as $role)<label class="tc-rbac-choice"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked($menu->roles->contains('id', $role->id))><span>{{ $role->nombre }}</span></label>@endforeach</div><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar roles</button></footer></form></div></dialog>
@foreach($menu->elementos as $element)
@foreach($element->elementosHijos as $child)
<dialog class="tc-rbac-dialog" id="menu-element-{{ $child->id }}"><div class="tc-rbac-dialog-card"><header><h2>Editar {{ $child->etiqueta }}</h2><button type="button" data-close-dialog>×</button></header><form method="post" action="{{ route('management.menus.elements.update', $child) }}" class="tc-rbac-dialog-body">@csrf @method('PATCH')<label>Orden<input type="number" name="orden" value="{{ $child->orden }}" required></label><label><input type="hidden" name="habilitado" value="0"><input type="checkbox" name="habilitado" value="1" @checked($child->habilitado)> Elemento habilitado</label><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar cambios</button></footer></form></div></dialog>
@endforeach
@endforeach
@endforeach
@endunless
</section>
@endsection
@push('scripts')<script>(()=>{const p=document.querySelector('[data-menu-page]');if(!p)return;p.querySelectorAll('[data-open-dialog]').forEach(b=>b.addEventListener('click',()=>document.getElementById(b.dataset.openDialog)?.showModal()));document.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog')?.close()));p.querySelector('[data-menu-filter]')?.addEventListener('input',e=>{const v=e.target.value.toLowerCase().trim();p.querySelectorAll('[data-menu-row]').forEach(r=>r.hidden=v!==''&&!r.dataset.search.includes(v));});})();</script>@endpush
