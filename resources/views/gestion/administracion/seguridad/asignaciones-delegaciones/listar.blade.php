@extends('layouts.panel.principal')

@section('title', 'Asignaciones globales y por sede')
@section('body_class', 'modulo-seguridad-rbac')

@section('content')
{{-- Consulta y asignación de roles globales o por sede. --}}
{{-- Pantalla de consulta y asignación de roles por usuario. --}}
<section class="tc-rbac-page" data-assignment-page>
    <header class="tc-rbac-hero">
        <div><span class="tc-rbac-eyebrow">Seguridad y delegación</span>
            <h1>Asignaciones globales y por sede</h1>
            <p>Asigna, revoca y consulta privilegios efectivos por cuenta desde una sola operación auditada.</p>
        </div><a class="tc-rbac-primary" href="{{ route('management.roles_permissions') }}"><i class="bi bi-shield-lock" aria-hidden="true"></i> Roles y permisos</a>
    </header>
    @unless($ready)<div class="tc-rbac-alert" role="alert">El esquema RBAC no está disponible. No se muestran formularios simulados.</div>@else
    <nav class="tc-rbac-tabs" aria-label="Administración de seguridad"><a href="{{ route('management.users') }}">Gestión de usuarios</a><a href="{{ route('management.roles_permissions') }}">Roles y permisos</a><a href="{{ route('management.permissions_matrix') }}">Matriz de privilegios</a><a class="is-active" href="{{ route('management.user_assignments') }}">Asignaciones globales y por sede</a><a href="{{ route('management.menus') }}">Menús y submenús dinámicos</a><a href="{{ route('management.modules') }}">Módulos y pantallas</a></nav>
    <section class="tc-rbac-toolbar"><label class="tc-rbac-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar usuarios</span><input type="search" data-assignment-filter placeholder="Buscar usuario o correo"></label><span class="tc-rbac-summary"><strong>{{ $users->total() }}</strong> usuarios consultados</span></section>
    <section class="tc-rbac-card tc-rbac-role-table">
        <div class="tc-rbac-table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol / alcance</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    {{-- Las asignaciones ya fueron preparadas por el controlador. --}}
                    @php($activeGlobal = $assignmentPresentation[$user->id]['activeGlobal']) @php($activeSite = $assignmentPresentation[$user->id]['activeSite'])
                    <tr data-assignment-row data-search="{{ strtolower($user->nombre.' '.$user->correo) }}">
                        <td><strong>{{ $user->nombre }}</strong><small>{{ $user->correo }}</small></td>
                        <td>
                            <div class="tc-rbac-assignment-chips">@forelse($activeGlobal as $role)<span class="tc-rbac-pill tc-rbac-pill--scope">{{ $role->nombre }} · Global</span>@empty @endforelse @forelse($activeSite as $role)<span class="tc-rbac-pill tc-rbac-pill--scope">{{ $role->nombre }} · Por sede</span>@empty @endforelse @if($activeGlobal->isEmpty() && $activeSite->isEmpty())<span class="tc-rbac-muted">Sin roles vigentes</span>@endif</div>
                        </td>
                        <td>{{ $activeSite->isNotEmpty() ? 'Sede autorizada' : 'Todas las sedes' }}</td>
                        <td><span class="tc-rbac-status tc-rbac-status--{{ $user->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $user->activo ? 'Activo' : 'Inactivo' }}</span></td>
                        <td><button class="tc-rbac-action tc-rbac-action--primary" type="button" data-open-dialog="asignar-roles-{{ $user->id }}"><i class="bi bi-person-gear" aria-hidden="true"></i> Asignar roles</button></td>
                    </tr>
                    @empty<tr>
                        <td colspan="5">No hay usuarios registrados.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </section>
    <div class="mt-3">{{ $users->links() }}</div>
    @endunless
</section>

@foreach($users as $user)
{{-- IDs de roles vigentes usados para marcar las casillas del formulario. --}}
@php($currentGlobal = $assignmentPresentation[$user->id]['currentGlobal']) @php($currentSite = $assignmentPresentation[$user->id]['currentSite'])
<dialog class="tc-rbac-dialog" id="asignar-roles-{{ $user->id }}" aria-labelledby="asignar-roles-title-{{ $user->id }}">
    <div class="tc-rbac-dialog-card">
        <header>
            <div><span class="tc-rbac-dialog-icon"><i class="bi bi-person-check" aria-hidden="true"></i></span>
                <div><small>Asignación de privilegios</small>
                    <h2 id="asignar-roles-title-{{ $user->id }}">{{ $user->nombre }}</h2>
                </div>
            </div><button type="button" class="tc-rbac-dialog-close" data-close-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <form method="post" action="{{ route('management.user_assignments.sync', $user) }}" class="tc-rbac-dialog-body">@csrf @method('PUT')<p class="tc-rbac-info">Las casillas marcadas se conservarán. Las desmarcadas se revocarán y quedarán registradas en auditoría.</p>
            <div class="tc-rbac-choice-grid">@foreach($roles as $role)<label class="tc-rbac-choice"><input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, $role->alcance_permitido === 'sede' ? $currentSite : $currentGlobal, true))><span><strong>{{ $role->nombre }}</strong><small>{{ ucfirst($role->alcance_permitido) }} · {{ $role->codigo }}</small></span></label>@endforeach</div>
            <footer><button type="button" class="tc-rbac-secondary" data-close-dialog>Cancelar</button><button class="tc-rbac-primary" type="submit"><i class="bi bi-save" aria-hidden="true"></i> Guardar asignación</button></footer>
        </form>
    </div>
</dialog>
@endforeach
@endsection
@push('scripts')
@vite('resources/js/gestion/administracion/seguridad/roles-permisos/asignaciones.js')
@endpush
