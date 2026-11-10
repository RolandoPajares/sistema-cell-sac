@extends('layouts.panel.principal')

@section('title', 'Usuarios y roles')
@section('body_class', 'modulo-usuarios')

@section('content')
{{-- Panel principal con filtros, indicadores y listado de usuarios. --}}
<section class="usuarios-page" data-usuarios-page data-open-create-dialog="{{ $errors->any() ? 'true' : 'false' }}">
    <header class="usuarios-hero">
        <div>
            <span class="usuarios-eyebrow">Gesti�n de usuarios</span>
            <h1>Usuarios y roles <i class="bi bi-people-fill" aria-hidden="true"></i></h1>
            <p>Administra las cuentas de acceso al sistema y sus permisos.</p>
        </div>
        <div class="usuarios-hero-actions"><button class="usuarios-primary-button" type="button" data-open-user-create><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo usuario</button>
            <details class="usuarios-export usuarios-export--hero">
                <summary><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary>
                <div class="usuarios-export-menu"><a href="{{ $exportUrls['pdf'] }}">PDF (.pdf)</a><a href="{{ $exportUrls['csv'] }}">CSV (.csv)</a><a href="{{ $exportUrls['excel'] }}">Excel (.xlsx)</a></div>
            </details>
        </div>
    </header>

    <section class="usuarios-kpi-grid" aria-label="Resumen de usuarios">
        <article class="usuarios-kpi usuarios-kpi--blue"><span class="usuarios-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <div><small>Total de usuarios</small><strong>{{ number_format($totalUsers) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> +{{ number_format($currentMonthUsers) }} este mes</span></div>
        </article>
        <article class="usuarios-kpi usuarios-kpi--green"><span class="usuarios-kpi-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span>
            <div><small>Usuarios activos</small><strong>{{ $accountStatusAvailable ? number_format($activeUsers) : '�' }}</strong><span>{{ $accountStatusAvailable && $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 0).'%' : 'Control de estado pendiente de migraci�n' }}</span></div>
        </article>
        <article class="usuarios-kpi usuarios-kpi--purple"><span class="usuarios-kpi-icon"><i class="bi bi-person-lock" aria-hidden="true"></i></span>
            <div><small>Usuarios inactivos</small><strong>{{ $accountStatusAvailable ? number_format($inactiveUsers) : '�' }}</strong><span>{{ $accountStatusAvailable && $totalUsers > 0 ? number_format(($inactiveUsers / $totalUsers) * 100, 0).'%' : 'Control de estado pendiente de migraci�n' }}</span></div>
        </article>
        <article class="usuarios-kpi usuarios-kpi--cyan"><span class="usuarios-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span>
            <div><small>Roles configurados</small><strong>{{ number_format($rolesConfigured) }}</strong><span>Roles configurables activos</span></div>
        </article>
    </section>

    <form class="usuarios-filters" method="get" action="{{ route('management.users') }}">
        <label class="usuarios-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar usuarios</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar por nombre, correo, rol o raz�n social..."></label>
        <button class="usuarios-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
        <div class="usuarios-filter-row">
            <label><span>Rol</span><select name="rol">
                    <option value="">Todos</option>@foreach ($roles as $role)<option value="{{ $role }}" @selected(($filters['rol'] ?? null)===$role)>{{ $roleLabels[$role] ?? $role }}</option>@endforeach
                </select></label>
            <label><span>Estado</span><select name="estado">
                    <option value="">Todos</option>
                    <option value="activo" @selected(($filters['estado'] ?? null)==='activo' )>Activos</option>
                    <option value="inactivo" @selected(($filters['estado'] ?? null)==='inactivo' )>Inactivos</option>
                </select></label>
            <label><span>Ciudad</span><select name="ciudad">
                    <option value="">Todas</option>@foreach ($cities as $city)<option value="{{ $city }}" @selected(($filters['ciudad'] ?? null)===$city)>{{ $city }}</option>@endforeach
                </select></label>
            <label><span>Fecha de registro</span><select name="fecha">
                    <option value="">Todos</option>
                    <option value="hoy" @selected(($filters['fecha'] ?? null)==='hoy' )>Hoy</option>
                    <option value="ultimos_30" @selected(($filters['fecha'] ?? null)==='ultimos_30' )>Últimos 30 d�as</option>
                    <option value="ultimos_90" @selected(($filters['fecha'] ?? null)==='ultimos_90' )>Últimos 90 d�as</option>
                </select></label>
            @if ($hasFilters)<a class="usuarios-clear-button" href="{{ route('management.users') }}"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Limpiar filtros</a>@else<button class="usuarios-clear-button" type="submit"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Aplicar filtros</button>@endif
        </div>
    </form>

    <section class="usuarios-list-card">
        <header class="usuarios-card-header">
            <div>
                <h2>Usuarios registrados ({{ number_format($users->total()) }})</h2>
                <p>Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} usuarios</p>
            </div>
            <div class="usuarios-card-tools"><span>Ordenar por: <b>Fecha de registro</b></span></div>
        </header>
        <div class="usuarios-table-wrap">
            <table class="usuarios-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                    <tr>
                        <td class="usuarios-index">{{ ($users->firstItem() ?? 1) + $loop->index }}.</td>
                        <td>
                            <div class="usuarios-identity"><span class="usuarios-avatar usuarios-avatar--{{ $userPresentation[$user->id]['avatarIndex'] }}">{{ $userPresentation[$user->id]['initials'] }}</span><span><strong>{{ $user->nombre }}</strong><small>{{ $userPresentation[$user->id]['roles'] }}</small></span></div>
                        </td>
                        <td class="usuarios-email">{{ $user->correo }}</td>
                        <td><span class="usuarios-role usuarios-role--{{ $userPresentation[$user->id]['roleIndex'] }}">{{ $userPresentation[$user->id]['roles'] }}</span></td>
                        <td>{{ $user->customer?->ciudad ?: '�' }}</td>
                        <td>@if ($accountStatusAvailable)<span class="usuarios-status usuarios-status--{{ $user->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $user->activo ? 'Activo' : 'Inactivo' }}</span>@else<span class="usuarios-status">Sin control de estado</span>@endif</td>
                        <td class="usuarios-date">{{ $user->creado_en?->format('d/m/Y') }}<small>{{ $user->creado_en?->format('H:i') }}</small></td>
                        <td class="usuarios-actions"><button class="usuarios-action" type="button" data-open-user-view="{{ $user->id }}" title="Ver usuario" aria-label="Ver {{ $user->nombre }}"><i class="bi bi-eye" aria-hidden="true"></i></button><button class="usuarios-action" type="button" data-open-user-edit="{{ $user->id }}" title="Editar usuario" aria-label="Editar {{ $user->nombre }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>@if ($accountStatusAvailable)<form method="post" action="{{ route('management.users.activate', $user) }}" data-confirm="�{{ $user->activo ? 'Desactivar' : 'Reactivar' }} esta cuenta?">@csrf @method('PATCH')<input type="hidden" name="activo" value="{{ $user->activo ? 0 : 1 }}"><button class="usuarios-action" type="submit" title="{{ $user->activo ? 'Desactivar' : 'Reactivar' }}" aria-label="{{ $user->activo ? 'Desactivar' : 'Reactivar' }} {{ $user->nombre }}"><i class="bi bi-{{ $user->activo ? 'person-lock' : 'person-check' }}" aria-hidden="true"></i></button></form>@endif</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="usuarios-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No hay usuarios que coincidan.</strong><span>Prueba con otros filtros o registra un nuevo usuario.</span></td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="usuarios-pagination"><span>Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} de {{ number_format($users->total()) }} usuarios</span>
            <div class="usuarios-pagination-controls">{{ $users->onEachSide(1)->links() }}
                <form method="get" action="{{ route('management.users') }}" class="usuarios-per-page">@foreach ($paginationQuery as $key => $value) @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="por_pagina" aria-label="Usuarios por p�gina" data-users-per-page>@foreach ($pageSizes as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>@endforeach</select><span>por p�gina</span></form>
            </div>
        </footer>
    </section>

    <div class="usuarios-bottom-grid">
        <section class="usuarios-bottom-card">
            <header class="usuarios-card-header">
                <div>
                    <h2>Distribuci�n por roles</h2>
                    <p>Usuarios agrupados por permisos.</p>
                </div>
            </header>
            <div class="usuarios-distribution">
                <div class="usuarios-donut"><strong>{{ number_format($totalUsers) }}</strong><span>Usuarios</span></div>
                <div class="usuarios-role-list">@forelse ($roleDistribution as $item)<div><span><i class="usuarios-dot usuarios-dot--{{ $loop->index % 7 }}" aria-hidden="true"></i>{{ $roleLabels[$item->rol] ?? $item->rol }}</span><b>{{ $item->total }}</b><small>{{ $totalUsers > 0 ? number_format(($item->total / $totalUsers) * 100, 1) : 0 }}%</small></div>@empty<span class="usuarios-muted">Sin distribuci�n disponible.</span>@endforelse</div>
            </div>
        </section>
        <section class="usuarios-bottom-card">
            <header class="usuarios-card-header">
                <div>
                    <h2>Actividad reciente</h2>
                    <p>Últimos usuarios registrados.</p>
                </div><a href="{{ route('management.audit') }}">Ver todas</a>
            </header>
            <div class="usuarios-activity-list">@forelse ($recentUsers as $recentUser)<div class="usuarios-activity-row"><span class="usuarios-activity-icon usuarios-activity-icon--{{ $loop->index % 2 ? 'purple' : 'blue' }}"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><strong>Nuevo usuario creado</strong><small>{{ $recentUser->nombre }} � {{ $recentUserPresentation[$recentUser->id]['roles'] ?? 'Sin rol vigente' }}</small></span><time>{{ $recentUser->creado_en?->diffForHumans() }}</time></div>@empty<div class="usuarios-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los nuevos registros aparecer�n aqu�.</span></div>@endforelse</div>
        </section>
    </div>
</section>

<dialog class="usuarios-dialog" id="nuevo-usuario" aria-labelledby="usuario-crear-titulo">
    <div class="usuarios-dialog-card">
        <header class="usuarios-dialog-header">
            <div><span class="usuarios-dialog-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><small>Gesti�n de usuarios</small>
                    <h2 id="usuario-crear-titulo">Registrar nuevo usuario</h2>
                    <p>Crea una cuenta y as�gnale un rol configurable.</p>
                </span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <div class="usuarios-dialog-body">@include('gestion.administracion.seguridad.usuarios.registrar', ['roles' => $roles, 'roleLabels' => $roleLabels, 'roleScopes' => $roleScopes, 'sites' => $sites])</div>
    </div>
</dialog>

@foreach ($users as $user)
<dialog class="usuarios-dialog usuarios-view-dialog" id="ver-usuario-{{ $user->id }}" aria-labelledby="usuario-detalle-titulo-{{ $user->id }}">
    <div class="usuarios-dialog-card">
        <header class="usuarios-dialog-header">
            <div><span class="usuarios-dialog-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span><span><small>{{ $userPresentation[$user->id]['roles'] }}</small>
                    <h2 id="usuario-detalle-titulo-{{ $user->id }}">{{ $user->nombre }}</h2>
                    <p>{{ $user->correo }}</p>
                </span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <div class="usuarios-view-body">
            <div class="usuarios-view-identity"><span class="usuarios-avatar usuarios-avatar--{{ $loop->index % 5 }}">{{ $userPresentation[$user->id]['initials'] }}</span>
                <div>
                    <h3>{{ $user->nombre }}</h3>
                    <p>Cuenta registrada en CELL SAC.</p>
                </div>
            </div>
            <div class="usuarios-view-grid">
                <div><small>Correo electr�nico</small><strong>{{ $user->correo }}</strong></div>
                <div><small>Rol asignado</small><strong>{{ $userPresentation[$user->id]['roles'] }}</strong></div>
                <div><small>Ciudad</small><strong>{{ $user->customer?->ciudad ?: 'No registrada' }}</strong></div>
                <div><small>Estado</small><span class="usuarios-status usuarios-status--{{ $user->activo ? 'active' : 'inactive' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $user->activo ? 'Activo' : 'Inactivo' }}</span></div>
                <div><small>Fecha de registro</small><strong>{{ $user->creado_en?->format('d/m/Y H:i') }}</strong></div>
                <div><small>Documento</small><strong>{{ $user->customer?->documento ?: 'No registrado' }}</strong></div>
            </div>
        </div>
        <footer class="usuarios-dialog-footer"><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cerrar</button></footer>
    </div>
</dialog>
<dialog class="usuarios-dialog" id="editar-usuario-{{ $user->id }}" aria-labelledby="usuario-editar-titulo-{{ $user->id }}">
    <div class="usuarios-dialog-card">
        <header class="usuarios-dialog-header">
            <div><span class="usuarios-dialog-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span><span><small>Gesti�n de usuarios</small>
                    <h2 id="usuario-editar-titulo-{{ $user->id }}">Editar usuario</h2>
                    <p>Actualiza datos sin cambiar las asignaciones RBAC.</p>
                </span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </header>
        <form method="POST" action="{{ route('management.users.update', $user) }}" class="usuarios-dialog-body">@csrf @method('PUT')<label>Nombre completo<input name="nombre" value="{{ $user->nombre }}" maxlength="120" required></label><label>Correo electr�nico<input type="email" name="correo" value="{{ $user->correo }}" maxlength="160" required></label><label>Nueva contrase�a <small>(opcional)</small><input type="password" name="password" minlength="12" autocomplete="new-password"></label><label>Confirmar contrase�a<input type="password" name="password_confirmation" minlength="12" autocomplete="new-password"></label>
            <footer class="usuarios-dialog-footer"><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cancelar</button><button class="usuarios-primary-button" type="submit">Guardar cambios</button></footer>
        </form>
    </div>
</dialog>
@endforeach
@endsection
{{-- Carga el comportamiento interactivo desde un archivo JavaScript externo. --}}
@push('scripts')
@vite('resources/js/gestion/administracion/seguridad/usuarios/listar.js')
@endpush
