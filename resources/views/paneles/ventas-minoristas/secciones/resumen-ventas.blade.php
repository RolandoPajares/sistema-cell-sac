@php
    $ventasDeHoy = (float) ($todaySalesTotal ?? 0);
    $pedidosPendientes = (int) ($pendingRetailOrderCount ?? 0);
    $unidadesVendidas = (int) ($todaySoldUnits ?? 0);
    $ticketPromedio = (float) ($averageTicketAmount ?? 0);
    $productosActivos = isset($activeProductCount) ? (int) $activeProductCount : null;
    $productosDisponibles = isset($availableProductCount) ? (int) $availableProductCount : null;
    $productosConStockBajo = isset($lowStockProductCount) ? (int) $lowStockProductCount : null;
    $productosSinStock = isset($outOfStockProductCount) ? (int) $outOfStockProductCount : null;

    $metricasDeVenta = [
        [
            'etiqueta' => 'Ventas de hoy',
            'valor' => 'S/ '.number_format($ventasDeHoy, 2, '.', ','),
            'detalle' => $ventasDeHoy > 0 ? 'Ingresos registrados hoy' : 'Sin movimientos hoy',
            'icono' => 'bi-bar-chart-line',
            'tono' => 'azul',
        ],
        [
            'etiqueta' => 'Pedidos pendientes',
            'valor' => number_format($pedidosPendientes),
            'detalle' => $pedidosPendientes > 0 ? 'Requieren atención' : 'Sin pedidos por atender',
            'icono' => 'bi-receipt',
            'tono' => 'verde',
        ],
        [
            'etiqueta' => 'Unidades vendidas',
            'valor' => number_format($unidadesVendidas),
            'detalle' => $unidadesVendidas > 0 ? 'Productos vendidos hoy' : 'Sin unidades registradas',
            'icono' => 'bi-box-seam',
            'tono' => 'violeta',
        ],
        [
            'etiqueta' => 'Ticket promedio',
            'valor' => 'S/ '.number_format($ticketPromedio, 2, '.', ','),
            'detalle' => $ticketPromedio > 0 ? 'Promedio por pedido' : 'Sin ventas para promediar',
            'icono' => 'bi-tag',
            'tono' => 'naranja',
        ],
    ];
@endphp

