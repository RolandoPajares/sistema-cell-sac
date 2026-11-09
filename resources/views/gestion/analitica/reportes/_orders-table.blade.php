{{-- Componente de analitica: reportes\_orders-table.blade.php. Renderiza un bloque visual reutilizable. --}}
{{-- Bloque reutilizable: muestra el listado tabular de pedidos recientes del reporte. --}}
<div class="reportes-orders-table-wrap">
    <table class="reportes-orders-table">
        {{-- Encabezado de la tabla con las columnas principales del pedido. --}}
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Productos</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
            {{-- Recorre los pedidos preparados por el controlador para mostrar cada registro. --}}
            @forelse ($latestOrders as $order)
                <tr>
                    {{-- Identificador del pedido con formato uniforme de seis dígitos. --}}
                    <td><strong>{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                    {{-- Nombre del cliente preparado previamente por el controlador. --}}
                    <td>{{ $order->customer_name }}</td>
                    {{-- Fecha de creación presentada en formato corto. --}}
                    <td>{{ $order->creado_en?->format('d/m/Y') }}</td>
                    {{-- Cantidad total de productos incluidos en el pedido. --}}
                    <td>{{ $order->details_count }} {{ $order->details_count === 1 ? 'producto' : 'productos' }}</td>
                    {{-- Importe total presentado con formato monetario. --}}
                    <td><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></td>
                    {{-- Estado visual usando la clase y etiqueta preparadas por el controlador. --}}
                    <td><span class="reportes-status reportes-status--{{ $order->status_class }}">{{ $order->status_label }}</span></td>
                    {{-- Enlace de solo consulta hacia el detalle del pedido. --}}
                    <td><a class="reportes-view-button" href="{{ route('orders.show', $order) }}" aria-label="Ver pedido {{ $order->id }}"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
                </tr>
            {{-- Mensaje mostrado cuando la consulta no devuelve pedidos. --}}
            @empty
                <tr><td class="reportes-table-empty" colspan="7"><i class="bi bi-receipt" aria-hidden="true"></i><strong>No hay pedidos recientes</strong><span>Las operaciones del periodo aparecerán aquí.</span></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
