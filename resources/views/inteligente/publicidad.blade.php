@extends('layouts.administracion')

@section('title', 'Publicidad inteligente')
@section('body_class', 'modulo-publicidad-inteligente')

@section('content')
    <section class="publicidad-ia-fondo">
        <div class="container publicidad-ia-layout">
            <aside class="publicidad-ia-menu">
                <div><i class="bi bi-stars"></i><span><b>MD Ads inteligente</b><small>Impulsa tus ventas con IA</small></span></div>
                <a class="activo" href="#vista-general"><i class="bi bi-grid"></i>Vista general</a>
                <a href="{{ route('management.campaigns') }}"><i class="bi bi-megaphone"></i>Campañas</a>
                <a href="#reportes"><i class="bi bi-bar-chart"></i>Reportes</a>
                <a href="#recomendaciones"><i class="bi bi-stars"></i>Insights</a>
                <div class="publicidad-convierte"><i class="bi bi-graph-up-arrow"></i><b>Convierte más.<br>Vende inteligente.</b><small>IA + Datos + Resultados</small></div>
            </aside>

            <main class="publicidad-ia-contenido" id="vista-general">
                <header>
                    <div>
                        <span>Inteligencia Artificial para tu negocio</span>
                        <h1>MD Ads inteligente<br><em>SmartCommerce</em></h1>
                        <p>Convierte clics en clientes. Analiza, optimiza y haz crecer tu negocio con los datos de tus campañas.</p>
                    </div>
                    <div class="publicidad-ia-acciones">
                        <a class="btn btn-primary" href="{{ route('management.campaigns') }}"><i class="bi bi-plus-lg"></i> Crear campaña</a>
                        <button type="button" disabled title="Próxima iteración"><i class="bi bi-bar-chart"></i> Optimizar anuncios</button>
                        <button type="button" disabled title="Próxima iteración"><i class="bi bi-file-text"></i> Generar reporte IA</button>
                    </div>
                </header>

                <div class="publicidad-metricas">
                    <article><i class="bi bi-megaphone"></i><div><span>Campañas activas</span><b>{{ $active }}</b><small>En ejecución</small></div></article>
                    <article><i class="bi bi-eye"></i><div><span>Impresiones</span><b>{{ number_format($views) }}</b><small>Vistas registradas</small></div></article>
                    <article><i class="bi bi-cursor"></i><div><span>Clics</span><b>{{ number_format($clicks) }}</b><small>Interacciones</small></div></article>
                    <article><i class="bi bi-graph-up-arrow"></i><div><span>CTR</span><b>{{ number_format($ctr, 1) }}%</b><small>Rendimiento global</small></div></article>
                </div>

                <div class="publicidad-rejilla">
                    <section class="panel"><div class="titulo-panel"><h2>Ventas atribuidas por día</h2><button disabled>Ventas atribuidas <i class="bi bi-chevron-down"></i></button></div><p>La serie diaria estará disponible cuando exista historial de atribución entre campañas, carrito y compras.</p></section>
                    <section class="panel embudo-publicidad"><h2>Conversión del embudo</h2><p>Próxima iteración: todavía no existe atribución entre campañas, carrito y compras.</p></section>
                    <aside class="panel" id="recomendaciones"><h2><i class="bi bi-stars"></i> Insights con IA</h2><p>El módulo queda preparado para incorporar recomendaciones con datos históricos.</p></aside>
                </div>

                <div class="publicidad-tablas" id="reportes">
                    <section class="panel"><h2>Productos destacados por ventas</h2><p>Requiere atribución de ventas por campaña.</p></section>
                    <section class="panel"><h2>Top campañas</h2><ol>@forelse($campaigns->take(6) as $campaign)<li>{{ $campaign->nombre }} <b>{{ (int) $campaign->clics }} clics</b></li>@empty<li>No hay campañas registradas.</li>@endforelse</ol></section>
                    <section class="panel"><h2>Recomendaciones de la IA</h2><p>Funcionalidad preparada para una siguiente etapa.</p><button class="btn btn-primary" type="button" disabled>Próxima iteración</button></section>
                </div>
            </main>
        </div>
    </section>
@endsection
