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
    @endunless
</section>

@if ($ready)
{{-- Diálogo de creación: contiene únicamente los campos necesarios para registrar un rol. --}}
<dialog class="tc-rbac-dialog" id="nuevo-rol">
    <div class="tc-rbac-dialog-card">
        <header>
            <h2>Nuevo rol</h2><button type="button" data-close-dialog aria-label="Cerrar">×</button>
        </header>
        <form method="post" action="{{ route('management.roles_permissions.store') }}" class="tc-rbac-dialog-body">@csrf<label>Código interno<input name="codigo" pattern="[a-z][a-z0-9_]*" maxlength="40" required></label><label>Nombre<input name="nombre" maxlength="120" required></label><label>Descripción<textarea name="descripcion" maxlength="255"></textarea></label><label>Alcance<select name="alcance_permitido" required>
                    <option value="sede">Sede</option>
                    <option value="propio">Propio</option>
                    <option value="empresa">Empresa</option>
                </select></label>
            <footer><button type="button" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit">Guardar rol</button></footer>
        </form>
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

{{-- Carga el comportamiento interactivo desde un archivo JavaScript externo. --}}
@push('scripts')
@vite('resources/js/gestion/roles-permisos/listar.js')
@endpush
