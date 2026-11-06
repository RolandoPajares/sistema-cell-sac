@extends('layouts.administracion')

@section('title', 'Cupones')
@section('body_class', auth()->user()?->isRole('administrador') ? 'modulo-cupones' : '')

@section('content')
    @php
        $rolPanel = auth()->user()?->rol ?: auth()->user()?->rolesVigentes()->value('roles.codigo');
    @endphp
    @if ($rolPanel !== 'administrador')
        <section class="container content-page">
            <h1>Cupones</h1>
            <p class="notice">Gestiona los cupones disponibles para las promociones comerciales.</p>
            @include('gestion.cupones.crear')
            @foreach ($coupons as $coupon)
                <article class="data-row">
                    <strong>{{ $coupon->codigo }}</strong>
                    <span>{{ $coupon->tipo_descuento === 'porcentaje' ? $coupon->valor . ' %' : 'S/ ' . number_format((float) $coupon->valor, 2) }}</span>
                    <span>{{ $coupon->estado_vigencia }}</span>
                </article>
            @endforeach
            {{ $coupons->links() }}
        </section>
    @else
        @php
            $statusClass = static fn (string $status): string => match ($status) {
                'Activo' => 'active',
                'Programado' => 'scheduled',
                default => 'finished',
            };
        @endphp
        <section class="admin-marketing-module admin-cupones-page" data-pending-module="cupones" data-em-coupon-page>
            @if (session('status'))
                <p class="gestion-pendiente-feedback" role="status">{{ session('status') }}</p>
            @endif
            @if ($errors->any())
                <div class="gestion-pendiente-feedback" role="alert">
                    <strong>Revisa la información del cupón:</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            @unless ($empresaCuponDisponible)
                <p class="gestion-pendiente-feedback" role="status">No se pueden consultar ni crear cupones hasta contar con propiedad empresarial persistida en MariaDB.</p>
            @endunless

            <header class="admin-marketing-header">
                <nav class="admin-marketing-breadcrumb" aria-label="Ruta de navegación"><span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Marketing</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Cupones y promociones</strong></nav>
                <div class="admin-marketing-heading-row">
                    <div><span class="admin-marketing-eyebrow"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Marketing</span><h1>Gestión de cupones y promociones</h1><p>Configura descuentos, controla condiciones de uso y mide su impacto comercial.</p></div>
                    <div class="admin-marketing-actions">
                        <details class="admin-marketing-export-menu"><summary class="admin-marketing-button admin-marketing-button--secondary"><i class="bi bi-download" aria-hidden="true"></i> Exportar <i class="bi bi-chevron-down" aria-hidden="true"></i></summary><div class="admin-marketing-export-options"><a href="{{ route('management.coupons.export', ['format' => 'pdf'] + request()->query()) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a><a href="{{ route('management.coupons.export', ['format' => 'excel'] + request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a><a href="{{ route('management.coupons.export', ['format' => 'csv'] + request()->query()) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a></div></details>
                        <a class="admin-marketing-button admin-marketing-button--secondary" href="#cupones-estadisticas"><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ver estadísticas</a>
                        <button class="admin-marketing-button admin-marketing-button--primary" type="button" data-pending-open="crear" @disabled(!$empresaCuponDisponible) title="{{ $empresaCuponDisponible ? 'Crear cupón' : 'Requiere estructura de propiedad por empresa' }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo cupón</button>
                        <a class="admin-marketing-button admin-marketing-button--secondary" href="{{ route('management.campaigns') }}"><i class="bi bi-megaphone" aria-hidden="true"></i> Nueva promoción</a>
                    </div>
                </div>
            </header>

            <section class="admin-marketing-kpis" aria-label="Indicadores de cupones">
                <article class="admin-marketing-kpi admin-marketing-kpi--blue"><span><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><small>Cupones registrados</small><strong>{{ number_format($total) }}</strong><em>Registros en el sistema</em></div></article>
                <article class="admin-marketing-kpi admin-marketing-kpi--orange"><span><i class="bi bi-megaphone" aria-hidden="true"></i></span><div><small>Cupones vigentes</small><strong>{{ number_format($activos) }}</strong><em>Dentro de su fecha y límite de uso</em></div></article>
                <article class="admin-marketing-kpi admin-marketing-kpi--green"><span><i class="bi bi-cart-check" aria-hidden="true"></i></span><div><small>Canjes registrados</small><strong>{{ number_format($canjes) }}</strong><em>Suma de usos guardados por cupón</em></div></article>
                <article class="admin-marketing-kpi admin-marketing-kpi--purple"><span><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><div><small>Ventas con descuento</small><strong>—</strong><em>El SQL no vincula ventas con cupones</em></div></article>
            </section>

            <form class="admin-marketing-filters" method="GET" action="{{ route('management.coupons') }}" id="cupones-filtros">
                <label class="admin-marketing-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar cupones</span><input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar código de cupón..."></label>
                <label><span class="visually-hidden">Tipo</span><select name="tipo"><option value="">Tipo: Todos</option><option value="porcentaje" @selected(($filters['tipo'] ?? null) === 'porcentaje')>Porcentaje</option><option value="fijo" @selected(($filters['tipo'] ?? null) === 'fijo')>Monto fijo</option></select></label>
                <label><span class="visually-hidden">Estado</span><select name="estado"><option value="">Estado: Todos</option>@foreach (['Activo', 'Programado', 'Finalizado'] as $status)<option value="{{ $status }}" @selected(($filters['estado'] ?? null) === $status)>{{ $status }}</option>@endforeach</select></label>
                <label><span class="visually-hidden">Vigencia</span><select name="vigencia"><option value="">Vigencia: Todas</option><option value="este_mes" @selected(($filters['vigencia'] ?? null) === 'este_mes')>Vence este mes</option><option value="proximos_30_dias" @selected(($filters['vigencia'] ?? null) === 'proximos_30_dias')>Próximos 30 días</option><option value="historico" @selected(($filters['vigencia'] ?? null) === 'historico')>Histórico</option></select></label>
                <button class="admin-marketing-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
                <a class="admin-marketing-clear-button" href="{{ route('management.coupons') }}">Limpiar filtros</a>
            </form>

            <section class="admin-marketing-table-card">
                <header class="admin-marketing-card-header"><div><span class="admin-marketing-card-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><h2>Listado de cupones y promociones</h2><p>{{ number_format($coupons->total()) }} registros disponibles para administrar.</p></div></div><span class="admin-marketing-count">{{ number_format($coupons->total()) }} registros</span></header>
                <div class="admin-marketing-table-wrap"><table class="admin-marketing-table"><thead><tr><th>Código / Promoción</th><th>Descuento</th><th>Aplicación</th><th>Vigencia</th><th>Uso</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
                    @forelse ($coupons as $coupon)
                        <tr>
                            <td><strong>{{ $coupon->codigo }}</strong><small>Cupón registrado</small></td>
                            <td><strong>{{ $coupon->tipo_descuento === 'porcentaje' ? number_format((float) $coupon->valor, 0).' %' : 'S/ '.number_format((float) $coupon->valor, 2) }}</strong><small>Compra mínima S/ {{ number_format((float) $coupon->minimo_compra, 2) }}</small></td>
                            <td>Todo el catálogo</td>
                            <td>{{ $coupon->inicia_en?->format('d/m/Y') }}<small>hasta {{ $coupon->finaliza_en?->format('d/m/Y') }}</small></td>
                            <td><div class="admin-marketing-usage"><span>{{ $coupon->usos }} / {{ $coupon->limite_usos ?? '∞' }}</span><i style="--usage: {{ $coupon->limite_usos ? min(100, ((int) $coupon->usos / max(1, (int) $coupon->limite_usos)) * 100) : 0 }}%"></i></div></td>
                            <td><span class="admin-marketing-status admin-marketing-status--{{ $statusClass($coupon->estado_vigencia) }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $coupon->estado_vigencia }}</span></td>
                            <td class="admin-marketing-row-actions" data-em-coupon-row data-em-coupon-code="{{ $coupon->codigo }}" data-em-coupon-type="{{ $coupon->tipo_descuento }}" data-em-coupon-value="{{ number_format((float) $coupon->valor, 2, '.', '') }}" data-em-coupon-minimum="{{ number_format((float) $coupon->minimo_compra, 2, '.', '') }}" data-em-coupon-limit="{{ $coupon->limite_usos ?? '' }}" data-em-coupon-uses="{{ $coupon->usos }}" data-em-coupon-start="{{ $coupon->inicia_en?->format('Y-m-d\\TH:i') ?? '' }}" data-em-coupon-end="{{ $coupon->finaliza_en?->format('Y-m-d\\TH:i') ?? '' }}" data-em-coupon-status="{{ $coupon->estado_vigencia }}" data-em-coupon-update-url="{{ route('management.coupons.update', $coupon) }}" data-em-coupon-deactivate-url="{{ route('management.coupons.deactivate', $coupon) }}">
                                <button type="button" data-em-coupon-action="detail"><i class="bi bi-eye" aria-hidden="true"></i><span>Ver</span></button>
                                <button type="button" data-em-coupon-action="edit"><i class="bi bi-pencil" aria-hidden="true"></i><span>Editar</span></button>
                                @if ($coupon->activo)<button class="is-danger" type="button" data-em-coupon-action="deactivate"><i class="bi bi-trash3" aria-hidden="true"></i><span>Cancelar</span></button>@endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="admin-marketing-empty"><span><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><strong>No hay cupones para mostrar</strong><p>Crea el primer cupón para comenzar a controlar tus promociones.</p><button type="button" class="admin-marketing-empty-button" data-pending-open="crear" @disabled(!$empresaCuponDisponible) title="{{ $empresaCuponDisponible ? 'Crear cupón' : 'Requiere estructura de propiedad por empresa' }}"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear cupón</button></div></td></tr>
                    @endforelse
                </tbody></table></div>
                <footer class="admin-marketing-table-footer"><span>Mostrando {{ $coupons->firstItem() ?? 0 }} a {{ $coupons->lastItem() ?? 0 }} de {{ number_format($coupons->total()) }} registros</span>{{ $coupons->links() }}</footer>
            </section>

            <section class="admin-marketing-bottom-grid" id="cupones-estadisticas">
                <article class="admin-marketing-summary-card"><header><div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--orange"><i class="bi bi-calendar-event" aria-hidden="true"></i></span><div><h2>Próximos vencimientos</h2><p>Cupones con fecha final en los próximos 30 días.</p></div></div><a href="{{ route('management.coupons', ['vigencia' => 'proximos_30_dias']) }}">Ver calendario <i class="bi bi-arrow-right" aria-hidden="true"></i></a></header>
                    @forelse ($proximos as $coupon)<div class="admin-marketing-summary-empty"><strong>{{ $coupon->codigo }}</strong><span>Vence {{ $coupon->finaliza_en?->format('d/m/Y H:i') }}</span></div>@empty<div class="admin-marketing-summary-empty"><i class="bi bi-calendar-check" aria-hidden="true"></i><strong>Sin vencimientos próximos</strong><span>No hay cupones activos que venzan dentro de 30 días.</span></div>@endforelse
                </article>
                <article class="admin-marketing-summary-card"><header><div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--blue"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><div><h2>Cupones más usados</h2><p>Ordenados por usos registrados.</p></div></div><a href="{{ route('management.coupons', ['estado' => 'Activo']) }}">Ver cupones <i class="bi bi-arrow-right" aria-hidden="true"></i></a></header>
                    @forelse ($mas_usados as $coupon)<div class="admin-marketing-summary-empty"><strong>{{ $coupon->codigo }}</strong><span>{{ number_format((int) $coupon->usos) }} canjes</span></div>@empty<div class="admin-marketing-summary-empty"><i class="bi bi-graph-up" aria-hidden="true"></i><strong>Sin canjes registrados</strong><span>El ranking aparecerá cuando los usos se registren.</span></div>@endforelse
                </article>
                <article class="admin-marketing-summary-card"><header><div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--green"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><div><h2>Impacto promocional</h2><p>Atribución de ventas por cupón.</p></div></div></header><div class="admin-marketing-impact"><strong>—</strong><span>Ventas atribuidas</span><div><i></i></div><small>La base actual no relaciona cupones con ventas.</small></div></article>
            </section>

            <dialog class="admin-marketing-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="cupones-crear-title"><div class="admin-marketing-dialog-card"><header><div><span class="admin-marketing-dialog-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><small>Marketing</small><h2 id="cupones-crear-title">Nuevo cupón</h2><p>Configura un descuento para tus clientes.</p></div></div><button type="button" data-pending-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="admin-marketing-dialog-body">@include('gestion.cupones.crear')</div></div></dialog>

            <dialog class="admin-marketing-dialog gestion-pendiente-modal em-marketing-dialog" data-em-coupon-dialog="detail" aria-labelledby="em-coupon-detail-title"><div class="admin-marketing-dialog-card"><header><div><span class="admin-marketing-dialog-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><small>Marketing · Consulta</small><h2 id="em-coupon-detail-title">Detalle del cupón</h2><p>Información del registro seleccionado.</p></div></div><button type="button" data-em-coupon-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="admin-marketing-dialog-body"><dl class="em-marketing-dialog__details"><div><dt>Código</dt><dd data-em-coupon-detail="code">—</dd></div><div><dt>Descuento</dt><dd data-em-coupon-detail="discount">—</dd></div><div><dt>Compra mínima</dt><dd data-em-coupon-detail="minimum">—</dd></div><div><dt>Usos registrados</dt><dd data-em-coupon-detail="uses">—</dd></div><div><dt>Vigencia</dt><dd data-em-coupon-detail="period">—</dd></div><div><dt>Estado</dt><dd data-em-coupon-detail="status">—</dd></div></dl></div><footer class="em-marketing-dialog__footer"><button type="button" class="admin-marketing-button admin-marketing-button--secondary" data-em-coupon-close>Cerrar</button></footer></div></dialog>

            <dialog class="admin-marketing-dialog gestion-pendiente-modal em-marketing-dialog" data-em-coupon-dialog="edit" aria-labelledby="em-coupon-edit-title"><div class="admin-marketing-dialog-card"><header><div><span class="admin-marketing-dialog-icon"><i class="bi bi-pencil-square" aria-hidden="true"></i></span><div><small>Marketing · Edición</small><h2 id="em-coupon-edit-title">Editar configuración</h2><p>Actualiza las condiciones del cupón.</p></div></div><button type="button" data-em-coupon-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header>
                <form method="POST" action="{{ route('management.coupons') }}" id="em-coupon-edit-form" data-em-coupon-edit-form>@csrf<input type="hidden" name="_method" value="PUT"><div class="admin-marketing-dialog-body"><label>Código<input type="text" data-em-coupon-edit-code readonly></label><div class="em-marketing-dialog__grid"><label>Tipo de descuento<select name="tipo_descuento" data-em-coupon-edit-type required><option value="porcentaje">Porcentaje</option><option value="fijo">Monto fijo</option></select></label><label>Valor<input type="number" name="valor" min="0.01" max="99999999.99" step="0.01" data-em-coupon-edit-value required></label><label>Compra mínima (S/)<input type="number" name="minimo_compra" min="0" max="99999999.99" step="0.01" data-em-coupon-edit-minimum required></label><label>Límite de usos (opcional)<input type="number" name="limite_usos" min="1" max="4294967295" step="1" data-em-coupon-edit-limit></label><label>Vigente desde<input type="datetime-local" name="inicia_en" data-em-coupon-edit-start required></label><label>Vigente hasta<input type="datetime-local" name="finaliza_en" data-em-coupon-edit-end required></label></div><p class="em-marketing-dialog__error" data-em-coupon-edit-error role="alert" hidden></p><p class="em-marketing-dialog__feedback" data-em-coupon-edit-feedback role="status" aria-live="polite" hidden></p></div></form>
                <footer class="em-marketing-dialog__footer"><button type="button" class="admin-marketing-button admin-marketing-button--secondary" data-em-coupon-close>Cancelar</button><button type="submit" form="em-coupon-edit-form" class="admin-marketing-button admin-marketing-button--primary" data-em-coupon-edit-confirm>Guardar cambios</button></footer>
            </div></dialog>

            <dialog class="admin-marketing-dialog gestion-pendiente-modal em-marketing-dialog" data-em-coupon-dialog="deactivate" aria-labelledby="em-coupon-deactivate-title"><div class="admin-marketing-dialog-card"><header><div><span class="admin-marketing-dialog-icon"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i></span><div><small>Marketing · Confirmación</small><h2 id="em-coupon-deactivate-title">Cancelar cupón</h2><p>El cupón dejará de estar disponible para nuevos canjes.</p></div></div><button type="button" data-em-coupon-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="admin-marketing-dialog-body"><p class="em-marketing-dialog__record">Cupón seleccionado: <strong data-em-coupon-deactivate-code>—</strong></p><form method="POST" action="{{ route('management.coupons') }}" id="em-coupon-deactivate-form" data-em-coupon-deactivate-form>@csrf<input type="hidden" name="_method" value="DELETE"><label class="em-marketing-dialog__check"><input type="checkbox" name="confirmacion" value="1" data-em-coupon-deactivate-ack required> Confirmo cancelar este cupón.</label></form><p class="em-marketing-dialog__error" data-em-coupon-deactivate-feedback role="alert" hidden></p></div><footer class="em-marketing-dialog__footer"><button type="button" class="admin-marketing-button admin-marketing-button--secondary" data-em-coupon-close>Volver</button><button type="submit" form="em-coupon-deactivate-form" class="admin-marketing-button admin-marketing-button--primary" data-em-coupon-deactivate-confirm>Confirmar cancelación</button></footer></div></dialog>
        </section>
    @endif
@endsection
