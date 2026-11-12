@extends('layouts.panel.principal')
@section('title', 'Roles y permisos')
@section('body_class', 'modulo-seguridad-rbac')

@section('content')
{{-- Bloque principal del catálogo de roles y permisos del sistema. --}}
<section class="tc-rbac-page" data-rbac-page>
    {{-- Encabezado de la pantalla y acceso a la creación de un nuevo rol. --}}
    <header class="tc-rbac-hero">
        <div><span class="tc-rbac-eyebrow">Seguridad y delegación</span>
            <h1>Roles y permisos</h1>
            <p>Crea roles, edítalos y administra sus privilegios desde la matriz.</p>
        </div><button class="tc-rbac-primary" type="button" data-open-dialog="nuevo-rol"><i class="bi bi-plus-lg"></i> Nuevo rol</button>
    </header>

    {{-- Mensaje preventivo cuando el esquema RBAC no está disponible. --}}
    @unless ($ready)
    <div class="tc-rbac-alert" role="alert">El esquema RBAC no está disponible.</div>
    @else

    {{-- Navegación entre las pantallas relacionadas con seguridad y autorización. --}}
    <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a class="is-active" href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>

    {{-- Formulario GET para filtrar roles por texto y estado. --}}
    <form class="tc-rbac-toolbar" method="get" action="{{ route('management.roles_permissions') }}"><label class="tc-rbac-search"><i class="bi bi-search"></i><input type="search" name="buscar" value="{{ $search }}" placeholder="Buscar por rol o descripción"></label><label class="tc-rbac-select"><select name="estado">
                <option value="">Todos</option>
                <option value="activo" @selected($status === 'activo')>Activos</option>
                <option value="inactivo" @selected($status === 'inactivo')>Inactivos</option>
            </select></label><button class="tc-rbac-filter-button" type="submit">Filtrar</button></form>

    {{-- Tabla de resultados con los roles entregados por el controlador. --}}
    <section class="tc-rbac-card tc-rbac-role-table">
        <div class="tc-rbac-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $role)
                    <tr>
                        <td><strong>{{ $role->nombre }}</strong><small>{{ $role->codigo }}</small></td>
                        <td>{{ $role->descripcion ?: 'Sin descripción registrada.' }}</td>
                        <td>{{ $role->usuarios_count }}</td>
                        <td>{{ $role->activo ? 'Activo' : 'Inactivo' }}</td>
                        <td class="tc-rbac-actions"><button class="tc-rbac-action" type="button" data-open-dialog="ver-rol-{{ $role->id }}">Ver</button>@if (! $role->es_sistema)<button class="tc-rbac-action tc-rbac-action--primary" type="button" data-open-dialog="editar-rol-{{ $role->id }}">Editar</button>@endif<a class="tc-rbac-action tc-rbac-action--primary" href="{{ route('management.permissions_matrix', ['rol' => $role->id]) }}">Matriz</a></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No hay roles que coincidan con los filtros.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="tc-rbac-card tc-rbac-profile-fields" aria-labelledby="tc-profile-fields-title">
        <header class="tc-rbac-section-heading"><div><span class="tc-rbac-eyebrow">Perfil personal</span><h2 id="tc-profile-fields-title">Campos adicionales por rol</h2><p>Define los campos que cada rol puede visualizar, editar y exigir en el perfil personal.</p></div></header>
        <form method="post" action="{{ route('management.roles_permissions.profile_fields.store') }}" class="tc-rbac-dialog-body tc-rbac-profile-field-create">
            @csrf
            <label>Código interno<input name="codigo" pattern="[a-z][a-z0-9_-]*" maxlength="80" required></label>
            <label>Etiqueta<input name="etiqueta" maxlength="120" required></label>
            <label>Tipo<select name="tipo" required><option value="text">Texto</option><option value="textarea">Texto largo</option><option value="email">Correo</option><option value="tel">Teléfono</option><option value="date">Fecha</option><option value="number">Número</option><option value="select">Lista</option></select></label>
            <label>Opciones de lista<textarea name="opciones" rows="2" maxlength="1000" placeholder="Una opción por línea"></textarea></label>
            <button class="tc-rbac-primary" type="submit"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear campo</button>
        </form>
        @forelse ($roles as $role)
            <details class="tc-rbac-profile-role-config">
                <summary><strong>{{ $role->nombre }}</strong><span>{{ $role->camposPerfil->where('pivot.visible', true)->count() }} campos visibles</span></summary>
                <form method="post" action="{{ route('management.roles_permissions.profile_fields.update', $role) }}">
                    @csrf @method('PUT')
                    @forelse ($profileFields as $field)
                        @php($grant = $role->camposPerfil->firstWhere('id', $field->id)?->pivot)
                        <fieldset class="tc-rbac-profile-field-row">
                            <legend>{{ $field->etiqueta }} <small>{{ $field->codigo }}</small></legend>
                            <input type="hidden" name="campos[{{ $field->id }}][id]" value="{{ $field->id }}">
                            <label><input type="checkbox" name="campos[{{ $field->id }}][visible]" value="1" @checked($grant?->visible)> Visible</label>
                            <label><input type="checkbox" name="campos[{{ $field->id }}][editable]" value="1" @checked($grant?->editable)> Editable</label>
                            <label><input type="checkbox" name="campos[{{ $field->id }}][requerido]" value="1" @checked($grant?->requerido)> Requerido</label>
                        </fieldset>
                    @empty
                        <p>No hay campos adicionales creados.</p>
                    @endforelse
                    @if ($profileFields->isNotEmpty())<button class="tc-rbac-primary" type="submit">Guardar configuración de {{ $role->nombre }}</button>@endif
                </form>
            </details>
        @empty
            <p>No hay roles configurables disponibles.</p>
        @endforelse
    </section>
    @endunless
