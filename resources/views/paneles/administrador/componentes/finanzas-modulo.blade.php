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
                <button class="admin-finance-button admin-finance-button--secondary" type="button" data-pending-notice="La exportación estará disponible cuando exista una fuente financiera integrada."><i class="bi bi-download" aria-hidden="true"></i> Exportar</button>
                <button class="admin-finance-button admin-finance-button--secondary" type="button" data-pending-notice="{{ $financeContent['secondary'] }} quedará disponible al conectar el backend financiero."><i class="bi bi-arrow-repeat" aria-hidden="true"></i> {{ $financeContent['secondary'] }}</button>
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

    <form class="admin-finance-filters" data-pending-form>
        <label class="admin-finance-search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <span class="visually-hidden">Buscar {{ $recordPlural }}</span>
            <input type="search" placeholder="Buscar {{ $recordSingular }}, cliente o referencia..." data-pending-search>
        </label>
        <label><span class="visually-hidden">Método o tipo</span><select data-pending-filter><option>Método: Todos</option><option>Transferencia</option><option>Tarjeta</option><option>Efectivo</option></select></label>
        <label><span class="visually-hidden">Estado</span><select data-pending-filter><option>Estado: Todos</option><option>Pendiente</option><option>En revisión</option><option>Confirmado</option></select></label>
        <label><span class="visually-hidden">Periodo</span><select data-pending-filter><option>Fecha: Este mes</option><option>Últimos 30 días</option><option>Este año</option></select></label>
        <button class="admin-finance-filter-button" type="submit"><i class="bi bi-funnel" aria-hidden="true"></i> Filtrar</button>
        <button class="admin-finance-clear-button" type="button" data-pending-notice="Los filtros se limpiarán cuando el módulo tenga una fuente financiera conectada.">Limpiar filtros</button>
    </form>
    <span class="admin-finance-filter-status" data-finance-filter-status role="status" aria-live="polite"></span>

    <section class="admin-finance-table-card">
        <header class="admin-finance-card-header">
            <div>
                <span class="admin-finance-card-icon"><i class="bi {{ $module['icon'] }}" aria-hidden="true"></i></span>
                <div>
                    <h2>{{ $financeContent['tableTitle'] }}</h2>
                    <p>La información aparecerá cuando se conecten los registros financieros.</p>
                </div>
            </div>
            <span class="admin-finance-count">Sin registros</span>
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
                    <tr>
                        <td colspan="{{ max(1, count($columns) + ($isCrud ? 1 : 0)) }}">
                            <div class="admin-finance-empty">
                                <span><i class="bi bi-inbox" aria-hidden="true"></i></span>
                                <strong>No hay {{ $recordPlural }} para mostrar</strong>
                                <p>Este módulo está listo visualmente; los datos aparecerán cuando exista la integración financiera.</p>
                                @if ($isCrud)
                                    <button class="admin-finance-empty-button" type="button" data-pending-open="crear"><i class="bi bi-plus-lg" aria-hidden="true"></i> {{ $financeContent['action'] }}</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <footer class="admin-finance-table-footer">
            <span>Sin resultados para mostrar</span>
            <nav class="admin-finance-pagination" aria-label="Paginación deshabilitada">
                <button type="button" disabled aria-label="Página anterior"><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                <button type="button" class="is-active" disabled>1</button>
                <button type="button" disabled aria-label="Página siguiente"><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
            </nav>
        </footer>
    </section>

    <section class="admin-finance-summary-grid" aria-label="Resumen de {{ $financeContent['title'] }}">
        @foreach ($financeContent['summary'] as $summary)
            <article class="admin-finance-summary-card">
                <header>
                    <div><span class="admin-finance-summary-icon admin-finance-summary-icon--{{ $summary['tone'] }}"><i class="bi {{ $summary['icon'] }}" aria-hidden="true"></i></span><h2>{{ $summary['title'] }}</h2></div>
                    <button type="button" data-pending-notice="Este resumen se habilitará al integrar datos financieros."><i class="bi bi-arrow-right" aria-hidden="true"></i></button>
                </header>
                <div class="admin-finance-summary-empty"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><strong>{{ $summary['text'] }}</strong><span>Sin datos integrados</span></div>
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
