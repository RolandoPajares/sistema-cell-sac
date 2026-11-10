@extends('layouts.panel.principal')

@section('title', 'Cajas, aperturas y cierres')
@section('body_class', 'modulo-finanzas-facturacion')

@section('content')
<section class="tc-ff" data-cajas-operativas data-cajas-reopen="{{ old('_form') === 'crear' && $errors->any() ? 'crear' : '' }}">
    <header class="tc-ff__header">
        <div>
            <div class="tc-ff__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i><span>Finanzas y Facturación</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Cajas, aperturas y cierres</strong></div>
            <h1><i class="bi bi-safe" aria-hidden="true"></i>Cajas, aperturas y cierres</h1>
            <p>Sesiones reales de las cajas autorizadas, con saldos y movimientos auditados.</p>
        </div>
        <div class="tc-ff__header-actions">
            @if ($canOpen)
                <button type="button" class="tc-ff__button tc-ff__button--primary" data-box-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i>Nueva apertura</button>
            @endif
            <a class="tc-ff__button" href="{{ route('management.payments.preview.cash_registers') }}">Gestión de cajas</a>
        </div>
    </header>

    @if (session('status')) <p class="alert alert-success" role="status">{{ session('status') }}</p> @endif
    @foreach ($errors->all() as $error) <p class="text-danger" role="alert">{{ $error }}</p> @endforeach

    <div class="tc-ff__metrics">
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-safe" aria-hidden="true"></i></span><div><small>Sesiones registradas</small><strong>{{ $openCount + $closedCount }}</strong><span>En sedes autorizadas</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--verde"><span class="tc-ff__metric-icon"><i class="bi bi-unlock" aria-hidden="true"></i></span><div><small>Sesiones abiertas</small><strong>{{ $openCount }}</strong><span>Admiten operaciones ordinarias</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--azul"><span class="tc-ff__metric-icon"><i class="bi bi-lock" aria-hidden="true"></i></span><div><small>Sesiones cerradas</small><strong>{{ $closedCount }}</strong><span>Con arqueo conforme</span></div></article>
        <article class="tc-ff__metric tc-ff__metric--ambar"><span class="tc-ff__metric-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span><div><small>Cajas disponibles</small><strong>{{ $boxes->count() }}</strong><span>Sin sesión abierta</span></div></article>
    </div>

    <form class="tc-ff__filters" method="GET" action="{{ route('management.payments.preview.cash_sessions') }}">
        <label class="tc-ff__search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" maxlength="120" placeholder="Buscar caja por código o nombre" aria-label="Buscar sesiones"></label>
        <div class="tc-ff__filter-fields">
            <label><span>Sede</span><select name="sede_id"><option value="">Todas las sedes</option>@foreach ($sites as $site)<option value="{{ $site->id }}" @selected((string) ($filters['sede_id'] ?? '') === (string) $site->id)>{{ $site->nombre }}</option>@endforeach</select></label>
            <label><span>Estado</span><select name="estado"><option value="">Todos</option><option value="abierta" @selected(($filters['estado'] ?? '') === 'abierta')>Abierta</option><option value="cerrada" @selected(($filters['estado'] ?? '') === 'cerrada')>Cerrada</option></select></label>
            <label><span>Desde</span><input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}"></label>
            <label><span>Hasta</span><input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}"></label>
            <button class="tc-ff__button tc-ff__button--primary" type="submit">Aplicar filtros</button>
            <a class="tc-ff__button" href="{{ route('management.payments.preview.cash_sessions') }}">Limpiar</a>
        </div>
    </form>

    <div class="tc-ff__main-grid">
        <article class="tc-ff__card tc-ff__table-card">
            <div class="tc-ff__card-heading"><h2><i class="bi bi-list-ul" aria-hidden="true"></i>Historial de sesiones</h2><small>{{ $sessions->total() }} registros</small></div>
            <div class="tc-ff__table-wrap">
                <table>
                    <thead><tr><th>Caja</th><th>Sede</th><th>Responsable</th><th>Saldo inicial</th><th>Apertura</th><th>Cierre</th><th>Estado</th><th>Acción</th></tr></thead>
                    <tbody>
                        @forelse ($sessions as $session)
                            <tr>
                                <td>{{ $session->caja?->codigo }}</td>
                                <td>{{ $session->caja?->sede?->nombre }}</td>
                                <td>{{ $session->usuarioApertura?->nombre }}</td>
                                <td>S/ {{ $session->saldo_inicial }}</td>
                                <td>{{ $session->abierta_en?->format('d/m/Y H:i') }}</td>
                                <td>{{ $session->cerrada_en?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td><span class="tc-ff__pill tc-ff__pill--{{ $session->estado === 'abierta' ? 'verde' : 'azul' }}">{{ ucfirst($session->estado) }}</span></td>
                                <td><a href="{{ route('management.payments.cash_sessions.show', $session) }}">Ver arqueo</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8">No hay sesiones que coincidan con los filtros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($sessions->hasPages())
                <div class="tc-ff__card-heading"><small>Página {{ $sessions->currentPage() }} de {{ $sessions->lastPage() }}</small><div class="tc-ff__header-actions">@if ($sessions->previousPageUrl()) <a class="tc-ff__button" href="{{ $sessions->previousPageUrl() }}">Anterior</a> @endif @if ($sessions->nextPageUrl()) <a class="tc-ff__button" href="{{ $sessions->nextPageUrl() }}">Siguiente</a> @endif</div></div>
            @endif
        </article>
        <aside class="tc-ff__card tc-ff__aside">
            <h2><i class="bi bi-shield-check" aria-hidden="true"></i>Control de caja</h2>
            <p>Una caja admite una sola sesión abierta. El cierre requiere que el efectivo declarado coincida con el saldo calculado a partir de las entradas y salidas registradas.</p>
            <div class="tc-ff__summary"><strong>{{ $openCount }} abiertas</strong><div><span>Disponibles para apertura</span><b>{{ $boxes->count() }}</b></div><div><span>Sesiones cerradas</span><b>{{ $closedCount }}</b></div></div>
        </aside>
    </div>

    @if ($canOpen)
        <dialog class="tc-ff__dialog" data-box-dialog="crear" aria-labelledby="cash-open-title">
            <form class="tc-ff__dialog-card" method="POST" action="{{ route('management.payments.cash_sessions.store') }}">
                @csrf
                <input type="hidden" name="_form" value="crear">
                <input type="hidden" name="token" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                <header class="tc-ff__dialog-header"><div><span class="tc-ff__dialog-icon"><i class="bi bi-unlock" aria-hidden="true"></i></span><div><h2 id="cash-open-title">Nueva apertura</h2><p>Declara el efectivo inicial contado.</p></div></div><button type="button" class="tc-ff__dialog-close" data-box-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <div class="tc-ff__dialog-body"><div class="tc-ff__field-grid">
                    <label class="tc-ff__field tc-ff__field--full">Caja *<select name="caja_id" required><option value="">Selecciona una caja</option>@foreach ($boxes as $box)<option value="{{ $box->id }}" @selected((string) old('caja_id') === (string) $box->id)>{{ $box->codigo }} · {{ $box->sede?->nombre }}</option>@endforeach</select></label>
                    <label class="tc-ff__field tc-ff__field--full">Efectivo inicial contado (S/) *<input type="text" name="saldo_inicial" inputmode="decimal" value="{{ old('saldo_inicial', '0.00') }}" required></label>
                </div></div>
                <footer class="tc-ff__dialog-footer"><button type="button" class="tc-ff__button" data-box-close>Cancelar</button><button type="submit" class="tc-ff__button tc-ff__button--primary">Abrir caja</button></footer>
            </form>
        </dialog>
    @endif
</section>
@endsection
