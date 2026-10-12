@php($isAdmin = in_array(auth()->user()->rol, ['administrador', 'ventas_minoristas', 'ventas_mayoristas'], true))
@extends($isAdmin ? 'layouts.admin' : 'layouts.store')

@section('title', 'Pedidos')
@section('body_class', 'modulo-pedidos')

@if ($isAdmin)
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/pedidos/pedidos.css') }}">
    @endpush
@endif

@section('content')
    <section class="{{ $isAdmin ? 'admin-page' : 'container content-page' }}">
        <header class="admin-page-head">
            <div><span class="eyebrow">Operación comercial</span><h1>Pedidos <i class="bi bi-cart3"></i></h1><p>Consulta el historial y actualiza el estado según tu rol.</p></div>
        </header>

        @if ($isAdmin)
            <section class="admin-panel" data-admin-table-container>
                <header class="admin-panel-header"><div class="admin-panel-title"><i class="bi bi-receipt"></i><div><h2>Lista de pedidos</h2><p>Pedidos almacenados en MySQL</p></div></div><label class="admin-toolbar-search"><i class="bi bi-search"></i><input type="search" data-table-search placeholder="Buscar pedido o cliente..."></label></header>
                <div class="admin-table-wrap">
                    <table class="admin-table">
                        <thead><tr><th>Pedido</th><th>Cliente</th><th>Fecha</th><th>Total</th><th>Estado</th><th>Acciones</th></tr></thead>
                        <tbody>
                            @forelse ($orders as $order)
                                <tr data-data-row>
                                    <td><a href="{{ route('orders.show', $order) }}"><b>#{{ $order->id }}</b></a></td>
                                    <td>{{ $order->user->nombre }}<small>{{ $order->user->correo }}</small></td>
                                    <td>{{ $order->creado_en?->format('d/m/Y H:i') ?? $order->creado_en }}</td>
                                    <td><b>S/ {{ number_format((float) $order->total, 2) }}</b></td>
                                    <td><span class="admin-status">{{ $order->estado }}</span></td>
                                    <td><a class="admin-action-icon" href="{{ route('orders.show', $order) }}" aria-label="Ver detalle"><i class="bi bi-eye"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="admin-table-empty">Todavía no hay pedidos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="admin-pagination">{{ $orders->links() }}</div>
            </section>
        @else
            @forelse ($orders as $order)
                <article class="data-row"><div><b>Pedido #{{ $order->id }}</b><small>{{ $order->creado_en?->format('d/m/Y H:i') ?? $order->creado_en }} · {{ $order->estado }}</small></div><strong>S/ {{ number_format((float) $order->total, 2) }}</strong><a class="btn btn-secondary" href="{{ route('orders.show', $order) }}">Ver detalle</a></article>
            @empty
                <p>Aún no hay pedidos.</p>
            @endforelse
            {{ $orders->links() }}
        @endif
    </section>
@endsection
