@php
    $cotizacionesAbiertas = isset($openQuotationCount) ? (int) $openQuotationCount : null;
    $pedidosDelMes = isset($monthlyOrderCount) ? (int) $monthlyOrderCount : null;
    $clientesMayoristasActivos = isset($activeWholesaleCustomerCount) ? (int) $activeWholesaleCustomerCount : null;
    $productosDisponibles = isset($availableProductCount) ? (int) $availableProductCount : null;

    $metricasComerciales = [
        [
            'etiqueta' => 'Cotizaciones abiertas',
            'valor' => $cotizacionesAbiertas,
            'detalle' => $cotizacionesAbiertas === null
                ? 'Información por integrar'
                : ($cotizacionesAbiertas === 0 ? 'Sin cotizaciones activas' : 'Requieren seguimiento'),
            'icono' => 'bi-file-earmark-text',
            'tono' => 'azul',
        ],
        [
            'etiqueta' => 'Pedidos del mes',
            'valor' => $pedidosDelMes,
            'detalle' => $pedidosDelMes === null
                ? 'Información por integrar'
                : ($pedidosDelMes === 0 ? 'Sin pedidos registrados' : 'Registrados este mes'),
            'icono' => 'bi-cart3',
            'tono' => 'verde',
        ],
        [
            'etiqueta' => 'Clientes B2B activos',
            'valor' => $clientesMayoristasActivos,
            'detalle' => $clientesMayoristasActivos === null
                ? 'Información por integrar'
                : ($clientesMayoristasActivos === 0 ? 'Aún sin actividad' : 'Con actividad comercial'),
            'icono' => 'bi-people',
            'tono' => 'violeta',
        ],
        [
            'etiqueta' => 'Productos disponibles',
            'valor' => $productosDisponibles,
            'detalle' => $productosDisponibles === null
                ? 'Información por integrar'
                : ($productosDisponibles === 0 ? 'Sin productos disponibles' : 'Catálogo actualizado'),
            'icono' => 'bi-box-seam',
            'tono' => 'celeste',
        ],
    ];

    $tieneResumenDeCotizaciones = isset($quotationStatusCounts) && is_array($quotationStatusCounts);
    $estadosCotizacion = [
        ['etiqueta' => 'Borrador', 'valor' => $tieneResumenDeCotizaciones ? (int) ($quotationStatusCounts['Borrador'] ?? 0) : null, 'tono' => 'borrador'],
        ['etiqueta' => 'Enviada', 'valor' => $tieneResumenDeCotizaciones ? (int) ($quotationStatusCounts['Enviada'] ?? 0) : null, 'tono' => 'enviada'],
        ['etiqueta' => 'Aprobada', 'valor' => $tieneResumenDeCotizaciones ? (int) ($quotationStatusCounts['Aprobada'] ?? 0) : null, 'tono' => 'aprobada'],
        ['etiqueta' => 'Rechazada', 'valor' => $tieneResumenDeCotizaciones ? (int) ($quotationStatusCounts['Rechazada'] ?? 0) : null, 'tono' => 'rechazada'],
    ];

    $totalCotizaciones = $tieneResumenDeCotizaciones
        ? array_sum(array_column($estadosCotizacion, 'valor'))
        : null;
@endphp

