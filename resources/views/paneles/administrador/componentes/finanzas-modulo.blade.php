@php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';

    $financeContent = [
        'pagos' => [
            'title' => 'Gestión de pagos',
            'description' => 'Supervisa cobros, confirma transacciones y controla la conciliación financiera.',
            'action' => 'Registrar pago',
            'secondary' => 'Conciliar pagos',
            'tableTitle' => 'Registro de pagos',
            'summary' => [
                ['title' => 'Pagos por conciliar', 'icon' => 'bi-hourglass-split', 'tone' => 'orange', 'text' => 'Sin movimientos por verificar.'],
                ['title' => 'Medios de pago', 'icon' => 'bi-bar-chart-line', 'tone' => 'blue', 'text' => 'La distribución aparecerá con pagos reales.'],
                ['title' => 'Actividad financiera reciente', 'icon' => 'bi-activity', 'tone' => 'green', 'text' => 'No hay actividad integrada todavía.'],
            ],
        ],
        'facturacion' => [
            'title' => 'Gestión de facturación',
            'description' => 'Controla comprobantes, revisa emisión y organiza documentos de venta.',
            'action' => 'Emitir comprobante',
            'secondary' => 'Configurar series',
            'tableTitle' => 'Comprobantes de venta',
            'summary' => [
                ['title' => 'Pendientes por gestionar', 'icon' => 'bi-hourglass-split', 'tone' => 'orange', 'text' => 'No hay comprobantes pendientes.'],
                ['title' => 'Resumen de facturación', 'icon' => 'bi-receipt', 'tone' => 'blue', 'text' => 'El resumen aparecerá con ventas emitidas.'],
                ['title' => 'Actividad reciente', 'icon' => 'bi-clock-history', 'tone' => 'green', 'text' => 'No hay emisiones registradas.'],
            ],
        ],
        'reembolsos' => [
            'title' => 'Gestión de reembolsos',
            'description' => 'Evalúa solicitudes, autoriza devoluciones y realiza seguimiento de importes y plazos.',
            'action' => 'Nueva solicitud',
            'secondary' => 'Revisar pendientes',
            'tableTitle' => 'Solicitudes de reembolso',
            'summary' => [
                ['title' => 'Casos prioritarios', 'icon' => 'bi-exclamation-triangle', 'tone' => 'orange', 'text' => 'No hay solicitudes prioritarias.'],
                ['title' => 'Seguimiento de reembolsos', 'icon' => 'bi-clock', 'tone' => 'blue', 'text' => 'El tiempo promedio aparecerá con datos reales.'],
                ['title' => 'Actividad reciente', 'icon' => 'bi-activity', 'tone' => 'green', 'text' => 'No hay reembolsos procesados.'],
            ],
        ],
    ][$moduleKey] ?? [
        'title' => $module['title'],
        'description' => $module['description'],
        'action' => 'Nuevo registro',
        'secondary' => 'Ver resumen',
        'tableTitle' => 'Listado de ' . $recordPlural,
        'summary' => [],
    ];
@endphp

