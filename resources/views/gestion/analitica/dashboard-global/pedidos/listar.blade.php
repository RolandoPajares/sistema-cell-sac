{{-- Analitica: dashboard-global\pedidos\listar.blade.php. Presenta datos preparados por el controlador. --}}
{{-- Bloque principal: presenta el resumen visual y el detalle de pedidos. --}}
@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Pedidos y operaciones recientes')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-dashboard-global')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- El controlador prepara los estados, porcentajes, barras y filtros del dashboard. --}}
<section class="tc-dg" data-dashboard-global="pedidos">
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
    @include('gestion.analitica.dashboard-global.componentes.cabecera')

{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
    <form class="tc-dg__filters tc-dg__filters--five" method="GET" action="{{ route('management.dashboard.orders') }}">
        <label><span><i class="bi bi-calendar3"></i> Rango de fechas</span><select name="periodo">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($periodOptions as $days => $label)
                <option value="{{ $days }}" @selected($filters['periodo'] === $days)>{{ $label }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-layers"></i> Estado</span><select name="estado"><option value="">Todos los estados</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($statuses as $status)
                <option value="{{ $status }}" @selected($filters['estado'] === $status)>{{ $status }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-tag"></i> Canal</span><select name="canal" @disabled(! $hasChannel)><option value="">Todos los canales</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($channels as $channel)
                <option value="{{ $channel }}" @selected($filters['canal'] === $channel)>{{ $channel }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-shop"></i> Sede</span><select name="sede" @disabled(! $hasSite)><option value="">Todas las sedes</option>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($sites as $site)
                <option value="{{ $site->id }}" @selected($filters['sede'] == $site->id)>{{ $site->nombre }}</option>
            @endforeach
        </select></label>
        <label><span><i class="bi bi-flag"></i> Prioridad</span><select disabled title="Los pedidos aún no registran prioridad en el esquema actual"><option>Sin prioridad registrada</option></select></label>
        <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
    </form>

    <div class="tc-dg__metrics tc-dg__metrics--six">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
        @foreach ($metrics as $metric)
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
            @include('gestion.analitica.dashboard-global.componentes.metrica', $metric)
        @endforeach
    </div>

    <div class="tc-dg__grid tc-dg__grid--orders">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-cart3"></i><div><h2>Pedidos recientes</h2><p>Últimos pedidos registrados en el período.</p></div><a href="{{ route('management.orders') }}">Ver todos los pedidos <i class="bi bi-arrow-right"></i></a></div>
            <div class="tc-dg__table-wrap"><table><thead><tr><th># Pedido</th><th>Cliente</th><th>Canal</th><th>Total</th><th>Estado</th><th>Fecha</th><th>Prioridad</th></tr></thead><tbody>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($orders->take(5) as $order)
                    <tr><td>{{ $order->id }}</td><td>{{ $order->user?->nombre ?? 'Sin cliente asociado' }}</td><td>{{ $order->canal ?? '—' }}</td><td>S/ {{ number_format((float) $order->total, 2) }}</td><td><span class="tc-dg__badge tc-dg__badge--{{ in_array(mb_strtolower($order->estado), ['cancelado', 'anulado'], true) ? 'red' : (in_array(mb_strtolower($order->estado), ['entregado', 'completado', 'pagado'], true) ? 'green' : 'blue') }}">{{ $order->estado }}</span></td><td>{{ $order->creado_en?->format('d/m/Y H:i') }}</td><td>—</td></tr>
                @empty
                    <tr><td class="tc-dg__table-empty" colspan="7">No hay pedidos en el período seleccionado.</td></tr>
                @endforelse
            </tbody></table></div>
        </article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-activity"></i><div><h2>Actividad operativa reciente</h2><p>Eventos registrados por el sistema.</p></div><a href="{{ route('management.audit') }}">Ver historial <i class="bi bi-arrow-right"></i></a></div>
            <ol class="tc-dg__timeline">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($activity as $event)
                    <li><i class="bi bi-clock-history"></i><span><strong>{{ $event->accion }} · {{ $event->entidad }}</strong><small>Registro {{ $event->entidad_id ?? 'general' }}</small></span><time>{{ $event->creado_en?->format('d/m H:i') }}</time></li>
                @empty
                    <li class="tc-dg__empty">No hay eventos de auditoría registrados.</li>
                @endforelse
            </ol>
        </article>
    </div>

    <div class="tc-dg__grid tc-dg__grid--thirds">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-pie-chart"></i><div><h2>Pedidos por estado</h2><p>Distribución del período.</p></div></div><div class="tc-dg__chart">@include('gestion.analitica.dashboard-global.componentes.anillo', $ordersDonut)</div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Operaciones por canal</h2><p>Cantidad registrada por canal.</p></div></div><div class="tc-dg__chart">@if ($channelBars->isEmpty())<p class="tc-dg__empty">Sin datos de canal disponibles.</p>@else @include('gestion.analitica.dashboard-global.componentes.grafico-barras', $channelChart) @endif</div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-layers"></i><div><h2>Tareas pendientes y acciones rápidas</h2><p>Accesos a operaciones pendientes.</p></div></div><div class="tc-dg__task-list"><a href="{{ route('management.orders') }}">Revisar pedidos pendientes <b>{{ number_format($statusCounts['new']) }}</b><i class="bi bi-chevron-right"></i></a><a href="{{ route('management.returns') }}">Revisar devoluciones <i class="bi bi-chevron-right"></i></a></div></article>
    </div>
</section>
@endsection
