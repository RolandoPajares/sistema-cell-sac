@extends('layouts.panel.principal')

@section('title', 'Reembolsos')
@section('body_class', 'modulo-reembolsos')

@section('content')
@php
    $module = $module ?? require config_path('gestion/finanzas/reembolsos.php');
@endphp

@if ($module['connected'] ?? false)
    @php
    $moduleKey = $module['key'];
    $filterOptions = $module['filter_options'] ?? ['types' => [], 'statuses' => []];
    $filterValues = $module['filters'] ?? [];
    $exportParameters = array_filter($filterValues, static fn ($value) => $value !== null && $value !== '');
    $formOptions = $module['form_options'] ?? [];
    $canSubmit = $moduleKey === 'pagos'
        ? ($formOptions['documentos'] ?? collect())->isNotEmpty() && ($formOptions['medios'] ?? collect())->isNotEmpty()
        : ($formOptions['devoluciones'] ?? collect())->isNotEmpty() && ($formOptions['cobros'] ?? collect())->isNotEmpty();
@endphp

<section class="admin-finance-module admin-finance-module--{{ $moduleKey }}" data-finance-module="{{ $moduleKey }}" data-finance-reopen="{{ old('_form') === 'crear' && $errors->any() ? 'crear' : '' }}">
    <header class="admin-finance-header">
        <nav class="admin-finance-breadcrumb" aria-label="Ruta de navegación">
            <span>Administración</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <span>{{ $module['area'] }}</span>
            <i class="bi bi-chevron-right" aria-hidden="true"></i>
            <strong>{{ $module['title'] }}</strong>
        </nav>

        <div class="admin-finance-heading-row">
            <div class="admin-finance-heading">
                <span class="admin-finance-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i> {{ $module['area'] }}</span>
                <h1>{{ $module['title'] }}</h1>
                <p>{{ $module['description'] }}</p>
            </div>
            <div class="admin-finance-actions">
                @if ($module['export_route'] ?? false)
                    <details class="admin-finance-export-menu">
                        <summary class="admin-finance-button admin-finance-button--secondary"><i class="bi bi-download" aria-hidden="true"></i><span>{{ $module['export_label'] ?? 'Exportar' }}</span><i class="bi bi-chevron-down admin-finance-export-chevron" aria-hidden="true"></i></summary>
                        <div class="admin-finance-export-options" role="menu">
                            <a role="menuitem" href="{{ route($module['export_route'], ['format' => 'excel'] + $exportParameters) }}"><i class="bi bi-file-earmark-excel" aria-hidden="true"></i><span><strong>Exportar Excel</strong><small>Archivo compatible .xlsx</small></span></a>
                            <a role="menuitem" href="{{ route($module['export_route'], ['format' => 'pdf'] + $exportParameters) }}"><i class="bi bi-file-earmark-pdf" aria-hidden="true"></i><span><strong>Exportar PDF</strong><small>Documento imprimible</small></span></a>
                            <a role="menuitem" href="{{ route($module['export_route'], ['format' => 'csv'] + $exportParameters) }}"><i class="bi bi-filetype-csv" aria-hidden="true"></i><span><strong>Exportar CSV</strong><small>Datos delimitados</small></span></a>
                        </div>
                    </details>
                @endif
                @if ($module['create_route'] ?? false)
                    <button class="admin-finance-button admin-finance-button--primary" type="button" data-finance-open="crear">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $moduleKey === 'pagos' ? 'Registrar solicitud' : 'Nueva solicitud' }}
                    </button>
                @endif
            </div>
        </div>
    </header>
    @if (session('status'))
        <p class="alert alert-success" role="status">{{ session('status') }}</p>
    @endif

    <section class="admin-finance-kpis" aria-label="Indicadores de {{ $module['title'] }}">
        @foreach ($module['kpis'] as $kpi)
            <article class="admin-finance-kpi admin-finance-kpi--{{ $kpi['tone'] ?? 'blue' }}">
                <span class="admin-finance-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div class="admin-finance-kpi-copy">
                    <small>{{ $kpi['label'] }}</small>
                    <strong>{{ $kpi['value'] }}</strong>
                    <span>{{ $kpi['helper'] }}</span>
                </div>
                <i class="admin-finance-kpi-spark" aria-hidden="true"></i>
            </article>
        @endforeach
    </section>

    <form class="admin-finance-filters" method="GET" action="{{ route($module['route']) }}">
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar {{ $module['record_plural'] }}</span>
            <input type="search" name="buscar" value="{{ $filterValues['buscar'] ?? '' }}" maxlength="120" placeholder="Buscar por código, referencia o documento...">
        </label>
        @if (($filterOptions['types'] ?? []) !== [])
            <label>
                <span class="visually-hidden">Tipo de operación</span>
                <select name="tipo">
                    <option value="">Tipo: todos</option>
                    @foreach ($filterOptions['types'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filterValues['tipo'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
        @endif
        <label>
            <span class="visually-hidden">Estado</span>
            <select name="estado">
                <option value="">Estado: todos</option>
                @foreach ($filterOptions['statuses'] as $status)
                    <option value="{{ $status }}" @selected(($filterValues['estado'] ?? '') === $status)>{{ $status }}</option>
                @endforeach
            </select>
        </label>
        <label>
            <span class="visually-hidden">Fecha inicial</span>
            <input type="date" name="desde" value="{{ $filterValues['desde'] ?? '' }}" aria-label="Fecha inicial">
        </label>
        <label>
            <span class="visually-hidden">Fecha final</span>
            <input type="date" name="hasta" value="{{ $filterValues['hasta'] ?? '' }}" aria-label="Fecha final">
        </label>
        <button class="admin-finance-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <a class="admin-finance-clear-button" href="{{ route($module['route']) }}">Limpiar filtros</a>
    </form>
    @foreach ($errors->all() as $error)
        <p class="text-danger" role="alert">{{ $error }}</p>
    @endforeach

    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header">
            <div>
                <span class="admin-finance-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <h2>{{ $module['table_title'] }}</h2>
                    <p>Registros consultados desde las tablas financieras y el alcance autorizado.</p>
                </div>
            </div>
            <span class="admin-finance-count">{{ number_format($module['total_count']) }} registros</span>
        </header>
        <div class="admin-finance-table-wrap">
            <table class="admin-finance-table">
                <thead>
                    <tr>
                        @foreach ($module['columns'] as $column)
                            <th>{{ $column }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($module['records'] as $record)
                        <tr>
                            @foreach ($record['cells'] as $cellIndex => $cell)
                                <td>
                                    @if ($cellIndex === 0 && isset($record['detail_url']))
                                        <a href="{{ $record['detail_url'] }}">{{ $cell }}</a>
                                    @else
                                        {{ $cell }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($module['columns']) }}">
                                <div class="admin-finance-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay {{ $module['record_plural'] }} para mostrar</strong>
                                    <p>No existen registros que coincidan con los filtros y el alcance autorizados.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>
                @if ($module['total_count'] > 0)
                    Mostrando {{ $module['pagination']->firstItem() }}–{{ $module['pagination']->lastItem() }} de {{ number_format($module['total_count']) }}
                @else
                    Sin resultados para mostrar
                @endif
            </span>
            @if ($module['pagination']->hasPages())
                <nav class="admin-finance-pagination" aria-label="Paginación">
                    @if ($module['pagination']->previousPageUrl())
                        <a href="{{ $module['pagination']->previousPageUrl() }}" rel="prev" aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                    @else
                        <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    @endif
                    <span class="is-active" aria-current="page">{{ $module['pagination']->currentPage() }} / {{ $module['pagination']->lastPage() }}</span>
                    @if ($module['pagination']->nextPageUrl())
                        <a href="{{ $module['pagination']->nextPageUrl() }}" rel="next" aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                    @else
                        <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    @endif
                </nav>
            @endif
        </footer>
    </section>

    @if ($module['summary'] !== [])
        <section class="admin-finance-summary-grid" aria-label="Resumen por estado de {{ $module['title'] }}">
            @foreach ($module['summary'] as $summary)
                <article class="admin-finance-summary-card">
                    <header>
                        <div><span class="admin-finance-summary-icon admin-finance-summary-icon--{{ $summary['tone'] }}"><i class="bi {{ $summary['icon'] }}" aria-hidden="true"></i></span><h2>{{ $summary['title'] }}</h2></div>
                    </header>
                    <div class="admin-finance-summary-empty"><i class="bi bi-list-check" aria-hidden="true"></i><strong>{{ $summary['text'] }}</strong><span>Estado leído del registro</span></div>
                </article>
            @endforeach
        </section>
    @endif

    @if ($module['create_route'] ?? false)
        <dialog class="admin-finance-dialog" data-finance-dialog="crear" aria-labelledby="{{ $moduleKey }}-crear-title">
            <form method="POST" action="{{ route($module['create_route']) }}" class="gestion-pendiente-modal-shell tc-finance-demo">
                @csrf
                <input type="hidden" name="_form" value="crear">
                @if ($moduleKey === 'pagos')
                    <input type="hidden" name="token" value="{{ old('token', $module['request_token']) }}">
                @endif
                <header class="gestion-pendiente-modal-header">
                    <div>
                        <span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                        <div>
                            <span class="gestion-pendiente-eyebrow">Finanzas · {{ $module['title'] }}</span>
                            <h2 id="{{ $moduleKey }}-crear-title">{{ $moduleKey === 'pagos' ? 'Registrar pago o cobro pendiente' : 'Solicitud de reembolso' }}</h2>
                            <p>{{ $moduleKey === 'pagos' ? 'El documento y el medio se consultan en la sede activa.' : 'La aprobación y el desembolso son etapas separadas.' }}</p>
                        </div>
                    </div>
                    <button type="button" class="gestion-pendiente-close" data-finance-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
                </header>
                <div class="gestion-pendiente-modal-body tc-finance-demo__body">
                    @if ($moduleKey === 'pagos')
                        <div class="tc-finance-form-grid">
                            <label class="tc-finance-field">Pedido, venta o compra *
                                <select name="origen" required>
                                    <option value="">Selecciona un documento</option>
                                    @foreach ($formOptions['documentos'] as $document)
                                        <option value="{{ $document['value'] }}" @selected(old('origen') === $document['value'])>{{ $document['label'] }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="tc-finance-field">Medio de pago *
                                <select name="medio_pago_id" required data-finance-method>
                                    <option value="">Selecciona un medio</option>
                                    @foreach ($formOptions['medios'] as $method)
                                        <option value="{{ $method->id }}" data-requires-cash="{{ $method->requiere_caja ? '1' : '0' }}" data-requires-bank="{{ $method->requiere_banco ? '1' : '0' }}" @selected((string) old('medio_pago_id') === (string) $method->id) @disabled(($method->requiere_caja && $formOptions['sesiones']->isEmpty()) || ($method->requiere_banco && $formOptions['cuentas']->isEmpty()))>{{ $method->nombre }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="tc-finance-field" data-finance-cash-field hidden>Sesión de caja *
                                <select name="sesion_caja_id">
                                    <option value="">Selecciona una sesión abierta</option>
                                    @foreach ($formOptions['sesiones'] as $cashSession)
                                        <option value="{{ $cashSession->id }}" @selected((string) old('sesion_caja_id') === (string) $cashSession->id)>{{ $cashSession->caja?->codigo }} · Sesión #{{ $cashSession->id }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="tc-finance-field" data-finance-bank-field hidden>Cuenta bancaria de la empresa *
                                <select name="cuenta_bancaria_id">
                                    <option value="">Selecciona una cuenta</option>
                                    @foreach ($formOptions['cuentas'] as $account)
                                        <option value="{{ $account->id }}" @selected((string) old('cuenta_bancaria_id') === (string) $account->id)>{{ $account->codigo }} · {{ $account->banco }} · {{ $account->moneda }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="tc-finance-field">Importe solicitado *
                                <input type="number" name="importe" value="{{ old('importe') }}" min="0.01" step="0.01" required placeholder="0.00">
                            </label>
                            <label class="tc-finance-field">Referencia
                                <input type="text" name="referencia" value="{{ old('referencia') }}" maxlength="120" placeholder="Referencia del documento o solicitud" data-finance-reference>
                            </label>
                        </div>
                        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Registro pendiente</strong><p>Esta acción no confirma el pago, no carga una tarjeta y no modifica el estado del documento.</p></div></div>
                    @else
                        <div class="tc-finance-form-grid">
                            <label class="tc-finance-field">Devolución resuelta *
                                <select name="devolucion_id" required>
                                    <option value="">Selecciona una devolución</option>
                                    @foreach ($formOptions['devoluciones'] as $return)
                                        <option value="{{ $return->id }}" @selected((string) old('devolucion_id') === (string) $return->id)>{{ $return->codigo }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="tc-finance-field">Cobro original *
                                <select name="pago_id" required>
                                    <option value="">Selecciona un cobro</option>
                                    @foreach ($formOptions['cobros'] as $payment)
                                        <option value="{{ $payment->id }}" @selected((string) old('pago_id') === (string) $payment->id)>{{ $payment->codigo }} · {{ $payment->moneda }} {{ $payment->importe }}</option>
                                    @endforeach
                                </select>
                            </label>
                            <label class="tc-finance-field">Importe solicitado *
                                <input type="number" name="importe" value="{{ old('importe') }}" min="0.01" step="0.01" required placeholder="0.00">
                            </label>
                        </div>
                        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Solicitud pendiente</strong><p>Registrar o aprobar la solicitud no ejecuta una devolución de dinero.</p></div></div>
                    @endif
                    @if (! $canSubmit)
                        <p role="status">No hay documentos y medios elegibles en la sede seleccionada para esta operación.</p>
                    @endif
                    @if (old('_form') === 'crear')
                        @foreach ($errors->all() as $error)
                            <p class="text-danger" role="alert">{{ $error }}</p>
                        @endforeach
                    @endif
                </div>
                <footer class="gestion-pendiente-modal-footer">
                    <button type="button" class="gestion-pendiente-secondary" data-finance-close>Cancelar</button>
                    <button type="submit" class="gestion-pendiente-primary" @disabled(! $canSubmit)>
                        <i class="bi bi-check2" aria-hidden="true"></i> Registrar solicitud pendiente
                    </button>
                </footer>
            </form>
        </dialog>
    @endif
</section>

@elseif (auth()->user()?->can('gestion.seguridad.consultar'))
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
            @include('gestion.finanzas.' . $moduleKey . '.crear', ['module' => $module])
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $moduleKey }}-editar-title">
            @include('gestion.finanzas.' . $moduleKey . '.editar', ['module' => $module])
        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $moduleKey }}-ver-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-ver-title">Ver {{ $module['record_singular'] ?? 'registro' }}</h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            @foreach (($module['fields'] ?? []) as $field)
                <div><small>{{ $field['label'] }}</small><strong>—</strong></div>
            @endforeach
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>

        </dialog>
        <dialog class="admin-finance-dialog gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $moduleKey }}-eliminar-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-eliminar-title">Eliminar {{ $module['record_singular'] ?? 'registro' }}</h2><p>Confirma la acción antes de continuar.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Acción no disponible todavía</strong><p>No se eliminó ningún registro. Esta confirmación es visual y quedará conectada cuando exista una operación autorizada del backend.</p></div></div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminación permanece pendiente de integración y no modificó ningún dato.">Eliminar cuando se integre</button></footer>
</div>

        </dialog>
    @endif

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

@else
    @php
    $moduleKey = $module['key'];
    $isCrud = ($module['mode'] ?? 'consult') === 'crud';
    $columns = $module['columns'] ?? [];
    $kpis = $module['kpis'] ?? [];
    $analytics = $module['analytics'] ?? [];
    $fields = $module['fields'] ?? [];
    $recordSingular = $module['record_singular'] ?? 'registro';
    $recordPlural = $module['record_plural'] ?? 'registros';
@endphp

<section class="gestion-pendiente-page gestion-pendiente-page--{{ $moduleKey }}" data-pending-module="{{ $moduleKey }}">
    <header class="gestion-pendiente-hero">
        <div class="gestion-pendiente-heading">
            <span class="gestion-pendiente-eyebrow"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i>{{ $module['area'] }}</span>
            <h1>{{ $module['title'] }}</h1>
            <p>{{ $module['description'] }}</p>
        </div>
        <div class="gestion-pendiente-hero-actions">
            <span class="gestion-pendiente-status"><i class="bi bi-link-45deg" aria-hidden="true"></i> Pendiente de integración</span>
            @if ($isCrud)
                <button class="gestion-pendiente-primary" type="button" data-pending-open="crear">
                    <i class="bi bi-plus-lg" aria-hidden="true"></i>
                    Nuevo {{ $recordSingular }}
                </button>
            @else
                <button class="gestion-pendiente-secondary" type="button" data-pending-notice="La exportación quedará habilitada cuando este módulo tenga una fuente de datos integrada.">
                    <i class="bi bi-download" aria-hidden="true"></i>
                    Exportar consulta
                </button>
            @endif
        </div>
    </header>

    <div class="gestion-pendiente-kpis">
        @foreach ($kpis as $kpi)
            <article class="gestion-pendiente-kpi gestion-pendiente-kpi--{{ $kpi['tone'] ?? 'blue' }}">
                <span class="gestion-pendiente-kpi-icon"><i class="bi {{ $kpi['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <small>{{ $kpi['label'] }}</small>
                    <strong>{{ $kpi['value'] ?? '—' }}</strong>
                    <span>{{ $kpi['helper'] ?? 'Sin datos integrados' }}</span>
                </div>
            </article>
        @endforeach
    </div>

    @if ($analytics !== [])
        <section class="gestion-pendiente-analytics" aria-label="Análisis visual de {{ $module['title'] }}">
            @foreach ($analytics as $analyticsCard)
                <article class="gestion-pendiente-analytics-card">
                    <header class="gestion-pendiente-analytics-header">
                        <div>
                            <span class="gestion-pendiente-analytics-icon"><i class="bi {{ $analyticsCard['icon'] ?? 'bi-bar-chart-line' }}" aria-hidden="true"></i></span>
                            <div>
                                <h2>{{ $analyticsCard['title'] }}</h2>
                                <p>{{ $analyticsCard['description'] }}</p>
                            </div>
                        </div>
                        <span class="gestion-pendiente-chart-status">Sin datos</span>
                    </header>

                    @if (($analyticsCard['type'] ?? 'line') === 'donut')
                        <div class="gestion-pendiente-donut-layout">
                            <div class="gestion-pendiente-donut" role="img" aria-label="Gráfica pendiente de {{ $analyticsCard['title'] }}">
                                <div><strong>—</strong><span>Sin datos</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-legend">
                                @foreach (($analyticsCard['legend'] ?? []) as $legendItem)
                                    <div><span class="gestion-pendiente-legend-dot gestion-pendiente-legend-dot--{{ $loop->index % 4 }}"></span><span>{{ $legendItem }}</span><strong>—</strong></div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="gestion-pendiente-line-chart" role="img" aria-label="Gráfica pendiente de {{ $analyticsCard['title'] }}">
                            <div class="gestion-pendiente-chart-y-axis"><span>—</span><span>—</span><span>—</span><span>—</span></div>
                            <div class="gestion-pendiente-chart-plot">
                                <svg viewBox="0 0 720 220" preserveAspectRatio="none" aria-hidden="true">
                                    <path d="M0 168 C75 158 93 174 150 138 S260 162 316 104 S420 132 478 88 S600 126 720 54" class="gestion-pendiente-placeholder-line"></path>
                                </svg>
                                <div class="gestion-pendiente-chart-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>Sin datos integrados</strong><span>La tendencia aparecerá aquí.</span></div>
                            </div>
                            <div class="gestion-pendiente-chart-x-axis">
                                @foreach (($analyticsCard['labels'] ?? ['Periodo 1', 'Periodo 2', 'Periodo 3', 'Periodo 4', 'Periodo 5']) as $label)
                                    <span>{{ $label }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </article>
            @endforeach
        </section>
    @endif

    <div class="gestion-pendiente-toolbar">
        <label class="gestion-pendiente-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar {{ $recordPlural }}</span>
            <input type="search" placeholder="Buscar {{ $recordPlural }}..." data-pending-search>
        </label>
        <label>
            <span class="visually-hidden">Filtrar por estado</span>
            <select data-pending-filter>
                <option value="">Todos los estados</option>
                <option value="activo">Activos</option>
                <option value="pendiente">Pendientes</option>
                <option value="cerrado">Cerrados</option>
            </select>
        </label>
        <button class="gestion-pendiente-secondary" type="button" data-pending-notice="Los filtros quedarán disponibles cuando exista una fuente de datos para este módulo.">
            <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
            Aplicar filtros
        </button>
    </div>

    <div class="gestion-pendiente-layout">
        <section class="gestion-pendiente-card gestion-pendiente-list-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                    <div>
                        <h2>Listado de {{ $recordPlural }}</h2>
                        <p>La información aparecerá cuando se conecte este módulo al backend.</p>
                    </div>
                </div>
                <span class="gestion-pendiente-count">Sin registros</span>
            </header>

            <div class="gestion-pendiente-table-wrap">
                <table class="gestion-pendiente-table">
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
                        <tr>
                            <td colspan="{{ max(1, count($columns) + ($isCrud ? 1 : 0)) }}">
                                <div class="gestion-pendiente-empty">
                                    <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                    <strong>No hay datos disponibles</strong>
                                    <p>Este módulo está preparado visualmente y quedará disponible cuando se integre con datos reales.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <footer class="gestion-pendiente-card-footer">
                <span>Sin resultados para mostrar</span>
                <div class="gestion-pendiente-pagination" aria-label="Paginación deshabilitada">
                    <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" class="is-active" disabled>1</button>
                    <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </footer>
        </section>

        <aside class="gestion-pendiente-card gestion-pendiente-side-card">
            <header class="gestion-pendiente-card-header">
                <div>
                    <span class="gestion-pendiente-card-icon gestion-pendiente-card-icon--purple"><i class="bi bi-stars" aria-hidden="true"></i></span>
                    <div>
                        <h2>Estado del módulo</h2>
                        <p>Preparación frontend</p>
                    </div>
                </div>
            </header>
            <div class="gestion-pendiente-integration">
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Interfaz administrativa creada</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-check2-circle" aria-hidden="true"></i><span>Diseño responsive preparado</span></div>
                <div class="gestion-pendiente-integration-row"><i class="bi bi-hourglass-split" aria-hidden="true"></i><span>Conexión de datos pendiente</span></div>
            </div>
            @if ($isCrud)
                <div class="gestion-pendiente-capabilities">
                    <h3>Interfaces preparadas</h3>
                    <button type="button" data-pending-open="crear"><i class="bi bi-plus-circle" aria-hidden="true"></i> Crear {{ $recordSingular }}</button>
                    <button type="button" data-pending-open="ver"><i class="bi bi-eye" aria-hidden="true"></i> Ver detalle</button>
                    <button type="button" data-pending-open="editar"><i class="bi bi-pencil-square" aria-hidden="true"></i> Editar registro</button>
                    <button type="button" data-pending-open="eliminar"><i class="bi bi-trash3" aria-hidden="true"></i> Confirmar eliminación</button>
                </div>
            @else
                <button class="gestion-pendiente-secondary gestion-pendiente-secondary--full" type="button" data-pending-notice="La consulta quedará habilitada cuando exista el origen de datos correspondiente.">
                    <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    Preparar consulta
                </button>
            @endif
        </aside>
    </div>

    @if ($isCrud)
        <dialog class="gestion-pendiente-modal" data-pending-modal="crear" aria-labelledby="{{ $moduleKey }}-crear-title">
            @include('gestion.' . $moduleKey . '.crear', ['module' => $module])
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="editar" aria-labelledby="{{ $moduleKey }}-editar-title">
            @include('gestion.' . $moduleKey . '.editar', ['module' => $module])
        </dialog>
        <dialog class="gestion-pendiente-modal" data-pending-modal="ver" aria-labelledby="{{ $moduleKey }}-ver-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-ver-title">Ver {{ $module['record_singular'] ?? 'registro' }}</h2><p>Detalle del registro seleccionado.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-notice"><i class="bi bi-info-circle" aria-hidden="true"></i><div><strong>Vista preparada para integración</strong><p>No hay un registro real seleccionado. El detalle se poblará desde el backend cuando se conecte este módulo.</p></div></div>
        <div class="gestion-pendiente-detail-grid">
            @foreach (($module['fields'] ?? []) as $field)
                <div><small>{{ $field['label'] }}</small><strong>—</strong></div>
            @endforeach
        </div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cerrar</button></footer>
</div>

        </dialog>
        <dialog class="gestion-pendiente-modal gestion-pendiente-modal--danger" data-pending-modal="eliminar" aria-labelledby="{{ $moduleKey }}-eliminar-title">
            <div class="gestion-pendiente-modal-shell">
    <header class="gestion-pendiente-modal-header">
        <div><span class="gestion-pendiente-modal-icon gestion-pendiente-modal-icon--danger"><i class="bi bi-trash3" aria-hidden="true"></i></span><div><span class="gestion-pendiente-eyebrow">{{ $module['area'] }}</span><h2 id="{{ $module['key'] }}-eliminar-title">Eliminar {{ $module['record_singular'] ?? 'registro' }}</h2><p>Confirma la acción antes de continuar.</p></div></div>
        <button type="button" class="gestion-pendiente-close" data-pending-close aria-label="Cerrar ventana"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
    </header>
    <div class="gestion-pendiente-modal-body">
        <div class="gestion-pendiente-modal-warning"><i class="bi bi-exclamation-triangle" aria-hidden="true"></i><div><strong>Acción no disponible todavía</strong><p>No se eliminó ningún registro. Esta confirmación es visual y quedará conectada cuando exista una operación autorizada del backend.</p></div></div>
    </div>
    <footer class="gestion-pendiente-modal-footer"><button type="button" class="gestion-pendiente-secondary" data-pending-close>Cancelar</button><button type="button" class="gestion-pendiente-danger" data-pending-notice="La eliminación permanece pendiente de integración y no modificó ningún dato.">Eliminar cuando se integre</button></footer>
</div>

        </dialog>
    @endif

    <div class="gestion-pendiente-feedback" data-pending-feedback role="status" aria-live="polite" hidden></div>
</section>

@endif
@endsection

