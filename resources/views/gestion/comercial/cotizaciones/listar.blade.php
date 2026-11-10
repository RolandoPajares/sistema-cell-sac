@extends('layouts.panel.principal')

@section('title', 'Gestión de cotizaciones')
@section('body_class', 'modulo-cotizaciones')

@section('content')
@if (isset($totalQuotations))
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
            <div class="cotizaciones-hero-actions">
                <details class="cotizaciones-export-menu">
                    <summary class="cotizaciones-filter-button"><i class="bi bi-download" aria-hidden="true"></i> Exportar</summary>
                    <div class="cotizaciones-export-options">
                        <a href="{{ route('management.quotations.export.list', ['format' => 'pdf'] + request()->query()) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a>
                        <a href="{{ route('management.quotations.export.list', ['format' => 'excel'] + request()->query()) }}"><i class="bi bi-file-earmark-spreadsheet" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a>
                        <a href="{{ route('management.quotations.export.list', ['format' => 'csv'] + request()->query()) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a>
                    </div>
                </details>
                <button class="cotizaciones-primary-button" type="button" data-admin-dialog-open="nueva-cotizacion">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i> Nueva cotización
                </button>
            </div>
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
                            <tr>
                                <td><strong class="cotizaciones-code">{{ $quotationPresentation[$quotation->id]['reference'] }}</strong></td>
                                <td><span class="cotizaciones-customer">{{ $quotationPresentation[$quotation->id]['customer_name'] }}</span><small>{{ $quotation->customer?->documento ?? '—' }}</small></td>
                                <td><span class="cotizaciones-type">Mayorista</span></td>
                                <td>{{ $quotation->creado_en?->format('d/m/Y') ?? '—' }}</td>
                                <td>{{ $quotation->details_count }}</td>
                                <td><strong>S/ {{ number_format((float) $quotation->total, 2) }}</strong></td>
                                <td><span class="cotizaciones-status cotizaciones-status--{{ $quotationPresentation[$quotation->id]['status_class'] }}"><i class="bi bi-circle-fill" aria-hidden="true"></i>{{ $quotationPresentation[$quotation->id]['status'] }}</span></td>
                                <td>
                                    <details class="cotizaciones-row-details">
                                        <summary aria-label="Ver detalle de cotización {{ $quotation->id }}" title="Ver detalle"><i class="bi bi-eye" aria-hidden="true"></i></summary>
                                        <div class="cotizaciones-detail-popover">
                                            <strong>Detalle de la cotización</strong>
                                            <span>{{ $quotationPresentation[$quotation->id]['customer_name'] }} · {{ $quotation->creado_en?->format('d/m/Y H:i') ?? 'Fecha no disponible' }}</span>
                                            <ul>
                                                @foreach ($quotation->details as $detail)
                                                    <li><span>{{ $detail->product?->nombre ?? 'Producto no disponible' }} × {{ $detail->cantidad }}</span><b>S/ {{ number_format((float) $detail->precio_unitario * (int) $detail->cantidad, 2) }}</b></li>
                                                @endforeach
                                            </ul>
                                            @if ($quotation->notas)
                                                <p>{{ $quotation->notas }}</p>
                                            @endif
                                            @if (in_array($quotationPresentation[$quotation->id]['status'], ['Borrador', 'Pendiente'], true) && $puedeEnviar)
                                                <details class="cotizaciones-edit-details">
                                                    <summary>Editar cotización</summary>
                                                    <form method="POST" action="{{ route('management.quotations.update', $quotation) }}" class="cotizaciones-edit-form">
                                                        @csrf
                                                        @method('PUT')
                                                        <label>Cliente
                                                            <select name="cliente_id" required>
                                                                @foreach ($customers as $customer)
                                                                    <option value="{{ $customer->id }}" @selected((int) $customer->id === (int) $quotation->cliente_id)>{{ $customer->nombre_contacto }} · {{ $customer->documento }}</option>
                                                                @endforeach
                                                            </select>
                                                        </label>
                                                        @foreach ($quotation->details as $index => $detail)
                                                            <label>Producto {{ $index + 1 }}
                                                                <select name="items[{{ $index }}][producto_id]" required>
                                                                    @foreach ($products as $product)
                                                                        <option value="{{ $product->id }}" @selected((int) $product->id === (int) $detail->producto_id)>{{ $product->nombre }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </label>
                                                            <label>Cantidad
                                                                <input type="number" name="items[{{ $index }}][cantidad]" min="1" max="100000" value="{{ $detail->cantidad }}" required>
                                                            </label>
                                                        @endforeach
                                                        <label>Notas<textarea name="notas" maxlength="500" rows="2">{{ $quotation->notas }}</textarea></label>
                                                        <button type="submit">Guardar cambios</button>
                                                    </form>
                                                </details>
                                            @endif
                                            <div class="cotizaciones-demo-actions" aria-label="Acciones de cotizacion">
    @if (in_array($quotationPresentation[$quotation->id]['status'], ['Borrador', 'Pendiente'], true))
        @if ($puedeEnviar && $puedeDescargar && filled($quotation->customer?->correo))
            <button type="button" data-quotation-action="enviar" data-reference="{{ $quotationPresentation[$quotation->id]['reference'] }}" data-customer="{{ $quotationPresentation[$quotation->id]['customer_name'] }}" data-recipient="{{ $quotation->customer->correo }}" data-download="{{ route('management.quotations.export', $quotation) }}" data-send="{{ route('management.quotations.update.send', $quotation) }}">Enviar cotizacion</button>
        @else
            <button type="button" disabled title="Se requieren permisos de actualizacion y exportacion, y un correo valido del cliente">Enviar cotizacion</button>
        @endif
    @endif
    @if (in_array($quotation->id, $aprobables, true))
        <form method="POST" action="{{ route('management.quotations.approve', $quotation) }}" style="display: contents">
            @csrf
            <button type="submit">Aprobar cotizacion</button>
        </form>
    @endif
    @if ($puedeEnviar && in_array($quotationPresentation[$quotation->id]['status'], ['Borrador', 'Pendiente'], true))
        <form method="POST" action="{{ route('management.quotations.deactivate', $quotation) }}" style="display: contents" data-confirm="¿Desactivar esta cotización? Se conservará el historial.">
            @csrf
            @method('DELETE')
            <button type="submit">Desactivar cotizacion</button>
        </form>
    @endif
    @if ($quotationPresentation[$quotation->id]['status'] === 'Aprobada')
        @if ($puedeCrearVenta)
            <button type="button" data-quotation-action="convertir" data-reference="{{ $quotationPresentation[$quotation->id]['reference'] }}" data-customer="{{ $quotationPresentation[$quotation->id]['customer_name'] }}" data-convert="{{ route('management.sales.convert_quotation', $quotation) }}" data-site-id="{{ $quotation->sede_id }}">Convertir en venta</button>
        @else
            <button type="button" disabled title="Se requiere el permiso de creacion de ventas">Convertir en venta</button>
        @endif
    @endif
</div>
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
                        <span class="cotizaciones-status cotizaciones-status--{{ $quotationPresentation[$recent->id]['status_class'] }}">{{ $recent->estado }}</span>
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
                    @include('gestion.comercial.cotizaciones.registrar', ['customers' => $customers, 'products' => $products])
                @else
                    <p class="cotizaciones-summary-empty">Para guardar una cotización, primero registra un cliente mayorista y al menos un producto activo. Los datos se cargarán desde esta base de datos.</p>
                @endif
            </div>
        </dialog>
        <dialog class="admin-dialog cotizaciones-demo-dialog" id="accion-demostrativa-cotizacion" aria-labelledby="accion-cotizacion-title" data-quotation-dialog>
            <header class="admin-dialog-header">
                <div><h2 id="accion-cotizacion-title" data-quotation-title>Accion de cotizacion</h2><p data-quotation-description>Revisa la accion seleccionada.</p></div>
                <button class="admin-dialog-close" type="button" data-close-quotation-dialog aria-label="Cerrar"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
            </header>
            <div class="admin-dialog-body cotizaciones-demo-body">
                <div class="cotizaciones-demo-warning"><i class="bi bi-info-circle" aria-hidden="true"></i><p data-quotation-message></p></div>
                <dl><div><dt>Cotizacion</dt><dd data-quotation-reference>—</dd></div><div><dt>Cliente</dt><dd data-quotation-customer>—</dd></div></dl>
                <div data-manual-send hidden>
                    <a class="cotizaciones-filter-button" data-quotation-download href="#" download>Descargar PDF</a>
                    <a class="cotizaciones-filter-button" data-quotation-mailto href="#">Abrir correo para envio manual</a>
                    <form id="confirmar-envio-cotizacion" method="POST" action="#" data-manual-send-form>
                        @csrf
                        <label><input type="checkbox" name="confirmar_envio_manual" value="1" required> Confirmo que envie manualmente el PDF al destinatario indicado.</label>
                    </form>
                </div>
                <form id="convertir-cotizacion" method="POST" action="#" data-conversion-form hidden>
                    @csrf
                    <label for="almacen-conversion-cotizacion">Almacén autorizado para la venta</label>
                    <select id="almacen-conversion-cotizacion" name="almacen_id" required>
                        <option value="">Seleccionar almacén...</option>
                        @foreach ($almacenesVenta as $almacen)
                            <option value="{{ $almacen->id }}" data-site-id="{{ $almacen->sede_id }}">{{ $almacen->sede_nombre }} · {{ $almacen->nombre }}</option>
                        @endforeach
                    </select>
                </form>
                <p class="cotizaciones-demo-feedback" data-quotation-blocked role="status" aria-live="polite" hidden></p>
            </div>
            <footer class="cotizaciones-demo-footer">
                <button class="cotizaciones-filter-button" type="button" data-close-quotation-dialog>Cerrar</button>
                <button class="cotizaciones-primary-button" type="submit" form="confirmar-envio-cotizacion" data-record-manual-send hidden>Registrar envio manual</button>
                <button class="cotizaciones-primary-button" type="submit" form="convertir-cotizacion" data-confirm-conversion hidden>Crear venta en borrador</button>
            </footer>
        </dialog>
    </section>
@else

{{-- Listado de cotizaciones del cliente autenticado. --}}
<section class="container content-page">
    <h1>Cotizaciones</h1>
    @include('gestion.comercial.cotizaciones.registrar', ['customers' => $customers, 'products' => $products])
    @forelse ($quotations as $quotation)
        <article class="data-row">
            <div>
                <strong>Cotizaci�n #{{ $quotation->id }} � {{ $quotation->customer->nombre_contacto }}</strong>
                <small>{{ $quotation->estado }} � {{ $quotation->creado_en }}</small>
            </div>
            <strong>S/ {{ number_format((float) $quotation->total, 2) }}</strong>
        </article>
    @empty
        <p>No tienes cotizaciones registradas.</p>
    @endforelse
    {{ $quotations->links() }}
</section>
@endif
@endsection


