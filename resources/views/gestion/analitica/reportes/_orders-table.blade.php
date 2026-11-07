<div class="reportes-orders-table-wrap">
    <table class="reportes-orders-table">
        <thead><tr><th>#</th><th>Cliente</th><th>Fecha</th><th>Productos</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
        <tbody>
            @forelse ($latestOrders as $order)
                @php
                    $customer = $order->user?->customer;
                    $customerName = $customer?->razon_social ?: $customer?->nombre_contacto ?: $order->user?->nombre ?: 'Cliente';
                    $status = $orderStatuses[$order->estado] ?? ['label' => $order->estado, 'class' => 'processing'];
                @endphp
                <tr>
                    <td><strong>{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>{{ $customerName }}</td>
                    <td>{{ $order->creado_en?->format('d/m/Y') }}</td>
                    <td>{{ $order->details_count }} {{ $order->details_count === 1 ? 'producto' : 'productos' }}</td>
                    <td><strong>S/ {{ number_format((float) $order->total, 2) }}</strong></td>
                    <td><span class="reportes-status reportes-status--{{ $status['class'] }}">{{ $status['label'] }}</span></td>
                    <td><a class="reportes-view-button" href="{{ route('orders.show', $order) }}" aria-label="Ver pedido {{ $order->id }}"><i class="bi bi-eye" aria-hidden="true"></i></a></td>
                </tr>
            @empty
                <tr><td class="reportes-table-empty" colspan="7"><i class="bi bi-receipt" aria-hidden="true"></i><strong>No hay pedidos recientes</strong><span>Las operaciones del periodo aparecerán aquí.</span></td></tr>
            @endforelse
        </tbody>
    </table>
</div>
