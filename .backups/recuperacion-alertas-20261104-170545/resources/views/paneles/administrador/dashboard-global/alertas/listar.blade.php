@extends('layouts.administracion')

@section('title', 'Alertas y notificaciones')
@section('body_class', 'modulo-dashboard-global')

@section('content')
@php
    $alertasPorTipo = [
        ['nombre' => 'Inventario', 'valor' => '37.5%', 'color' => '#0767f5'],
        ['nombre' => 'Pedidos', 'valor' => '18.8%', 'color' => '#0bc4ee'],
        ['nombre' => 'Seguridad', 'valor' => '12.5%', 'color' => '#9b82f6'],
        ['nombre' => 'Otros', 'valor' => '31.2%', 'color' => '#77bbfb'],
    ];
@endphp
<section class="tc-dg" data-dashboard-global="alertas">
    @include('paneles.administrador.dashboard-global.componentes.cabecera', ['titulo' => 'Alertas y notificaciones', 'icono' => 'bi-bell', 'descripcion' => 'Monitorea eventos importantes, incidencias y recordatorios del sistema.'])
    @include('paneles.administrador.dashboard-global.componentes.aviso-diseno')

    <div class="tc-dg__tabs" role="tablist" aria-label="Tipos de alertas" data-alert-tabs>
        <button type="button" role="tab" aria-selected="true" data-alert-tab="todas"><i class="bi bi-exclamation-triangle"></i> Todas <span>(27)</span></button>
        <button type="button" role="tab" aria-selected="false" data-alert-tab="inventario"><i class="bi bi-box-seam"></i> Inventario <span>(12)</span></button>
        <button type="button" role="tab" aria-selected="false" data-alert-tab="pedidos"><i class="bi bi-cart3"></i> Pedidos <span>(5)</span></button>
        <button type="button" role="tab" aria-selected="false" data-alert-tab="seguridad"><i class="bi bi-shield-check"></i> Seguridad <span>(3)</span></button>
        <button type="button" role="tab" aria-selected="false" data-alert-tab="sistema"><i class="bi bi-gear"></i> Sistema <span>(4)</span></button>
    </div>

    <div class="tc-dg__metrics tc-dg__metrics--four">
        @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'Alertas críticas', 'icono' => 'bi-exclamation-triangle', 'tono' => 'rojo', 'valor' => '8', 'detalle' => '↑ 33.3% vs. mes anterior'])
        @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'No leídas', 'icono' => 'bi-bell', 'valor' => '27', 'detalle' => '↓ 18.2% vs. mes anterior'])
        @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'Vencimientos', 'icono' => 'bi-calendar-event', 'tono' => 'ambar', 'valor' => '14', 'detalle' => '↑ 27.3% vs. mes anterior'])
        @include('paneles.administrador.dashboard-global.componentes.metrica', ['titulo' => 'Incidencias resueltas', 'icono' => 'bi-check-circle', 'tono' => 'verde', 'valor' => '96', 'detalle' => '↑ 22.4% vs. mes anterior'])
    </div>

    <div class="tc-dg__grid tc-dg__grid--alerts">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bell"></i><div><h2>Alertas y notificaciones (27)</h2><p>Ejemplos de alertas recientes.</p></div><span>Todas las alertas</span></div><div class="tc-dg__alert-list" data-alert-list><button class="is-selected" type="button" data-alert-kind="inventario" data-alert-title="Stock crítico: iPhone 15 (128 GB)" data-alert-description="El inventario de ejemplo está por debajo del mínimo configurado."><i class="bi bi-exclamation-triangle"></i><span><strong>Stock crítico: iPhone 15 (128 GB)</strong><small>El inventario está por debajo del mínimo (5 unidades).</small></span><b>Crítica</b><time>Hoy, 12:46</time></button><button type="button" data-alert-kind="inventario" data-alert-title="Vencimiento de garantía" data-alert-description="Tres equipos de ejemplo vencerán en los próximos 7 días."><i class="bi bi-calendar-event"></i><span><strong>Vencimiento de garantía</strong><small>3 equipos con garantía por vencer.</small></span><b>Inventario</b><time>Hoy, 11:20</time></button><button type="button" data-alert-kind="pedidos" data-alert-title="Pedido con retraso" data-alert-description="Un pedido de ejemplo supera el tiempo de entrega estimado."><i class="bi bi-cart3"></i><span><strong>Pedido #PD-2841 con retraso</strong><small>Supera el tiempo estimado de entrega.</small></span><b>Pedidos</b><time>Hoy, 10:18</time></button><button type="button" data-alert-kind="seguridad" data-alert-title="Intento de acceso no autorizado" data-alert-description="Ejemplo de un evento de acceso que requiere revisión."><i class="bi bi-shield-exclamation"></i><span><strong>Intento de acceso no autorizado</strong><small>Se detectó un inicio de sesión fallido.</small></span><b>Seguridad</b><time>Ayer, 19:37</time></button><button type="button" data-alert-kind="sistema" data-alert-title="Tarea programada con error" data-alert-description="Ejemplo de una sincronización de inventario fallida."><i class="bi bi-gear"></i><span><strong>Tarea programada con error</strong><small>Falló una sincronización de inventario.</small></span><b>Sistema</b><time>Ayer, 17:05</time></button><p class="tc-dg__alert-empty" hidden data-alert-empty>No hay ejemplos en esta categoría.</p></div></article>
        <article class="tc-dg__card tc-dg__alert-detail"><div class="tc-dg__card-head"><i class="bi bi-exclamation-triangle"></i><div><h2 data-alert-detail-title>Stock crítico: iPhone 15 (128 GB)</h2><p>Detalle del evento seleccionado (ejemplo).</p></div><span>Crítica</span></div><p class="tc-dg__detail-label">Descripción</p><p class="tc-dg__detail-copy" data-alert-detail-description>El inventario de ejemplo está por debajo del mínimo configurado.</p><div class="tc-dg__detail-grid"><div><i class="bi bi-box-seam"></i><small>Producto</small><strong>iPhone 15 (128 GB)</strong></div><div><i class="bi bi-box"></i><small>Stock actual</small><strong>5 unidades</strong></div><div><i class="bi bi-geo-alt"></i><small>Sede</small><strong>Tienda online</strong></div><div><i class="bi bi-tag"></i><small>Categoría</small><strong>Celulares</strong></div></div><p class="tc-dg__recommendation"><i class="bi bi-lightbulb"></i><span><strong>Recomendación de ejemplo</strong> Genera una orden de compra o transfiere stock al integrar el inventario por sede.</span></p><div class="tc-dg__alert-actions"><button type="button" disabled>Marcar como leída</button><button type="button" disabled>Asignar</button><button type="button" disabled>Resolver</button></div></article>
    </div>

    <div class="tc-dg__grid tc-dg__grid--thirds">
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-calendar3"></i><div><h2>Recordatorios (3)</h2><p>Ejemplos de vencimientos importantes.</p></div></div><div class="tc-dg__task-list"><span>Renovar licencias del sistema <b>En 3 días</b></span><span>Revisión de accesos de usuarios <b>En 4 días</b></span><span>Cierre mensual de inventario <b>En 8 días</b></span></div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-gear"></i><div><h2>Tareas automáticas (4)</h2><p>Ejemplos de procesos programados.</p></div></div><div class="tc-dg__task-list"><span>Sincronización de inventario <b>Activa</b></span><span>Respaldo de base de datos <b>Activa</b></span><span>Envío de reportes por correo <b>Activa</b></span></div></article>
        <article class="tc-dg__card"><div class="tc-dg__card-head"><i class="bi bi-bar-chart"></i><div><h2>Alertas por tipo</h2><p>Ejemplo de distribución por categoría.</p></div></div><div class="tc-dg__chart">@include('paneles.administrador.dashboard-global.componentes.anillo', ['centro' => '64', 'etiqueta' => 'Alertas totales', 'gradiente' => 'conic-gradient(#0767f5 0 37%, #0bc4ee 37% 56%, #9b82f6 56% 69%, #77bbfb 69% 82%, #8ce5db 82% 100%)', 'leyenda' => $alertasPorTipo])</div></article>
    </div>
</section>
@endsection