<section class="admin-finance-module admin-finance-module--{{ $moduleKey }}" data-pending-module="{{ $moduleKey }}" data-finance-module="{{ $moduleKey }}">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>Finanzas</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $module['title'] }}</strong>
        </nav>

        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> Finanzas</span>
                <h1>{{ $financeContent['title'] }}</h1>
                <p>{{ $financeContent['description'] }}</p>
            </div>
            <div class="admin-finance-actions">
                <button class="admin-finance-button admin-finance-button--secondary" type="button" disabled title="Exportación no disponible en esta pantalla"><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
                <button class="admin-finance-button admin-finance-button--secondary" type="button" disabled title="Operación no disponible en esta pantalla"><i class="bi bi-arrow-repeat" aria-hidden="true"></i> {{ $financeContent['secondary'] }}</button>
                @if ($isCrud)
                    <button class="admin-finance-button admin-finance-button--primary" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $financeContent['action'] }}</button>
                @endif
            </div>
        </div>
    </header>

    <section class="admin-finance-kpis" aria-label="Indicadores de {{ $financeContent['title'] }}">
        @foreach ($kpis as $kpi)
            <article class="admin-finance-kpi admin-finance-kpi--{{ $kpi['tone'] ?? 'blue' }}">
                <span class="admin-finance-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div class="admin-finance-kpi-copy">
                    <small>{{ $kpi['label'] }}</small>
                    <strong>{{ $kpi['value'] ?? '—' }}</strong>
                    <span>{{ $kpi['helper'] ?? 'Sin datos integrados' }}</span>
                </div>
                <i class="admin-finance-kpi-spark" aria-hidden="true"></i>
            </article>
        @endforeach
    </section>

    <form class="admin-finance-filters" method="GET" action="{{ url()->current() }}">
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar {{ $recordPlural }}</span>
            <input type="search" name="buscar" value="{{ $module['filters']['buscar'] ?? request('buscar') }}" placeholder="Buscar {{ $recordSingular }}, cliente o referencia...">
        </label>
        <label><span class="visually-hidden">Tipo</span><select name="tipo"><option value="">Tipo: Todos</option>@foreach (($module['filter_options']['types'] ?? []) as $codigo => $nombre)<option value="{{ $codigo }}" @selected(($module['filters']['tipo'] ?? request('tipo')) === $codigo)>{{ $nombre }}</option>@endforeach</select></label>
        <label><span class="visually-hidden">Estado</span><select name="estado"><option value="">Estado: Todos</option>@foreach (($module['filter_options']['statuses'] ?? []) as $estado)<option value="{{ $estado }}" @selected(($module['filters']['estado'] ?? request('estado')) === $estado)>{{ $estado }}</option>@endforeach</select></label>
        <label><span class="visually-hidden">Periodo</span><select name="periodo"><option value="">Fecha: Todo el periodo</option><option value="mes" @selected(($module['filters']['periodo'] ?? request('periodo')) === 'mes')>Este mes</option><option value="ultimos_30_dias" @selected(($module['filters']['periodo'] ?? request('periodo')) === 'ultimos_30_dias')>Últimos 30 días</option><option value="anio" @selected(($module['filters']['periodo'] ?? request('periodo')) === 'anio')>Este año</option></select></label>
        <button class="admin-finance-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="admin-finance-clear-button" href="{{ url()->current() }}">Limpiar filtros</a>
    </form>
    <span class="admin-finance-filter-status" data-finance-filter-status role="status" aria-live="polite"></span>

    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header">
            <div>
                <span class="admin-finance-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <h2>{{ $financeContent['tableTitle'] }}</h2>
                    <p>{{ $module['description'] }}</p>
                </div>
            </div>
            <span class="admin-finance-count">{{ number_format($module['total_count'] ?? 0) }} registros</span>
        </header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead>
                    <tr>
                        @foreach ($columns as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                        @if ($isCrud)
                            <th>Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @if (! empty($module['records']))
                        @foreach ($module['records'] as $registro)
                            <tr>@foreach ($registro['cells'] as $celda)<td>{{ $celda }}</td>@endforeach</tr>
                        @endforeach
                    @else
                    <tr>
                        <td colspan="{{ max(1, count($columns) + ($isCrud ? 1 : 0)) }}">
                            <div class="admin-finance-empty">
                                <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                <strong>No hay {{ $recordPlural }} para mostrar</strong>
                                <p>La consulta a MariaDB no encontró registros para mostrar.</p>
                                @if ($isCrud)
                                    <button class="admin-finance-empty-button" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $financeContent['action'] }}</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>Mostrando {{ number_format(($module['pagination'] ?? null)?->firstItem() ?? 0) }}–{{ number_format(($module['pagination'] ?? null)?->lastItem() ?? 0) }} de {{ number_format($module['total_count'] ?? 0) }}</span>
            <nav class="admin-finance-pagination" aria-label="Paginación">
                @if (($module['pagination'] ?? null)?->previousPageUrl())<a href="{{ $module['pagination']->previousPageUrl() }}" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>@else<button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>@endif
                <button type="button" class="is-active" disabled>{{ ($module['pagination'] ?? null)?->currentPage() ?? 1 }}</button>
                @if (($module['pagination'] ?? null)?->nextPageUrl())<a href="{{ $module['pagination']->nextPageUrl() }}" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>@else<button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>@endif
            </nav>
        </footer>
    </section>

    <section class="admin-finance-summary-grid" aria-label="Resumen de {{ $financeContent['title'] }}">
        @foreach (($module['summary'] ?? $financeContent['summary']) as $summary)
            <article class="admin-finance-summary-card">
                <header>
                    <div><span class="admin-finance-summary-icon admin-finance-summary-icon--{{ $summary['tone'] }}"><i class="bi {{ $summary['icon'] }}" aria-hidden="true"></i></span><h2>{{ $summary['title'] }}</h2></div>
                    <button type="button" aria-label="Ver resumen: {{ $summary['title'] }}" disabled title="Solo consulta de resumen"><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </header>
                <div class="admin-finance-summary-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>{{ $summary['text'] }}</strong><span>{{ $module['summary'] ?? false ? 'Datos consultados en MariaDB' : 'Sin datos integrados' }}</span></div>
            </article>
        @endforeach
    </section>

    @if ($isCrud)
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $moduleKey }}-crear-title">
            @include('paneles.administrador.finanzas.' . $moduleKey . '.crear', ['module' => $module])
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $moduleKey }}-editar-title">
            @include('paneles.administrador.finanzas.' . $moduleKey . '.editar', ['module' => $module])
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $moduleKey }}-ver-title">
            @include('paneles.administrador.finanzas.' . $moduleKey . '.ver', ['module' => $module])
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $moduleKey }}-eliminar-title">
            @include('paneles.administrador.finanzas.' . $moduleKey . '.eliminar', ['module' => $module])
        </dialog>
    @endif

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>
