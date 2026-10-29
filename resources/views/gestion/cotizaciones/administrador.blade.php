@extends('layouts.administracion')

@section('title', 'Gestión de cotizaciones')
@section('body_class', 'modulo-cotizaciones')

@section('content')
    @php
        $statusClass = static fn (string $status): string => match ($status) {
            'Borrador' => 'draft',
            'Pendiente', 'Enviada' => 'pending',
            'Aprobada' => 'approved',
            'Convertida' => 'converted',
            'Rechazada', 'Vencida' => 'rejected',
            default => 'muted',
        };
    @endphp

    <section class="cotizaciones-page">
        <header class="cotizaciones-hero">
            <div>
                <nav class="cotizaciones-breadcrumb" aria-label="Ruta de navegación">
                    <a href="{{ route('management.dashboard') }}">Administración</a>
                    <span aria-hidden="true">/</span>
                    <span>Operaciones comerciales</span>
                    <span aria-hidden="true">/</span>
                    <span aria-current="page">Cotizaciones</span>
                </nav>
                <h1>Gestión de cotizaciones</h1>
                <p>Administra las propuestas comerciales y da seguimiento a cada cotización mayorista.</p>
            </div>
            <button class="cotizaciones-primary-button" type="button" data-admin-dialog-open="nueva-cotizacion" @disabled($customers->isEmpty() || $products->isEmpty())>
                <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva cotización
            </button>
        </header>

        <section class="cotizaciones-kpi-grid" aria-label="Resumen de cotizaciones">
            <article class="cotizaciones-kpi cotizaciones-kpi--blue">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span>
                <div><small>Cotizaciones generadas</small><strong>{{ number_format($totalQuotations) }}</strong><span>Registros activos</span></div>
            </article>
            <article class="cotizaciones-kpi cotizaciones-kpi--orange">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-clock-history" aria-hidden="true"></i></span>
                <div><small>Pendientes</small><strong>{{ number_format($pendingQuotations) }}</strong><span>Borradores y enviadas</span></div>
            </article>
            <article class="cotizaciones-kpi cotizaciones-kpi--green">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-check-circle" aria-hidden="true"></i></span>
                <div><small>Aprobadas</small><strong>{{ number_format($approvedQuotations) }}</strong><span>Propuestas aprobadas</span></div>
            </article>
            <article class="cotizaciones-kpi cotizaciones-kpi--purple">
                <span class="cotizaciones-kpi-icon"><i class="bi bi-cash-stack" aria-hidden="true"></i></span>
                <div><small>Monto cotizado</small><strong>S/ {{ number_format($quotedTotal, 2) }}</strong><span>Importe de cotizaciones activas</span></div>
            </article>
        </section>

        <form class="cotizaciones-filters" method="GET" action="{{ route('management.quotations') }}">
            <label class="cotizaciones-search">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input type="search" name="buscar" value="{{ $filters['buscar'] ?? '' }}" placeholder="Buscar cliente, código o producto..." aria-label="Buscar cotización, cliente o producto">
            </label>
            <label>
                <select name="cliente" aria-label="Cliente mayorista">
                    <option value="">Cliente: todos</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" @selected((string) ($filters['cliente'] ?? '') === (string) $customer->id)>{{ $customer->nombre_contacto }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <select name="estado" aria-label="Estado de cotización">
                    <option value="">Estado: todos</option>
                    @foreach ($statusSummary as $status)
                        <option value="{{ $status['label'] }}" @selected(($filters['estado'] ?? '') === $status['label'])>{{ $status['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="cotizaciones-date-filter">
                <input type="date" name="desde" value="{{ $filters['desde'] ?? '' }}" aria-label="Fecha desde">
            </label>
            <label class="cotizaciones-date-filter">
                <input type="date" name="hasta" value="{{ $filters['hasta'] ?? '' }}" aria-label="Fecha hasta">
            </label>
            <button class="cotizaciones-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
            <a class="cotizaciones-clear-button" href="{{ route('management.quotations') }}">Limpiar</a>
        </form>

        <section class="cotizaciones-table-card" aria-labelledby="cotizaciones-list-title">
            <header class="cotizaciones-table-header">
                <div><h2 id="cotizaciones-list-title">Listado de cotizaciones</h2><span>{{ number_format($quotations->total()) }} registros</span></div>
            </header>
            <div class="cotizaciones-table-scroll">
                <table class="cotizaciones-table">
                    <thead>
                        <tr>
                            <th scope="col">Cotización</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Emisión</th>
                            <th scope="col">Productos</th>
                            <th scope="col">Total</th>
                            <th scope="col">Estado</th>
                            <th scope="col"><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($quotations as $quotation)
                            @php
                                $status = (string) $quotation->estado;
                                $customerName = $quotation->customer?->nombre_contacto ?? 'Cliente no disponible';
                            @endphp
                            <tr>
                                <td><strong class="cotizaciones-code">COT-{{ $quotation->creado_en?->format('Y') ?? now()->format('Y') }}-{{ str_pad((string) $quotation->id, 4, '0', STR_PAD_LEFT) }}</strong></td>
                                <td><span class="cotizaciones-customer">{{ $customerName }}</span><small>{{ $quotation->customer?->documento ?? '—' }}</small></td>
                                <td><span class="cotizaciones-type">Mayorista</span></td>
                                <td>{{ $quotation->creado_en?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $quotation->details_count }}</td>
                                <td><strong>S/ {{ number_format((float) $quotation->total, 2) }}</strong></td>
                                <td><span class="cotizaciones-status cotizaciones-status--{{ $statusClass($status) }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $status }}</span></td>
                                <td>
                                    <details class="cotizaciones-row-details">
                                        <summary aria-label="Ver detalle de cotización {{ $quotation->id }}" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></summary>
                                        <div class="cotizaciones-detail-popover">
                                            <strong>Detalle de la cotización</strong>
                                            <span>{{ $customerName }} · {{ $quotation->creado_en?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}</span>
                                            <ul>
                                                @foreach ($quotation->details as $detail)
                                                    <li><span>{{ $detail->product?->nombre ?? 'Producto no disponible' }} × {{ $detail->cantidad }}</span><b>S/ {{ number_format((float) $detail->precio_unitario * (int) $detail->cantidad, 2) }}</b></li>
                                                @endforeach
                                            </ul>
                                            @if ($quotation->notas)
                                                <p>{{ $quotation->notas }}</p>
                                            @endif
                                        </div>
                                    </details>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="cotizaciones-empty" colspan="8"><i class="bi bi-inbox" aria-hidden="true"></i><strong>No hay cotizaciones para mostrar</strong><span>Prueba cambiando los filtros o crea una nueva cotización.</span></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <footer class="cotizaciones-table-footer">
                <span>
                    @if ($quotations->total() > 0)
                        Mostrando {{ $quotations->firstItem() }}–{{ $quotations->lastItem() }} de {{ $quotations->total() }} cotizaciones
                    @else
                        Sin registros
                    @endif
                </span>
                {{ $quotations->links() }}
            </footer>
        </section>

        <div class="cotizaciones-bottom-grid">
            <section class="cotizaciones-summary-card">
                <header><span class="cotizaciones-summary-icon"><i class="bi bi-activity" aria-hidden="true"></i></span><div><h2>Actividad comercial reciente</h2><p>Últimas cotizaciones registradas</p></div></header>
                @forelse ($recentQuotations as $recent)
                    <div class="cotizaciones-recent-row">
                        <span class="cotizaciones-recent-dot"></span>
                        <div><strong>COT-{{ $recent->creado_en?->format('Y') ?? now()->format('Y') }}-{{ str_pad((string) $recent->id, 4, '0', STR_PAD_LEFT) }} · {{ $recent->customer?->nombre_contacto ?? 'Cliente no disponible' }}</strong><small>{{ $recent->creado_en?->format('d/m/Y') ?? 'Fecha no disponible' }} · S/ {{ number_format((float) $recent->total, 2) }}</small></div>
                        <span class="cotizaciones-status cotizaciones-status--{{ $statusClass((string) $recent->estado) }}">{{ $recent->estado }}</span>
                    </div>
                @empty
                    <p class="cotizaciones-summary-empty">Las cotizaciones recientes aparecerán aquí.</p>
                @endforelse
            </section>

            <section class="cotizaciones-summary-card">
                <header><span class="cotizaciones-summary-icon cotizaciones-summary-icon--green"><i class="bi bi-pie-chart" aria-hidden="true"></i></span><div><h2>Cotizaciones por estado</h2><p>Distribución de los registros activos</p></div></header>
                @forelse ($statusSummary as $status)
                    <div class="cotizaciones-status-row">
                        <div><span>{{ $status['label'] }}</span><strong>{{ number_format($status['count']) }}</strong></div>
                        <div class="cotizaciones-progress" role="progressbar" aria-label="{{ $status['label'] }}" aria-valuenow="{{ $status['percent'] }}" aria-valuemin="0" aria-valuemax="100"><span style="width: {{ $status['percent'] }}%"></span></div>
                    </div>
                @empty
                    <p class="cotizaciones-summary-empty">Aún no hay cotizaciones registradas.</p>
                @endforelse
            </section>
        </div>

        <dialog class="admin-dialog cotizaciones-create-dialog" id="nueva-cotizacion" aria-labelledby="nueva-cotizacion-title">
            <header class="admin-dialog-header">
                <div><h2 id="nueva-cotizacion-title">Nueva cotización</h2><p>Registra una propuesta para un cliente mayorista.</p></div>
                <button class="admin-dialog-close" type="button" data-admin-dialog-close aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-dialog-body">
                @if ($customers->isNotEmpty() && $products->isNotEmpty())
                    @include('gestion.cotizaciones.crear', ['customers' => $customers, 'products' => $products])
                @else
                    <p class="cotizaciones-summary-empty">Para crear una cotización, primero registra un cliente mayorista y al menos un producto activo.</p>
                @endif
            </div>
        </dialog>
    </section>
@endsection
