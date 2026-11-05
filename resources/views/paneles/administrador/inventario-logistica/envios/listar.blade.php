@extends('layouts.administracion')

@section('title', 'Envíos y entregas')
@section('body_class', 'modulo-inventario-logistica')

@section('content')
<section class="tc-il">
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Inventario y logística <i class="bi bi-chevron-right" aria-hidden="true"></i> Ecommerce</span>
            <h1><i class="bi bi-truck" aria-hidden="true"></i> Envíos y entregas</h1>
            <p>Consulta de envíos de pedidos y sus últimos eventos registrados.</p>
        </div>
    </header>

    <div class="tc-il__notice" role="status">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>El catálogo RBAC mantiene deshabilitada la entrada admin.shipments. Esta vista es de consulta y no altera estados de entrega.</span>
    </div>

    <div class="tc-il__metrics">
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></span><div><small>Envíos registrados</small><strong>{{ number_format($resumen['total'], 0, ',', ' ') }}</strong><span>En sedes autorizadas</span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-upc" aria-hidden="true"></i></span><div><small>Con seguimiento</small><strong>{{ number_format($resumen['con_seguimiento'], 0, ',', ' ') }}</strong><span>Números guardados</span></div></article>
        <article class="tc-il__metric tc-il__metric--violeta"><span class="tc-il__metric-icon"><i class="bi bi-file-earmark-text" aria-hidden="true"></i></span><div><small>Con guía de remisión</small><strong>{{ number_format($resumen['con_guia'], 0, ',', ' ') }}</strong><span>Referencias almacenadas</span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-signpost-split" aria-hidden="true"></i></span><div><small>Modalidades registradas</small><strong>{{ number_format($resumen['modalidades'], 0, ',', ' ') }}</strong><span>Valores presentes en MariaDB</span></div></article>
    </div>

    <form class="tc-il__filters" method="GET" action="{{ route('management.inventory.preview.shipments') }}">
        <label class="tc-il__search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="buscar" value="{{ request('buscar') }}" maxlength="100" placeholder="Buscar por código, pedido, transportista o seguimiento" aria-label="Buscar envíos">
        </label>
        <div class="tc-il__filter-fields">
            <label>
                <span>Estado</span>
                <select name="estado">
                    <option value="">Todos los estados registrados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Modalidad</span>
                <select name="modalidad">
                    <option value="">Todas las modalidades registradas</option>
                    @foreach ($modalidades as $modalidad)
                        <option value="{{ $modalidad }}" @selected(request('modalidad') === $modalidad)>{{ $modalidad }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="tc-il__button tc-il__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            @if (request()->hasAny(['buscar', 'estado', 'modalidad']))
                <a class="tc-il__button" href="{{ route('management.inventory.preview.shipments') }}">Limpiar filtros</a>
            @endif
        </div>
    </form>

    <div class="tc-il__main-grid">
        <article class="tc-il__card tc-il__table-card">
            <div class="tc-il__card-heading">
                <h2><i class="bi bi-truck" aria-hidden="true"></i> Envíos registrados</h2>
                <small>{{ number_format($envios->total(), 0, ',', ' ') }} resultados</small>
            </div>
            <div class="tc-il__table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Pedido</th>
                            <th>Almacén / sede</th>
                            <th>Transportista / servicio</th>
                            <th>Seguimiento</th>
                            <th>Estado</th>
                            <th>Último evento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($envios as $envio)
                            <tr>
                                <td>{{ $envio->codigo }}</td>
                                <td>Pedido #{{ $envio->pedido_id }}</td>
                                <td>{{ $envio->almacen?->nombre ?? '—' }}@if ($envio->almacen?->sede?->nombre) / {{ $envio->almacen->sede->nombre }}@endif</td>
                                <td>{{ $envio->transportista ?: '—' }}@if ($envio->servicio) / {{ $envio->servicio }}@endif</td>
                                <td>{{ $envio->numero_seguimiento ?: '—' }}</td>
                                <td><span class="tc-il__pill">{{ $envio->estado }}</span></td>
                                <td>
                                    @if ($envio->ultimoEvento)
                                        {{ $envio->ultimoEvento->estado }}@if ($envio->ultimoEvento->descripcion) — {{ $envio->ultimoEvento->descripcion }}@endif
                                        <small>{{ $envio->ultimoEvento->ocurrido_en?->format('d/m/Y H:i') }}</small>
                                    @else
                                        Sin eventos registrados
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="tc-il__empty">
                                    @if ($resumen['total'] === 0)
                                        No hay envíos registrados para las sedes autorizadas.
                                    @else
                                        No se encontraron envíos con los filtros seleccionados.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($envios->hasPages())
                <div class="tc-il__pagination">{{ $envios->links() }}</div>
            @endif
        </article>

        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Envíos por estado</h2></div>
            @forelse ($distribucion as $grupo)
                <div class="tc-il__card-heading"><span>{{ $grupo['estado'] }}</span><strong>{{ number_format($grupo['cantidad'], 0, ',', ' ') }}</strong></div>
            @empty
                <p class="tc-il__empty">Todavía no hay estados de envío registrados.</p>
            @endforelse
        </article>
    </div>
</section>
@endsection