<section class="ventas-b2b-dashboard" aria-labelledby="ventas-b2b-titulo">
    <header class="ventas-b2b-cabecera">
        <div>
            <span class="ventas-b2b-etiqueta">Ventas mayoristas</span>
            <h1 id="ventas-b2b-titulo">Resumen comercial</h1>
            <p>Gestiona cotizaciones, pedidos y clientes B2B desde un solo lugar.</p>
        </div>

        <div class="ventas-b2b-cabecera-acciones" role="group" aria-label="Acciones principales">
            <a class="ventas-b2b-boton ventas-b2b-boton--principal" href="{{ route('quotations.index') }}">
                <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                Nueva cotización
            </a>
            <a class="ventas-b2b-boton ventas-b2b-boton--secundario" href="{{ route('orders.index') }}">
                <i class="bi bi-bag-check" aria-hidden="true"></i>
                Ver pedidos
            </a>
        </div>
    </header>

    <div class="ventas-b2b-metricas" role="list" aria-label="Indicadores comerciales">
        @foreach ($metricasComerciales as $metrica)
            <article class="ventas-b2b-metrica ventas-b2b-metrica--{{ $metrica['tono'] }}" role="listitem">
                <span class="ventas-b2b-metrica-icono" aria-hidden="true">
                    <i class="bi {{ $metrica['icono'] }}"></i>
                </span>
                <div>
                    <h2>{{ $metrica['etiqueta'] }}</h2>
                    <strong>{{ $metrica['valor'] === null ? '—' : number_format($metrica['valor']) }}</strong>
                    <p>{{ $metrica['detalle'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    <div class="ventas-b2b-analitica">
        <article class="ventas-b2b-panel ventas-b2b-actividad" aria-labelledby="ventas-b2b-actividad-titulo">
            <header class="ventas-b2b-panel-cabecera">
                <div class="ventas-b2b-panel-titulo">
                    <i class="bi bi-bar-chart-line" aria-hidden="true"></i>
                    <h2 id="ventas-b2b-actividad-titulo">Actividad comercial</h2>
                </div>

                <div class="ventas-b2b-periodos" role="group" aria-label="Periodo de actividad comercial">
                    <button class="is-active" type="button" data-ventas-periodo="7" aria-pressed="true">7 días</button>
                    <button type="button" data-ventas-periodo="30" aria-pressed="false">30 días</button>
                    <button type="button" data-ventas-periodo="90" aria-pressed="false">3 meses</button>
                </div>
            </header>

            <div class="ventas-b2b-grafico-vacio">
                <span class="ventas-b2b-grafico-icono" aria-hidden="true"><i class="bi bi-bar-chart"></i></span>
                <strong>Aún no hay ventas registradas</strong>
                <p data-ventas-periodo-mensaje aria-live="polite">La evolución aparecerá cuando se registren pedidos.</p>
            </div>
        </article>

        <article class="ventas-b2b-panel ventas-b2b-estados" aria-labelledby="ventas-b2b-estados-titulo">
            <header class="ventas-b2b-panel-cabecera">
                <div class="ventas-b2b-panel-titulo">
                    <i class="bi bi-bullseye" aria-hidden="true"></i>
                    <h2 id="ventas-b2b-estados-titulo">Estado de cotizaciones</h2>
                </div>
            </header>

            <div class="ventas-b2b-estados-contenido">
                <div
                    class="ventas-b2b-anillo"
                    role="img"
                    aria-label="{{ $totalCotizaciones === null ? 'Resumen de cotizaciones pendiente de integración' : $totalCotizaciones.' cotizaciones en total' }}"
                >
                    <div>
                        <strong>{{ $totalCotizaciones ?? '—' }}</strong>
                        <span>total</span>
                    </div>
                </div>

                <ul class="ventas-b2b-leyenda">
                    @foreach ($estadosCotizacion as $estado)
                        <li>
                            <span class="ventas-b2b-punto ventas-b2b-punto--{{ $estado['tono'] }}" aria-hidden="true"></span>
                            <span>{{ $estado['etiqueta'] }}</span>
                            <strong>{{ $estado['valor'] ?? '—' }}</strong>
                        </li>
                    @endforeach
                </ul>
            </div>
        </article>
    </div>

    <div class="ventas-b2b-inferior">
        <article class="ventas-b2b-panel ventas-b2b-cotizaciones" aria-labelledby="ventas-b2b-cotizaciones-titulo">
            <header class="ventas-b2b-panel-cabecera">
                <div class="ventas-b2b-panel-titulo">
                    <i class="bi bi-file-earmark-text" aria-hidden="true"></i>
                    <h2 id="ventas-b2b-cotizaciones-titulo">Cotizaciones recientes</h2>
                </div>
                <a href="{{ route('quotations.index') }}">Ver todas <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </header>

            <div class="ventas-b2b-cotizaciones-vacio">
                <span aria-hidden="true"><i class="bi bi-file-earmark-plus"></i></span>
                <div>
                    <strong>Todavía no hay cotizaciones</strong>
                    <p>Crea tu primera cotización para comenzar a gestionar tus clientes B2B.</p>
                    <a class="ventas-b2b-boton ventas-b2b-boton--principal" href="{{ route('quotations.index') }}">
                        <i class="bi bi-plus-circle" aria-hidden="true"></i>
                        Crear primera cotización
                    </a>
                </div>
            </div>
        </article>

        <article class="ventas-b2b-panel ventas-b2b-accesos" aria-labelledby="ventas-b2b-accesos-titulo">
            <header class="ventas-b2b-panel-cabecera">
                <div class="ventas-b2b-panel-titulo">
                    <i class="bi bi-lightning-charge" aria-hidden="true"></i>
                    <h2 id="ventas-b2b-accesos-titulo">Accesos rápidos</h2>
                </div>
            </header>

            <nav class="ventas-b2b-accesos-grid" aria-label="Accesos comerciales">
                <a href="{{ route('quotations.index') }}">
                    <i class="bi bi-file-earmark-plus" aria-hidden="true"></i>
                    <span><strong>Nueva cotización</strong><small>Gestiona una propuesta B2B</small></span>
                </a>
                <a href="{{ route('orders.index') }}">
                    <i class="bi bi-bag-check" aria-hidden="true"></i>
                    <span><strong>Ver pedidos</strong><small>Consulta pedidos mayoristas</small></span>
                </a>
                <a href="{{ route('smart.wholesale') }}">
                    <i class="bi bi-buildings" aria-hidden="true"></i>
                    <span><strong>Portal mayorista</strong><small>Explora herramientas B2B</small></span>
                </a>
                <a href="{{ route('catalog.index') }}">
                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                    <span><strong>Consultar catálogo</strong><small>Revisa productos disponibles</small></span>
                </a>
            </nav>
        </article>
    </div>
</section>
