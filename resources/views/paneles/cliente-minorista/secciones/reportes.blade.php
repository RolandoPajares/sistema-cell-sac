{{-- Sección "Reportes" del cliente minorista (se abre desde el menú con #reportes) --}}
{{-- Todo se calcula con los pedidos del cliente que manda el controlador ($orders) --}}
@php
    $pedidosReporte = ($orders ?? collect())->where('estado', '!=', 'Cancelado');
    $totalGastado = $pedidosReporte->sum(fn ($pedido) => (float) $pedido->total);
    $promedio = $pedidosReporte->count() > 0 ? $totalGastado / $pedidosReporte->count() : 0;
    $unidades = $pedidosReporte->sum(fn ($pedido) => $pedido->details->sum('cantidad'));
    $pedidoMayor = (float) ($pedidosReporte->max(fn ($pedido) => (float) $pedido->total) ?? 0);

    // gasto de los últimos 6 meses (nombres en español)
    $nombresMes = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Set', 'Oct', 'Nov', 'Dic'];
    $meses = collect(range(5, 0))->map(function (int $atras) use ($pedidosReporte, $nombresMes) {
        $mes = now()->startOfMonth()->subMonths($atras);
        $total = $pedidosReporte
            ->filter(fn ($pedido) => $pedido->creado_en && $pedido->creado_en->format('Y-m') === $mes->format('Y-m'))
            ->sum(fn ($pedido) => (float) $pedido->total);

        return ['nombre' => $nombresMes[$mes->month - 1], 'total' => $total];
    });
    $mesMaximo = max($meses->max('total'), 1);

    // cuántos pedidos hay en cada estado (aquí sí se cuentan los cancelados)
    $estados = ($orders ?? collect())->groupBy('estado')->map->count()->sortDesc();
    $estadoMaximo = max($estados->max() ?? 0, 1);

    // productos que más compró
    $productos = $pedidosReporte->flatMap->details
        ->groupBy('producto_id')
        ->map(fn ($detalles) => [
            'nombre' => $detalles->first()->product?->nombre ?? 'Producto',
            'unidades' => $detalles->sum('cantidad'),
            'total' => $detalles->sum(fn ($detalle) => $detalle->cantidad * (float) $detalle->precio_unitario),
        ])
        ->sortByDesc('unidades')
        ->take(5)
        ->values();
@endphp

<section class="reporte-cliente" aria-labelledby="reporte-titulo">
    <header class="reporte-cliente-cabecera">
        <div>
            <span class="reporte-cliente-etiqueta"><i class="bi bi-bar-chart-line"></i> Reportes</span>
            <h1 id="reporte-titulo">Resumen de tus compras</h1>
            <p>Mira cuánto has comprado, en qué meses y cuáles son tus productos favoritos.</p>
        </div>
        <button type="button" class="reporte-cliente-boton" data-reporte-descargar data-reporte-archivo="mis-compras-cell-sac.csv">
            <i class="bi bi-download"></i> Descargar CSV
        </button>
    </header>

    <div class="reporte-cliente-kpis">
        <article><small>Total comprado</small><strong>S/ {{ number_format($totalGastado, 2) }}</strong></article>
        <article><small>Promedio por pedido</small><strong>S/ {{ number_format($promedio, 2) }}</strong></article>
        <article><small>Unidades compradas</small><strong>{{ $unidades }}</strong></article>
        <article><small>Pedido más alto</small><strong>S/ {{ number_format($pedidoMayor, 2) }}</strong></article>
    </div>

    <div class="reporte-cliente-columnas">
        <article class="reporte-cliente-caja">
            <h2><i class="bi bi-calendar3"></i> Gasto de los últimos 6 meses</h2>
            <div class="reporte-cliente-barras">
                @foreach ($meses as $mes)
                    <div class="reporte-cliente-barra" title="S/ {{ number_format($mes['total'], 2) }}">
                        <span class="reporte-cliente-barra-valor">{{ $mes['total'] > 0 ? 'S/ '.number_format($mes['total'], 0) : '' }}</span>
                        <span class="reporte-cliente-barra-relleno" style="height: {{ max(round($mes['total'] / $mesMaximo * 100), 2) }}%"></span>
                        <small>{{ $mes['nombre'] }}</small>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="reporte-cliente-caja">
            <h2><i class="bi bi-pie-chart"></i> Pedidos por estado</h2>
            @forelse ($estados as $estado => $cantidad)
                <div class="reporte-cliente-estado">
                    <span>{{ $estado }}</span>
                    <div class="reporte-cliente-estado-linea"><span style="width: {{ round($cantidad / $estadoMaximo * 100) }}%"></span></div>
                    <b>{{ $cantidad }}</b>
                </div>
            @empty
                <p class="reporte-cliente-vacio">Todavía no tienes pedidos.</p>
            @endforelse
        </article>
    </div>

    <article class="reporte-cliente-caja">
        <h2><i class="bi bi-trophy"></i> Productos que más compraste</h2>
        <div class="reporte-cliente-tabla-contenedor">
            <table class="reporte-cliente-tabla" data-reporte-tabla>
                <thead>
                    <tr><th>#</th><th>Producto</th><th>Unidades</th><th>Total</th></tr>
                </thead>
                <tbody>
                    @forelse ($productos as $indice => $producto)
                        <tr>
                            <td>{{ $indice + 1 }}</td>
                            <td>{{ $producto['nombre'] }}</td>
                            <td>{{ $producto['unidades'] }}</td>
                            <td>S/ {{ number_format($producto['total'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="reporte-cliente-vacio">Cuando compres algo aparecerá aquí.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>
