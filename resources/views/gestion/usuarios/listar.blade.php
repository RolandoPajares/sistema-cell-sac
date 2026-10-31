@extends('layouts.administracion')

@section('title', 'Usuarios y roles')
@section('body_class', 'modulo-usuarios')

@section('content')
    @php
        $roleLabels = $roleLabels ?? [];
        $initialsFor = static fn (string $name): string => mb_strtoupper(mb_substr(trim($name), 0, 1));
        $formatRole = static fn (string $role): string => $roleLabels[$role] ?? ucfirst(str_replace('_', ' ', $role));
        $hasFilters = ($filters['buscar'] ?? null) || ($filters['rol'] ?? null) || ($filters['estado'] ?? null) || ($filters['ciudad'] ?? null) || ($filters['fecha'] ?? null);
    @endphp

    <section class="usuarios-page" data-usuarios-page>
        <header class="usuarios-hero">
            <div>
                <span class="usuarios-eyebrow">Gestión de usuarios</span>
                <h1>Usuarios y los siete roles <i class="bi bi-people-fill" aria-hidden="true"></i></h1>
                <p>Administra las cuentas de acceso al sistema y sus permisos.</p>
            </div>
            <button class="usuarios-primary-button" type="button" data-open-user-create><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo usuario</button>
        </header>

        <section class="usuarios-kpi-grid" aria-label="Resumen de usuarios">
            <article class="usuarios-kpi usuarios-kpi--blue"><span class="usuarios-kpi-icon"><i class="bi bi-people" aria-hidden="true"></i></span><div><small>Total de usuarios</small><strong>{{ number_format($totalUsers) }}</strong><span><i class="bi bi-arrow-up" aria-hidden="true"></i> +{{ number_format($currentMonthUsers) }} este mes</span></div></article>
            <article class="usuarios-kpi usuarios-kpi--green"><span class="usuarios-kpi-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div><small>Usuarios activos</small><strong>{{ number_format($activeUsers) }}</strong><span><i class="bi bi-check-circle" aria-hidden="true"></i> {{ $totalUsers > 0 ? number_format(($activeUsers / $totalUsers) * 100, 0) : 0 }}% del total</span></div></article>
            <article class="usuarios-kpi usuarios-kpi--purple"><span class="usuarios-kpi-icon"><i class="bi bi-person-lock" aria-hidden="true"></i></span><div><small>Usuarios inactivos</small><strong>{{ number_format($inactiveUsers) }}</strong><span>{{ $totalUsers > 0 ? number_format(($inactiveUsers / $totalUsers) * 100, 0) : 0 }}% del total</span></div></article>
            <article class="usuarios-kpi usuarios-kpi--cyan"><span class="usuarios-kpi-icon"><i class="bi bi-people-fill" aria-hidden="true"></i></span><div><small>Roles configurados</small><strong>{{ number_format($rolesConfigured) }}</strong><span>Roles del sistema</span></div></article>
        </section>

        <form class="usuarios-filters" method="get" action="{{ route('management.users') }}">
            <label class="usuarios-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar usuarios</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar por nombre, correo, rol o razón social..."></label>
            <button class="usuarios-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtros</button>
            <button class="usuarios-primary-button usuarios-primary-button--compact" type="button" data-open-user-create><i class="bi bi-person-plus" aria-hidden="true"></i> Nuevo usuario</button>
            <div class="usuarios-filter-row">
                <label><span>Rol</span><select name="rol"><option value="">Todos</option>@foreach ($roles as $role)<option value="{{ $role }}" @selected(($filters['rol'] ?? null) === $role)>{{ $roleLabels[$role] ?? $role }}</option>@endforeach</select></label>
                <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="activo" @selected(($filters['estado'] ?? null) === 'activo')>Activos</option><option value="inactivo" @selected(($filters['estado'] ?? null) === 'inactivo')>Inactivos</option></select></label>
                <label><span>Ciudad</span><select name="ciudad"><option value="">Todas</option>@foreach ($cities as $city)<option value="{{ $city }}" @selected(($filters['ciudad'] ?? null) === $city)>{{ $city }}</option>@endforeach</select></label>
                <label><span>Fecha de registro</span><select name="fecha"><option value="">Todos</option><option value="hoy" @selected(($filters['fecha'] ?? null) === 'hoy')>Hoy</option><option value="ultimos_30" @selected(($filters['fecha'] ?? null) === 'ultimos_30')>Últimos 30 días</option><option value="ultimos_90" @selected(($filters['fecha'] ?? null) === 'ultimos_90')>Últimos 90 días</option></select></label>
                @if ($hasFilters)<a class="usuarios-clear-button" href="{{ route('management.users') }}"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Limpiar filtros</a>@else<button class="usuarios-clear-button" type="submit"><i class="bi bi-arrow-clockwise" aria-hidden="true"></i> Aplicar filtros</button>@endif
            </div>
        </form>

        <section class="usuarios-list-card">
            <header class="usuarios-card-header"><div><h2>Usuarios registrados ({{ number_format($users->total()) }})</h2><p>Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} usuarios</p></div><div class="usuarios-card-tools"><span>Ordenar por: <b>Fecha de registro</b></span><button type="button" disabled title="Exportación disponible próximamente"><i class="bi bi-download" aria-hidden="true"></i> Exportar</button></div></header>
            <div class="usuarios-table-wrap">
                <table class="usuarios-table">
                    <thead><tr><th>#</th><th>Usuario</th><th>Correo</th><th>Rol</th><th>Ciudad</th><th>Estado</th><th>Registro</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="usuarios-index">{{ ($users->firstItem() ?? 1) + $loop->index }}.</td>
                                <td><div class="usuarios-identity"><span class="usuarios-avatar usuarios-avatar--{{ $loop->index % 5 }}">{{ $initialsFor($user->nombre) }}</span><span><strong>{{ $user->nombre }}</strong><small>{{ $formatRole($user->rol) }}</small></span></div></td>
                                <td class="usuarios-email">{{ $user->correo }}</td>
                                <td><span class="usuarios-role usuarios-role--{{ $loop->index % 7 }}">{{ $formatRole($user->rol) }}</span></td>
                                <td>{{ $user->customer?->ciudad ?: '—' }}</td>
                                <td><span class="usuarios-status usuarios-status--active"><i class="bi bi-circle-fill" aria-hidden="true"></i>Activo</span></td>
                                <td class="usuarios-date">{{ $user->created_at?->format('d/m/Y') }}<small>{{ $user->created_at?->format('H:i') }}</small></td>
                                <td class="usuarios-actions"><button class="usuarios-action" type="button" data-open-user-view="{{ $user->id }}" title="Ver usuario" aria-label="Ver {{ $user->nombre }}"><i class="bi bi-eye" aria-hidden="true"></i></button></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="usuarios-empty"><i class="bi bi-people" aria-hidden="true"></i><strong>No hay usuarios que coincidan.</strong><span>Prueba con otros filtros o registra un nuevo usuario.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="usuarios-pagination"><span>Mostrando {{ $users->firstItem() ?? 0 }} a {{ $users->lastItem() ?? 0 }} de {{ number_format($users->total()) }} usuarios</span><div class="usuarios-pagination-controls">{{ $users->onEachSide(1)->links() }}<form method="get" action="{{ route('management.users') }}" class="usuarios-per-page">@foreach (request()->except(['page', 'por_pagina']) as $key => $value) @if (is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif @endforeach<select name="por_pagina" aria-label="Usuarios por página" onchange="this.form.submit()">@foreach ([10, 20, 50] as $option)<option value="{{ $option }}" @selected(($filters['por_pagina'] ?? 10) === $option)>{{ $option }}</option>@endforeach</select><span>por página</span></form></div></footer>
        </section>

        <div class="usuarios-bottom-grid">
            <section class="usuarios-bottom-card"><header class="usuarios-card-header"><div><h2>Distribución por roles</h2><p>Usuarios agrupados por permisos.</p></div></header><div class="usuarios-distribution"><div class="usuarios-donut"><strong>{{ number_format($totalUsers) }}</strong><span>Usuarios</span></div><div class="usuarios-role-list">@forelse ($roleDistribution as $item)<div><span><i class="usuarios-dot usuarios-dot--{{ $loop->index % 7 }}" aria-hidden="true"></i>{{ $roleLabels[$item->rol] ?? $item->rol }}</span><b>{{ $item->total }}</b><small>{{ $totalUsers > 0 ? number_format(($item->total / $totalUsers) * 100, 1) : 0 }}%</small></div>@empty<span class="usuarios-muted">Sin distribución disponible.</span>@endforelse</div></div></section>
            <section class="usuarios-bottom-card"><header class="usuarios-card-header"><div><h2>Actividad reciente</h2><p>Últimos usuarios registrados.</p></div><a href="{{ route('management.audit') }}">Ver todas</a></header><div class="usuarios-activity-list">@forelse ($recentUsers as $recentUser)<div class="usuarios-activity-row"><span class="usuarios-activity-icon usuarios-activity-icon--{{ $loop->index % 2 ? 'purple' : 'blue' }}"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><strong>Nuevo usuario creado</strong><small>{{ $recentUser->nombre }} · {{ $formatRole($recentUser->rol) }}</small></span><time>{{ $recentUser->created_at?->diffForHumans() }}</time></div>@empty<div class="usuarios-secondary-empty"><i class="bi bi-clock-history" aria-hidden="true"></i><strong>Sin actividad reciente</strong><span>Los nuevos registros aparecerán aquí.</span></div>@endforelse</div></section>
        </div>
    </section>

    <dialog class="usuarios-dialog" id="nuevo-usuario">
        <div class="usuarios-dialog-card"><header class="usuarios-dialog-header"><div><span class="usuarios-dialog-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><small>Gestión de usuarios</small><h2>Registrar nuevo usuario</h2><p>Crea una cuenta y asígnale uno de los siete roles de CELL SAC.</p></span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="usuarios-dialog-body">@include('gestion.usuarios.crear', ['roles' => $roles, 'roleLabels' => $roleLabels])</div></div>
    </dialog>

    @foreach ($users as $user)
        <dialog class="usuarios-dialog usuarios-view-dialog" id="ver-usuario-{{ $user->id }}">
            <div class="usuarios-dialog-card"><header class="usuarios-dialog-header"><div><span class="usuarios-dialog-icon"><i class="bi bi-person-vcard" aria-hidden="true"></i></span><span><small>{{ $formatRole($user->rol) }}</small><h2>{{ $user->nombre }}</h2><p>{{ $user->correo }}</p></span></div><button type="button" class="usuarios-dialog-close" data-close-user-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="usuarios-view-body"><div class="usuarios-view-identity"><span class="usuarios-avatar usuarios-avatar--{{ $loop->index % 5 }}">{{ $initialsFor($user->nombre) }}</span><div><h3>{{ $user->nombre }}</h3><p>Cuenta registrada en CELL SAC.</p></div></div><div class="usuarios-view-grid"><div><small>Correo electrónico</small><strong>{{ $user->correo }}</strong></div><div><small>Rol asignado</small><strong>{{ $formatRole($user->rol) }}</strong></div><div><small>Ciudad</small><strong>{{ $user->customer?->ciudad ?: 'No registrada' }}</strong></div><div><small>Estado</small><span class="usuarios-status usuarios-status--active"><i class="bi bi-circle-fill" aria-hidden="true"></i>Activo</span></div><div><small>Fecha de registro</small><strong>{{ $user->created_at?->format('d/m/Y H:i') }}</strong></div><div><small>Documento</small><strong>{{ $user->customer?->documento ?: 'No registrado' }}</strong></div></div></div><footer class="usuarios-dialog-footer"><button class="usuarios-cancel-button" type="button" data-close-user-dialog>Cerrar</button></footer></div>
        </dialog>
    @endforeach
@endsection

@push('scripts')
    <script>
        (() => {
            const page = document.querySelector('[data-usuarios-page]');
            if (!page) {
                return;
            }

            const openDialog = (dialog) => {
                if (dialog && !dialog.open) {
                    dialog.showModal();
                }
            };

            page.querySelectorAll('[data-open-user-create]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector('#nuevo-usuario'))));
            page.querySelectorAll('[data-open-user-view]').forEach((button) => button.addEventListener('click', () => openDialog(document.querySelector(`#ver-usuario-${button.dataset.openUserView}`))));
            document.querySelectorAll('[data-close-user-dialog]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));
            document.querySelectorAll('.usuarios-dialog').forEach((dialog) => dialog.addEventListener('click', (event) => { if (event.target === dialog) { dialog.close(); } }));

            const form = document.querySelector('[data-user-form]');
            const role = form?.querySelector('[data-user-role]');
            const documentField = form?.querySelector('[data-user-document]');
            const roleNote = form?.querySelector('[data-user-role-note]');

            const updateCustomerFields = () => {
                const isCustomer = role?.value.startsWith('cliente_');
                if (documentField) {
                    documentField.required = Boolean(isCustomer);
                }
                if (roleNote) {
                    roleNote.hidden = !isCustomer;
                }
            };

            role?.addEventListener('change', updateCustomerFields);
            updateCustomerFields();

            @if ($errors->any())
                openDialog(document.querySelector('#nuevo-usuario'));
            @endif
        })();
    </script>
@endpush