</section>

@if ($ready)
{{-- Diálogo de creación: contiene únicamente los campos necesarios para registrar un rol. --}}
<dialog class="tc-rbac-dialog" id="nuevo-rol">
    <div class="tc-rbac-dialog-card tc-rbac-role-create-card">
        <aside class="tc-rbac-role-create-aside"><div class="tc-rbac-role-create-logo"><i class="bi bi-shield-plus" aria-hidden="true"></i></div><h2>Nuevo rol</h2><p>Crea un rol para organizar y controlar los permisos de acceso en el sistema.</p><span><i class="bi bi-shield-check" aria-hidden="true"></i> Control de accesos</span><span><i class="bi bi-gear" aria-hidden="true"></i> Permisos por módulos</span><span><i class="bi bi-layers" aria-hidden="true"></i> Gestión empresarial</span></aside>
        <div class="tc-rbac-role-create-main"><header class="tc-rbac-role-create-header"><div><span class="tc-rbac-dialog-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><h2>Crear nuevo rol</h2><p>Define el nombre y la descripción del rol.</p></div></div><button type="button" data-close-dialog aria-label="Cerrar">×</button></header>
        <form method="post" action="{{ route('management.roles_permissions.store') }}" class="tc-rbac-dialog-body">@csrf<label>Nombre del rol <b>*</b><input name="nombre" data-role-name maxlength="120" placeholder="Ej. Supervisor de ventas" required></label><label>Descripción<textarea name="descripcion" maxlength="255" placeholder="Describe brevemente las responsabilidades y funciones de este rol..."></textarea></label><label>Alcance<select name="alcance_permitido" required>
                    <option value="empresa" selected>Empresa</option>
                </select></label>
            <input type="hidden" name="codigo" data-role-code required><p class="tc-rbac-role-create-info"><i class="bi bi-info-circle" aria-hidden="true"></i> Luego de crear el rol, podrás asignar sus permisos desde la Matriz de privilegios.</p><footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar rol</button></footer>
        </form></div>
    </div>
</dialog>
@endif

{{-- Diálogos de consulta y edición asociados a cada rol listado. --}}
@foreach ($roles as $role)
{{-- Diálogo de consulta: muestra información sin permitir modificaciones. --}}
<dialog class="tc-rbac-dialog" id="ver-rol-{{ $role->id }}">
    <div class="tc-rbac-dialog-card">
        <header>
            <h2>{{ $role->nombre }}</h2><button type="button" data-close-dialog aria-label="Cerrar">×</button>
        </header>
        <div class="tc-rbac-dialog-body">
            <p><strong>Código:</strong> {{ $role->codigo }}</p>
            <p><strong>Alcance:</strong> {{ ucfirst($role->alcance_permitido) }}</p>
            <p><strong>Permisos:</strong> {{ $role->permisos_count }}</p>
            <p><strong>Usuarios:</strong> {{ $role->usuarios_count }}</p><button type="button" data-close-dialog>Cerrar</button>
        </div>
    </div>
</dialog>
@if (! $role->es_sistema)
{{-- Diálogo de edición: se muestra solo para roles que no están protegidos por el sistema. --}}
<dialog class="tc-rbac-dialog" id="editar-rol-{{ $role->id }}">
    <div class="tc-rbac-dialog-card">
        <header>
            <h2>Editar {{ $role->nombre }}</h2><button type="button" data-close-dialog aria-label="Cerrar">×</button>
        </header>
        <form method="post" action="{{ route('management.roles_permissions.update', $role) }}" class="tc-rbac-dialog-body">@csrf @method('PUT')<label>Nombre<input name="nombre" value="{{ $role->nombre }}" maxlength="120" required></label><label>Descripción<textarea name="descripcion" maxlength="255">{{ $role->descripcion }}</textarea></label><label>Alcance<select name="alcance_permitido" required>
                    <option value="empresa" @selected($role->alcance_permitido === 'empresa')>Empresa</option>
                    <option value="sede" @selected($role->alcance_permitido === 'sede')>Sede</option>
                    <option value="propio" @selected($role->alcance_permitido === 'propio')>Propio</option>
                </select></label>
            <footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar cambios</button></footer>
        </form>
    </div>
</dialog>
@endif
@endforeach
@endsection

@push('styles')
@vite('resources/css/modulos/seguridad-role-create.css')
@endpush

{{-- Carga el comportamiento interactivo desde un archivo JavaScript externo. --}}
@push('scripts')
@vite('resources/js/gestion/administracion/seguridad/roles-permisos/listar.js')
@endpush