<section class="ventas-b2c-dashboard" aria-labelledby="ventas-b2c-titulo">
    <header class="ventas-b2c-cabecera">
        <div>
            <span class="ventas-b2c-etiqueta">Ventas minoristas</span>
            <h1 id="ventas-b2c-titulo">Resumen de ventas B2C</h1>
            <p>Controla pedidos, ingresos e inventario de tu operación diaria.</p>
        </div>

        <div class="ventas-b2c-cabecera-acciones" role="group" aria-label="Acciones principales">
            <a class="ventas-b2c-boton ventas-b2c-boton--principal" href="{{ route('orders.index') }}">
                <i class="bi bi-receipt" aria-hidden="true"></i>
                Ver pedidos
            </a>
            <a class="ventas-b2c-boton ventas-b2c-boton--secundario" href="{{ route('management.inventory') }}">
                <i class="bi bi-box-seam" aria-hidden="true"></i>
                Consultar inventario
            </a>
        </div>
    </header>

    <div class="ventas-b2c-metricas" role="list" aria-label="Indicadores de ventas minoristas">
        @foreach ($metricasDeVenta as $metrica)
            <article class="ventas-b2c-metrica ventas-b2c-metrica--{{ $metrica['tono'] }}" role="listitem">
                <span class="ventas-b2c-metrica-icono" aria-hidden="true">
                    <i class="bi {{ $metrica['icono'] }}"></i>
                </span>
                <div>
                    <h2>{{ $metrica['etiqueta'] }}</h2>
                    <strong>{{ $metrica['valor'] }}</strong>
                    <p>{{ $metrica['detalle'] }}</p>
                </div>
            </article>
        @endforeach
    </div>

    <div class="ventas-b2c-analitica">
        <article class="ventas-b2c-panel ventas-b2c-actividad" aria-labelledby="ventas-b2c-actividad-titulo">
            <header class="ventas-b2c-panel-cabecera">
                <div class="ventas-b2c-panel-titulo">
                    <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>
                    <h2 id="ventas-b2c-actividad-titulo">Actividad de ventas</h2>
                </div>

                <div class="ventas-b2c-periodos" role="group" aria-label="Periodo de actividad de ventas">
                    <button class="is-active" type="button" data-ventas-b2c-periodo="7" aria-pressed="true">7 días</button>
                    <button type="button" data-ventas-b2c-periodo="30" aria-pressed="false">30 días</button>
                    <button type="button" data-ventas-b2c-periodo="90" aria-pressed="false">3 meses</button>
                </div>
            </header>

            <div class="ventas-b2c-grafico-vacio">
                <span class="ventas-b2c-grafico-icono" aria-hidden="true"><i class="bi bi-bar-chart"></i></span>
                <strong>Aún no hay ventas registradas</strong>
                <p data-ventas-b2c-mensaje aria-live="polite">La actividad aparecerá cuando se registren pedidos B2C.</p>
            </div>
        </article>

        <article class="ventas-b2c-panel ventas-b2c-inventario" aria-labelledby="ventas-b2c-inventario-titulo">
            <header class="ventas-b2c-panel-cabecera">
                <div class="ventas-b2c-panel-titulo">
                    <i class="bi bi-boxes" aria-hidden="true"></i>
                    <h2 id="ventas-b2c-inventario-titulo">Estado del inventario</h2>
                </div>
            </header>

            <div class="ventas-b2c-inventario-contenido">
                <div
                    class="ventas-b2c-anillo {{ $productosActivos === null ? 'is-pending' : '' }}"
                    role="img"
                    aria-label="{{ $productosActivos === null ? 'Resumen de inventario pendiente de integración' : $productosActivos.' productos activos' }}"
                >
                    <div>
                        <strong>{{ $productosActivos === null ? '—' : number_format($productosActivos) }}</strong>
                        <span>productos activos</span>
                    </div>
                </div>

                <ul class="ventas-b2c-leyenda">
                    <li>
                        <span class="ventas-b2c-punto ventas-b2c-punto--disponible" aria-hidden="true"></span>
                        <span>Disponibles</span>
                        <strong>{{ $productosDisponibles === null ? '—' : number_format($productosDisponibles) }}</strong>
                    </li>
                    <li>
                        <span class="ventas-b2c-punto ventas-b2c-punto--bajo" aria-hidden="true"></span>
                        <span>Stock bajo</span>
                        <strong>{{ $productosConStockBajo === null ? '—' : number_format($productosConStockBajo) }}</strong>
                    </li>
                    <li>
                        <span class="ventas-b2c-punto ventas-b2c-punto--agotado" aria-hidden="true"></span>
                        <span>Sin stock</span>
                        <strong>{{ $productosSinStock === null ? '—' : number_format($productosSinStock) }}</strong>
                    </li>
                </ul>
            </div>
        </article>
    </div>

    <div class="ventas-b2c-inferior">
        <article class="ventas-b2c-panel ventas-b2c-pedidos" aria-labelledby="ventas-b2c-pedidos-titulo">
            <header class="ventas-b2c-panel-cabecera">
                <div class="ventas-b2c-panel-titulo">
                    <i class="bi bi-receipt" aria-hidden="true"></i>
                    <h2 id="ventas-b2c-pedidos-titulo">Pedidos recientes</h2>
                </div>
                <a href="{{ route('orders.index') }}">Ver todos <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </header>

            <div class="ventas-b2c-pedidos-vacio">
                <span aria-hidden="true"><i class="bi bi-receipt-cutoff"></i></span>
                <div>
                    <strong>Consulta tus pedidos B2C</strong>
                    <p>Los pedidos de clientes minoristas aparecerán aquí cuando estén disponibles.</p>
                    <a class="ventas-b2c-boton ventas-b2c-boton--principal" href="{{ route('orders.index') }}">
                        <i class="bi bi-arrow-right-circle" aria-hidden="true"></i>
                        Ver pedidos
                    </a>
                </div>
            </div>
        </article>

        <article class="ventas-b2c-panel ventas-b2c-accesos" aria-labelledby="ventas-b2c-accesos-titulo">
            <header class="ventas-b2c-panel-cabecera">
                <div class="ventas-b2c-panel-titulo">
                    <i class="bi bi-lightning-charge" aria-hidden="true"></i>
                    <h2 id="ventas-b2c-accesos-titulo">Accesos rápidos</h2>
                </div>
            </header>

            <nav class="ventas-b2c-accesos-grid" aria-label="Accesos de ventas minoristas">
                <a href="{{ route('orders.index') }}">
                    <i class="bi bi-receipt" aria-hidden="true"></i>
                    <span><strong>Pedidos</strong><small>Revisa pedidos B2C</small></span>
                </a>
                <a href="{{ route('management.inventory') }}">
                    <i class="bi bi-box-seam" aria-hidden="true"></i>
                    <span><strong>Inventario</strong><small>Consulta existencias</small></span>
                </a>
                <a href="{{ route('catalog.index') }}">
                    <i class="bi bi-tag" aria-hidden="true"></i>
                    <span><strong>Catálogo</strong><small>Explora los productos</small></span>
                </a>
                <a href="{{ route('recommendations') }}">
                    <i class="bi bi-stars" aria-hidden="true"></i>
                    <span><strong>Recomendador</strong><small>Encuentra mejores opciones</small></span>
                </a>
            </nav>
        </article>
    </div>
</section>
