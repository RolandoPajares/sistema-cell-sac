@extends('layouts.administracion')

@section('title', 'Cajas, aperturas y cierres')
@section('body_class', 'modulo-finanzas-facturacion')

@section('content')
<section class="tc-ff" data-cajas-operativas data-cajas-reopen="{{ old('_form') === 'crear' && $errors->any() ? 'crear' : '' }}">
    <header class="tc-ff__header">
        <div>
            <div class="tc-ff__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i><span>Finanzas y Facturación</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Cajas, aperturas y cierres</strong></div>
            <h1><i class="bi bi-cash-stack" aria-hidden="true"></i>Cajas, aperturas y cierres</h1>
            <p>Consulta y administra cajas de las sedes de la empresa activa.</p>
        </div>
        <div class="tc-ff__header-actions">
            @if ($canCreate)
                <button class="tc-ff__button tc-ff__button--primary" type="button" data-box-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva caja</button>
            @endif
        </div>
    </header>

    @if (session('status')) <p class="alert alert-success" role="status">{{ session('status') }}</p> @endif
    @foreach ($errors->all() as $error) <p class="text-danger" role="alert">{{ $error }}</p> @endforeach

    <div class="tc-ff__metrics">
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><small>Cajas registradas</small><strong>{{ $totalBoxes }}</strong><span>Empresa activa</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--verde"><span class="tc-ff__metric-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span><div><small>Cajas activas</small><strong>{{ $activeBoxes }}</strong><span>Disponibles para operar</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--ambar"><span class="tc-ff__metric-icon"><i class="bi bi-pause-circle" aria-hidden="true"></i></span><div><small>Cajas inactivas</small><strong>{{ $totalBoxes - $activeBoxes }}</strong><span>Conservadas en el historial</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-safe" aria-hidden="true"></i></span><div><small>Sesiones abiertas</small><strong>{{ $openSessions }}</strong><span>Registradas en sesiones_caja</span></div></article>
    </div>

    <form class="tc-ff__filters" method="GET" action="{{ route('management.payments.preview.cash_registers') }}">
        <label class="tc-ff__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="120" placeholder="Buscar por código o nombre" aria-label="Buscar cajas"></label>
        <div class="tc-ff__filter-fields">
            <label><span>Sede</span><select name="sede_id"><option value="">Todas las sedes</option>@foreach ($sites as $site)<option value="{{ $site->id }}" @selected((string) ($filters['sede_id'] ?? '') === (string) $site->id)>{{ $site->nombre }}</option>@endforeach</select></label>
            <label><span>Estado</span><select name="activa"><option value="">Todas</option><option value="1" @selected((string) ($filters['activa'] ?? '') === '1')>Activa</option><option value="0" @selected((string) ($filters['activa'] ?? '') === '0')>Inactiva</option></select></label>
            <button class="tc-ff__button tc-ff__button--primary" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i>Aplicar filtros</button>
            <a class="tc-ff__button" href="{{ route('management.payments.preview.cash_registers') }}">Limpiar</a>
        </div>
    </form>

    <div class="tc-ff__main-grid">
        <article class="tc-ff__card tc-ff__table-card">
            <div class="tc-ff__card-heading"><h2><i class="bi bi-list-ul" aria-hidden="true"></i>Cajas registradas</h2><small>{{ $boxes->total() }} registros</small></div>
            <div class="tc-ff__table-wrap">
                <table>
                    <thead><tr><th>Código</th><th>Sede</th><th>Nombre</th><th>Estado</th><th>Sesiones abiertas</th><th>Creación</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @forelse ($boxes as $box)
                            <tr>
                                <td>{{ $box->codigo }}</td>
                                <td>{{ $box->sede?->nombre ?? 'Sede no disponible' }}</td>
                                <td>{{ $box->nombre }}</td>
                                <td><span class="tc-ff__pill tc-ff__pill--{{ $box->activa ? 'verde' : 'rojo' }}">{{ $box->activa ? 'Activa' : 'Inactiva' }}</span></td>
                                <td>{{ $box->sesiones_abiertas }}</td>
                                <td>{{ $box->creado_en?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="tc-ff__row-actions">
                                        @if ($canUpdate && in_array($box->sede_id, $editableSiteIds, true))
                                            <button type="button" data-box-edit data-box-edit-url="{{ route('management.payments.cash_registers.update', $box) }}" data-box-code="{{ $box->codigo }}" data-box-name="{{ $box->nombre }}" data-box-site-id="{{ $box->sede_id }}" data-box-site-name="{{ $box->sede?->nombre }}" title="Editar caja" aria-label="Editar caja {{ $box->codigo }}"><i class="bi bi-pencil" aria-hidden="true"></i></button>
                                            <form method="POST" action="{{ route('management.payments.cash_registers.status.update', $box) }}" data-box-status>
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="activa" value="{{ $box->activa ? '0' : '1' }}">
                                                <button type="submit" data-box-status-label="{{ $box->activa ? 'desactivar' : 'activar' }}" title="{{ $box->activa ? 'Desactivar' : 'Activar' }} caja" aria-label="{{ $box->activa ? 'Desactivar' : 'Activar' }} caja {{ $box->codigo }}"><i class="bi {{ $box->activa ? 'bi-pause-circle' : 'bi-play-circle' }}" aria-hidden="true"></i></button>
                                            </form>
                                        @else
                                            <span>Solo consulta</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7">No hay cajas que coincidan con los filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($boxes->hasPages())
                <div class="tc-ff__card-heading">
                    <small>Página {{ $boxes->currentPage() }} de {{ $boxes->lastPage() }}</small>
                    <div class="tc-ff__header-actions">
                        @if ($boxes->previousPageUrl()) <a class="tc-ff__button" href="{{ $boxes->previousPageUrl() }}">Anterior</a> @endif
                        @if ($boxes->nextPageUrl()) <a class="tc-ff__button" href="{{ $boxes->nextPageUrl() }}">Siguiente</a> @endif
                    </div>
                </div>
            @endif
        </article>
        <aside class="tc-ff__card tc-ff__aside">
            <h2><i class="bi bi-geo-alt" aria-hidden="true"></i>Cajas por sede</h2>
            <p>Distribución de los registros activos e inactivos en la empresa.</p>
            <div class="tc-ff__summary">
                <strong>{{ $totalBoxes }} cajas</strong>
                @forelse ($sites as $site)
                    <div><span>{{ $site->nombre }}</span><b>{{ $siteCounts[$site->id] ?? 0 }}</b></div>
                @empty
                    <div><span>No hay sedes activas.</span></div>
                @endforelse
            </div>
        </aside>
    </div>

    <div class="tc-ff__bottom-grid">
        <article class="tc-ff__card"><div class="tc-ff__card-heading"><h2><i class="bi bi-check-circle" aria-hidden="true"></i>Disponibilidad</h2></div><div class="tc-ff__ranking"><div><b>1</b><span>Cajas activas</span><strong>{{ $activeBoxes }}</strong></div><div><b>2</b><span>Cajas inactivas</span><strong>{{ $totalBoxes - $activeBoxes }}</strong></div></div></article>
        <article class="tc-ff__card"><div class="tc-ff__card-heading"><h2><i class="bi bi-safe" aria-hidden="true"></i>Sesiones</h2></div><div class="tc-ff__ranking"><div><b>1</b><span>Abiertas</span><strong>{{ $openSessions }}</strong></div></div></article>
        <article class="tc-ff__card"><div class="tc-ff__card-heading"><h2><i class="bi bi-shield-check" aria-hidden="true"></i>Control operativo</h2></div><p>Una caja con sesión abierta no puede editarse ni desactivarse.</p></article>
    </div>

    @if ($canCreate)
        <dialog class="tc-ff__dialog" data-box-dialog="crear" aria-labelledby="box-create-title">
            <form class="tc-ff__dialog-card" method="POST" action="{{ route('management.payments.cash_registers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="crear">
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><h2 id="box-create-title">Nueva caja</h2><p>Registro administrativo de una caja real.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <label class="tc-ff__field">Sede *<select name="sede_id" required><option value="">Selecciona una sede</option>@foreach ($createSites as $site)<option value="{{ $site->id }}" @selected((string) old('sede_id') === (string) $site->id)>{{ $site->nombre }}</option>@endforeach</select></label>
                    <label class="tc-ff__field">Código *<input type="text" name="codigo" value="{{ old('codigo') }}" maxlength="40" required></label>
                    <label class="tc-ff__field tc-ff__field--full">Nombre *<input type="text" name="nombre" value="{{ old('nombre') }}" maxlength="120" required></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Guardar caja</button></footer>
            </form>
        </dialog>
    @endif
    @if ($canUpdate)
        <dialog class="tc-ff__dialog" data-box-dialog="editar" aria-labelledby="box-edit-title">
            <form class="tc-ff__dialog-card" method="POST" data-box-edit-form>
                @csrf
                @method('PUT')
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-pencil" aria-hidden="true"></i></span><div><h2 id="box-edit-title">Editar caja</h2><p>La sede original no puede cambiarse.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <input type="hidden" name="sede_id" data-box-field="sede_id">
                    <label class="tc-ff__field">Sede<input type="text" data-box-field="sede_nombre" disabled></label>
                    <label class="tc-ff__field">Código *<input type="text" name="codigo" maxlength="40" required data-box-field="codigo"></label>
                    <label class="tc-ff__field tc-ff__field--full">Nombre *<input type="text" name="nombre" maxlength="120" required data-box-field="nombre"></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Guardar cambios</button></footer>
            </form>
        </dialog>
    @endif
</section>
@endsection
