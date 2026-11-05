@extends('layouts.administracion')

@section('title', 'Control de IMEI y series')
@section('body_class', 'modulo-inventario-logistica')

@section('content')
<section class="tc-il">
    <header class="tc-il__header">
        <div>
            <span class="tc-il__breadcrumb"><i class="bi bi-house" aria-hidden="true"></i> Inventario y logística <i class="bi bi-chevron-right" aria-hidden="true"></i> Trazabilidad</span>
            <h1><i class="bi bi-phone" aria-hidden="true"></i> Control de IMEI y series</h1>
            <p>Consulta de equipos serializados registrados en las sedes autorizadas.</p>
        </div>
    </header>

    <div class="tc-il__notice" role="status">
        <i class="bi bi-info-circle" aria-hidden="true"></i>
        <span>Los equipos serializados deben vincularse a una entrada real de inventario. Este panel no genera existencias por separado.</span>
    </div>

    <div class="tc-il__metrics">
        <article class="tc-il__metric"><span class="tc-il__metric-icon"><i class="bi bi-boxes" aria-hidden="true"></i></span><div><small>Equipos registrados</small><strong>{{ number_format($resumen['total'], 0, ',', ' ') }}</strong><span>En sedes autorizadas</span></div></article>
        <article class="tc-il__metric tc-il__metric--verde"><span class="tc-il__metric-icon"><i class="bi bi-upc-scan" aria-hidden="true"></i></span><div><small>Con IMEI</small><strong>{{ number_format($resumen['con_imei'], 0, ',', ' ') }}</strong><span>Identificadores consultados</span></div></article>
        <article class="tc-il__metric tc-il__metric--violeta"><span class="tc-il__metric-icon"><i class="bi bi-upc" aria-hidden="true"></i></span><div><small>Con número de serie</small><strong>{{ number_format($resumen['con_serie'], 0, ',', ' ') }}</strong><span>Identificadores consultados</span></div></article>
        <article class="tc-il__metric tc-il__metric--ambar"><span class="tc-il__metric-icon"><i class="bi bi-list-check" aria-hidden="true"></i></span><div><small>Estados registrados</small><strong>{{ number_format($resumen['estados'], 0, ',', ' ') }}</strong><span>Valores presentes en MariaDB</span></div></article>
    </div>

    <form class="tc-il__filters" method="GET" action="{{ route('management.inventory.preview.serials') }}">
        <label class="tc-il__search">
            <i class="bi bi-search" aria-hidden="true"></i>
            <input type="search" name="buscar" value="{{ request('buscar') }}" maxlength="100" placeholder="Buscar por IMEI, serie, producto o variante" aria-label="Buscar dispositivos">
        </label>
        <div class="tc-il__filter-fields">
            <label>
                <span>Producto</span>
                <select name="producto">
                    <option value="">Todos los productos</option>
                    @foreach ($productos as $producto)
                        <option value="{{ $producto->id }}" @selected((string) request('producto') === (string) $producto->id)>{{ $producto->marca }} / {{ $producto->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Almacén</span>
                <select name="almacen">
                    <option value="">Todos los almacenes autorizados</option>
                    @foreach ($almacenes as $almacen)
                        <option value="{{ $almacen->id }}" @selected((string) request('almacen') === (string) $almacen->id)>{{ $almacen->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Estado</span>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    @foreach ($estados as $estado)
                        <option value="{{ $estado }}" @selected(request('estado') === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>
            <button type="submit" class="tc-il__button tc-il__button--primary"><i class="bi bi-funnel" aria-hidden="true"></i> Aplicar filtros</button>
            @if (request()->hasAny(['buscar', 'producto', 'almacen', 'estado']))
                <a class="tc-il__button" href="{{ route('management.inventory.preview.serials') }}">Limpiar filtros</a>
            @endif
        </div>
    </form>

    <div class="tc-il__main-grid">
        <article class="tc-il__card tc-il__table-card">
            <div class="tc-il__card-heading">
                <h2><i class="bi bi-list-ul" aria-hidden="true"></i> IMEI y series registradas</h2>
                <small>{{ number_format($dispositivos->total(), 0, ',', ' ') }} resultados</small>
            </div>
            <div class="tc-il__table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>IMEI / serie</th>
                            <th>Producto</th>
                            <th>Variante</th>
                            <th>Almacén</th>
                            <th>Estado</th>
                            <th>Ingreso</th>
                            <th>Último movimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dispositivos as $dispositivo)
                            <tr>
                                <td>{{ $dispositivo->identificadorLegible() }}</td>
                                <td>{{ $dispositivo->producto?->marca }} / {{ $dispositivo->producto?->nombre }}</td>
                                <td>{{ $dispositivo->variante?->codigo_sku ?: $dispositivo->variante?->nombre_color ?: '—' }}</td>
                                <td>{{ $dispositivo->almacen?->nombre ?? '—' }}@if ($dispositivo->almacen?->sede?->nombre) / {{ $dispositivo->almacen->sede->nombre }}@endif</td>
                                <td><span class="tc-il__pill">{{ $dispositivo->estado }}</span></td>
                                <td>{{ $dispositivo->fecha_ingreso?->format('d/m/Y H:i') ?? '—' }}</td>
                                <td>
                                    @if ($dispositivo->ultimoMovimiento)
                                        {{ $dispositivo->ultimoMovimiento->estado_anterior ? $dispositivo->ultimoMovimiento->estado_anterior.' → ' : '' }}{{ $dispositivo->ultimoMovimiento->estado_nuevo }}
                                        <small>{{ $dispositivo->ultimoMovimiento->creado_en?->format('d/m/Y H:i') }}</small>
                                    @else
                                        Sin movimiento vinculado
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="tc-il__empty">
                                    @if ($resumen['total'] === 0)
                                        No hay dispositivos registrados para las sedes autorizadas.
                                    @else
                                        No se encontraron dispositivos con los filtros seleccionados.
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($dispositivos->hasPages())
                <div class="tc-il__pagination">{{ $dispositivos->links() }}</div>
            @endif
        </article>

        <article class="tc-il__card">
            <div class="tc-il__card-heading"><h2><i class="bi bi-pie-chart" aria-hidden="true"></i> Equipos por estado</h2></div>
            @forelse ($distribucion as $grupo)
                <div class="tc-il__card-heading">
                    <span>{{ $grupo['estado'] }}</span>
                    <strong>{{ number_format($grupo['cantidad'], 0, ',', ' ') }}</strong>
                </div>
            @empty
                <p class="tc-il__empty">Todavía no hay estados de equipos registrados.</p>
            @endforelse
        </article>
    </div>
</section>
@endsection
