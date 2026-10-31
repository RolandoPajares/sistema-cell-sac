@extends('layouts.administracion')

@section('title', 'Cupones')
@section('body_class', auth()->user()?->rol === 'administrador' ? 'modulo-cupones' : '')

@section('content')
    @if (auth()->user()?->rol !== 'administrador')
        <section class="container content-page">
            <h1>Cupones</h1>
            <p class="notice">Gestiona los cupones disponibles para las promociones comerciales.</p>
            @include('gestion.cupones.crear')
            @foreach ($coupons as $coupon)
                <article class="data-row">
                    <strong>{{ $coupon->codigo }}</strong>
                    <span>{{ $coupon->tipo_descuento === 'porcentaje' ? $coupon->valor . ' %' : 'S/ ' . number_format((float) $coupon->valor, 2) }}</span>
                    <span>{{ $coupon->activo ? 'Activo' : 'Finalizado' }}</span>
                </article>
            @endforeach
            {{ $coupons->links() }}
        </section>
    @else
    <section class="admin-marketing-module admin-cupones-page" data-pending-module="cupones">
        <header class="admin-marketing-header">
            <nav class="admin-marketing-breadcrumb" aria-label="Ruta de navegación"><span>Administración</span><i class="bi bi-chevron-right" aria-hidden="true"></i><span>Marketing</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Cupones y promociones</strong></nav>
            <div class="admin-marketing-heading-row">
                <div><span class="admin-marketing-eyebrow"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> Marketing</span><h1>Gestión de cupones y promociones</h1><p>Configura descuentos, controla condiciones de uso y mide su impacto comercial.</p></div>
                <div class="admin-marketing-actions"><button class="admin-marketing-button admin-marketing-button--secondary" type="button" data-pending-notice="La exportación se habilitará con los reportes comerciales integrados."><i class="bi bi-download" aria-hidden="true"></i> Exportar</button><button class="admin-marketing-button admin-marketing-button--secondary" type="button" data-pending-notice="Las estadísticas se habilitarán con los canjes registrados."><i class="bi bi-bar-chart-line" aria-hidden="true"></i> Ver estadísticas</button><button class="admin-marketing-button admin-marketing-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> Nuevo cupón</button><button class="admin-marketing-button admin-marketing-button--secondary" type="button" data-pending-notice="Las promociones se gestionan desde Campañas."><i class="bi bi-megaphone" aria-hidden="true"></i> Nueva promoción</button></div>
            </div>
        </header>

        <section class="admin-marketing-kpis" aria-label="Indicadores de cupones">
            <article class="admin-marketing-kpi admin-marketing-kpi--blue"><span><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><small>Cupones registrados</small><strong>{{ number_format($coupons->total()) }}</strong><em>Registros en el sistema</em></div></article>
            <article class="admin-marketing-kpi admin-marketing-kpi--orange"><span><i class="bi bi-megaphone" aria-hidden="true"></i></span><div><small>Promociones vigentes</small><strong>—</strong><em>Datos de vigencia</em></div></article>
            <article class="admin-marketing-kpi admin-marketing-kpi--green"><span><i class="bi bi-cart-check" aria-hidden="true"></i></span><div><small>Canjes realizados</small><strong>—</strong><em>Requiere registro de ventas</em></div></article>
            <article class="admin-marketing-kpi admin-marketing-kpi--purple"><span><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><div><small>Ventas con descuento</small><strong>—</strong><em>Impacto pendiente de integrar</em></div></article>
        </section>

        <form class="admin-marketing-filters" data-pending-form><label class="admin-marketing-search"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">Buscar cupones</span><input type="search" placeholder="Buscar código, promoción o cliente..." data-pending-search></label><label><span class="visually-hidden">Tipo</span><select data-pending-filter><option>Tipo: Todos</option><option>Porcentaje</option><option>Monto fijo</option></select></label><label><span class="visually-hidden">Estado</span><select data-pending-filter><option>Estado: Todos</option><option>Activo</option><option>Programado</option><option>Finalizado</option></select></label><label><span class="visually-hidden">Vigencia</span><select data-pending-filter><option>Vigencia: Este mes</option><option>Próximos 30 días</option><option>Histórico</option></select></label><button class="admin-marketing-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button><button class="admin-marketing-clear-button" type="button" data-pending-notice="Los filtros se limpiarán cuando el módulo tenga filtros conectados.">Limpiar filtros</button></form>

        <section class="admin-marketing-table-card">
            <header class="admin-marketing-card-header"><div><span class="admin-marketing-card-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><h2>Listado de cupones y promociones</h2><p>{{ number_format($coupons->total()) }} registros disponibles para administrar.</p></div></div><span class="admin-marketing-count">{{ number_format($coupons->total()) }} registros</span></header>
            <div class="admin-marketing-table-wrap"><table class="admin-marketing-table"><thead><tr><th>Código / Promoción</th><th>Descuento</th><th>Aplicación</th><th>Vigencia</th><th>Uso</th><th>Estado</th><th>Acciones</th></tr></thead><tbody>
                @forelse ($coupons as $coupon)
                    <tr><td><strong>{{ $coupon->codigo }}</strong><small>Promoción registrada</small></td><td><strong>{{ $coupon->tipo_descuento === 'porcentaje' ? number_format((float) $coupon->valor, 0).' %' : 'S/ '.number_format((float) $coupon->valor, 2) }}</strong><small>Compra mínima S/ {{ number_format((float) $coupon->minimo_compra, 2) }}</small></td><td>Todo el catálogo</td><td>{{ $coupon->inicia_en?->format('d/m/Y') }}<small>hasta {{ $coupon->finaliza_en?->format('d/m/Y') }}</small></td><td><div class="admin-marketing-usage"><span>{{ $coupon->usos }} / {{ $coupon->limite_usos ?? '∞' }}</span><i style="--usage: {{ $coupon->limite_usos ? min(100, ((int) $coupon->usos / max(1, (int) $coupon->limite_usos)) * 100) : 0 }}%"></i></div></td><td><span class="admin-marketing-status admin-marketing-status--{{ $coupon->activo ? 'active' : 'finished' }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $coupon->activo ? 'Activo' : 'Finalizado' }}</span></td><td class="admin-marketing-row-actions"><button type="button" data-pending-notice="La vista detallada se habilitará con el módulo de cupones completo."><i class="bi bi-eye" aria-hidden="true"></i><span>Ver</span></button><button type="button" data-pending-notice="La edición se habilitará cuando exista el endpoint de actualización."><i class="bi bi-pencil" aria-hidden="true"></i><span>Editar</span></button><button class="is-danger" type="button" data-pending-notice="La cancelación se habilitará con el flujo de desactivación."><i class="bi bi-trash3" aria-hidden="true"></i><span>Cancelar</span></button></td></tr>
                @empty
                    <tr><td colspan="7"><div class="admin-marketing-empty"><span><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><strong>No hay cupones para mostrar</strong><p>Crea el primer cupón para comenzar a controlar tus promociones.</p><button type="button" class="admin-marketing-empty-button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> Crear cupón</button></div></td></tr>
                @endforelse
            </tbody></table></div>
            <footer class="admin-marketing-table-footer"><span>Mostrando {{ $coupons->firstItem() ?? 0 }} a {{ $coupons->lastItem() ?? 0 }} de {{ number_format($coupons->total()) }} registros</span>{{ $coupons->links() }}</footer>
        </section>

        <section class="admin-marketing-bottom-grid"><article class="admin-marketing-summary-card"><header><div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--orange"><i class="bi bi-calendar-event" aria-hidden="true"></i></span><div><h2>Próximos vencimientos</h2><p>Cupones que requieren seguimiento.</p></div></div><button type="button" data-pending-notice="Los vencimientos se ordenarán cuando se conecten los filtros de vigencia.">Ver calendario <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="admin-marketing-summary-empty"><i class="bi bi-calendar-check" aria-hidden="true"></i><strong>Sin vencimientos destacados</strong><span>La información aparecerá con datos de vigencia.</span></div></article><article class="admin-marketing-summary-card"><header><div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--blue"><i class="bi bi-bar-chart" aria-hidden="true"></i></span><div><h2>Cupones más usados</h2><p>Rendimiento por código.</p></div></div><button type="button" data-pending-notice="El ranking se mostrará con los canjes reales.">Ver ranking <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="admin-marketing-summary-empty"><i class="bi bi-graph-up" aria-hidden="true"></i><strong>Sin canjes registrados</strong><span>El ranking aparecerá con las ventas conectadas.</span></div></article><article class="admin-marketing-summary-card"><header><div><span class="admin-marketing-summary-icon admin-marketing-summary-icon--green"><i class="bi bi-graph-up-arrow" aria-hidden="true"></i></span><div><h2>Impacto promocional</h2><p>Resultados comerciales.</p></div></div><button type="button" data-pending-notice="El impacto se calculará con ventas y canjes.">Ver métricas <i class="bi bi-arrow-right" aria-hidden="true"></i></button></header><div class="admin-marketing-impact"><strong>—</strong><span>Ventas atribuidas</span><div><i></i></div><small>Esperando datos comerciales.</small></div></article></section>

        <dialog class="admin-marketing-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="cupones-crear-title"><div class="admin-marketing-dialog-card"><header><div><span class="admin-marketing-dialog-icon"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span><div><small>Marketing</small><h2 id="cupones-crear-title">Nuevo cupón</h2><p>Configura un descuento para tus clientes.</p></div></div><button type="button" data-pending-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button></header><div class="admin-marketing-dialog-body">@include('gestion.cupones.crear')</div></div></dialog>
        <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
    </section>
    @endif
@endsection
