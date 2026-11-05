@extends('layouts.administracion')

@section('title', 'Alertas y notificaciones')
@section('body_class', 'modulo-dashboard-global')

@section('content')
<section class="tc-dg" data-dashboard-global="alertas">
    @include('paneles.administrador.dashboard-global.componentes.cabecera', ['titulo' => 'Alertas y notificaciones', 'icono' => 'bi-bell', 'descripcion' => 'Consulta las notificaciones persistidas para la cuenta administrativa actual.', 'exportRoute' => $disponible ? 'management.reports.dashboard_alerts.export' : null, 'exportParameters' => array_filter(['estado' => $estado, 'buscar' => $busqueda], static fn ($value) => $value !== null && $value !== '')])

    @unless ($disponible)
        <div class="tc-dg__empty" role="status">La tabla notificaciones no está disponible en la base de datos.</div>
    @else
        <form id="filtro-estado-alertas" method="GET" action="{{ route('management.dashboard.alerts') }}">
            @if ($busqueda !== null && $busqueda !== '')
                <input type="hidden" name="buscar" value="{{ $busqueda }}">
            @endif
        </form>

        <div class="tc-dg__tabs" role="tablist" aria-label="Estado de notificaciones">
            <button type="submit" form="filtro-estado-alertas" name="estado" value="todas" role="tab" aria-selected="{{ $estado === 'todas' ? 'true' : 'false' }}"><i class="bi bi-bell"></i> Todas <span>({{ number_format($resumen['total']) }})</span></button>
            <button type="submit" form="filtro-estado-alertas" name="estado" value="no_leidas" role="tab" aria-selected="{{ $estado === 'no_leidas' ? 'true' : 'false' }}"><i class="bi bi-bell-fill"></i> No leídas <span>({{ number_format($resumen['no_leidas']) }})</span></button>
            <button type="submit" form="filtro-estado-alertas" name="estado" value="leidas" role="tab" aria-selected="{{ $estado === 'leidas' ? 'true' : 'false' }}"><i class="bi bi-envelope-open"></i> Leídas <span>({{ number_format($resumen['leidas']) }})</span></button>
        </div>

        <form class="tc-dg__filters" method="GET" action="{{ route('management.dashboard.alerts') }}">
            <label><span><i class="bi bi-search"></i> Buscar notificaciones</span><input type="search" name="buscar" value="{{ $busqueda }}" maxlength="120"></label>
            <label><span><i class="bi bi-funnel"></i> Estado</span><select name="estado">
                <option value="todas" @selected($estado === 'todas')>Todas</option>
                <option value="no_leidas" @selected($estado === 'no_leidas')>No leídas</option>
                <option value="leidas" @selected($estado === 'leidas')>Leídas</option>
            </select></label>
            <button type="submit"><i class="bi bi-arrow-repeat"></i> Aplicar filtros</button>
        </form>

        <div class="tc-dg__metrics tc-dg__metrics--four">
            @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'Notificaciones', 'icono' => 'bi-bell', 'valor' => number_format($resumen['total']), 'detalle' => 'Registros asociados a esta cuenta'])
            @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'No leídas', 'icono' => 'bi-envelope-exclamation', 'tono' => 'rojo', 'valor' => number_format($resumen['no_leidas']), 'detalle' => 'Según leido_en en MariaDB'])
            @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'Leídas', 'icono' => 'bi-envelope-open', 'tono' => 'verde', 'valor' => number_format($resumen['leidas']), 'detalle' => 'Según leido_en en MariaDB'])
            @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'Tipos registrados', 'icono' => 'bi-tags', 'tono' => 'violeta', 'valor' => number_format($resumen['tipos']), 'detalle' => 'Tipos distintos almacenados'])
        </div>

        <form id="seleccionar-notificacion" method="GET" action="{{ route('management.dashboard.alerts') }}">
            <input type="hidden" name="estado" value="{{ $estado }}">
            @if ($busqueda !== null && $busqueda !== '')
                <input type="hidden" name="buscar" value="{{ $busqueda }}">
            @endif
        </form>

        <div class="tc-dg__grid tc-dg__grid--alerts">
            <article class="tc-dg__card">
                <div class="tc-dg__card-head"><i class="bi bi-bell"></i><div><h2>Notificaciones ({{ number_format($resumen['total']) }})</h2><p>Registros de la cuenta administrativa autenticada.</p></div><span>{{ $estado === 'todas' ? 'Todas' : ($estado === 'no_leidas' ? 'No leídas' : 'Leídas') }}</span></div>
                <div class="tc-dg__alert-list">
                    @forelse ($notificaciones as $notificacion)
                        <button class="{{ $notificacionSeleccionada?->is($notificacion) ? 'is-selected' : '' }}" type="submit" form="seleccionar-notificacion" name="notificacion" value="{{ $notificacion->id }}">
                            <i class="bi {{ $notificacion->leido_en ? 'bi-envelope-open' : 'bi-bell-fill' }}"></i>
                            <span><strong>{{ $notificacion->nombreTipo() }}</strong><small>{{ $notificacion->datosLegibles() }}</small></span>
                            <b>{{ $notificacion->leido_en ? 'Leída' : 'No leída' }}</b>
                            <time>{{ $notificacion->creado_en?->format('d/m/Y H:i') ?? 'Fecha no informada' }}</time>
                        </button>
                    @empty
                        <p class="tc-dg__alert-empty">
                            @if ($resumen['total'] === 0)
                                No hay notificaciones registradas.
                            @else
                                No hay notificaciones que coincidan con los filtros aplicados.
                            @endif
                        </p>
                    @endforelse
                </div>
                {{ $notificaciones->links() }}
            </article>

            <article class="tc-dg__card tc-dg__alert-detail">
                @if ($notificacionSeleccionada)
                    <div class="tc-dg__card-head"><i class="bi bi-bell"></i><div><h2>{{ $notificacionSeleccionada->nombreTipo() }}</h2><p>Datos persistidos en la notificación seleccionada.</p></div><span>{{ $notificacionSeleccionada->leido_en ? 'Leída' : 'No leída' }}</span></div>
                    <p class="tc-dg__detail-label">Datos de la notificación</p>
                    <p class="tc-dg__detail-copy">{{ $notificacionSeleccionada->datosLegibles() }}</p>
                    <div class="tc-dg__detail-grid">
                        <div><i class="bi bi-tag"></i><small>Tipo</small><strong>{{ $notificacionSeleccionada->tipo }}</strong></div>
                        <div><i class="bi bi-envelope"></i><small>Lectura</small><strong>{{ $notificacionSeleccionada->leido_en ? 'Leída' : 'Pendiente de lectura' }}</strong></div>
                        <div><i class="bi bi-calendar-event"></i><small>Creada</small><strong>{{ $notificacionSeleccionada->creado_en?->format('d/m/Y H:i') ?? 'Fecha no informada' }}</strong></div>
                        <div><i class="bi bi-fingerprint"></i><small>Identificador</small><strong>{{ $notificacionSeleccionada->id }}</strong></div>
                    </div>
                    @unless ($notificacionSeleccionada->leido_en)
                        <div class="tc-dg__alert-actions">
                            <form method="POST" action="{{ route('management.account.notifications.read', $notificacionSeleccionada->id) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit">Marcar como leída</button>
                            </form>
                        </div>
                    @endunless
                @else
                    <div class="tc-dg__card-head"><i class="bi bi-bell-slash"></i><div><h2>Detalle de notificación</h2><p>Selecciona un registro para consultar sus datos.</p></div></div>
                    <p class="tc-dg__empty">No hay notificaciones para mostrar.</p>
                @endif
            </article>
        </div>

        <div class="tc-dg__grid tc-dg__grid--thirds">
            <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-pie-chart"></i><div><h2>Notificaciones por tipo</h2><p>Distribución calculada desde registros reales.</p></div></div><div class="tc-dg__chart">
                @if ($resumen['distribucion'] === [])
                    <p class="tc-dg__empty">No hay datos para construir la distribución.</p>
                @else
                    @include('paneles.administrador.dashboard-global.componentes.anillo', ['centro' => number_format($resumen['total']), 'etiqueta' => 'Notificaciones', 'gradiente' => $resumen['gradiente'], 'leyenda' => $resumen['distribucion']])
                @endif
            </div></article>
            <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-envelope-check"></i><div><h2>Estado de lectura</h2><p>Conteo actual del destinatario autenticado.</p></div></div><div class="tc-dg__task-list"><span>No leídas <b>{{ number_format($resumen['no_leidas']) }}</b></span><span>Leídas <b>{{ number_format($resumen['leidas']) }}</b></span></div></article>
            <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-person-check"></i><div><h2>Alcance de consulta</h2><p>Los registros quedan aislados por destinatario.</p></div></div><div class="tc-dg__task-list"><span>Cuenta consultada <b>Autenticada</b></span><a href="{{ route('management.account.notifications') }}">Ver mis notificaciones <i class="bi bi-arrow-right"></i></a></div></article>
        </div>
    @endunless
</section>
@endsection
