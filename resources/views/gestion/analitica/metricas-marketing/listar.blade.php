{{-- Analitica: metricas-marketing\listar.blade.php. Presenta datos preparados por el controlador. --}}
{{-- Bloque principal: presenta el acceso a las métricas de marketing disponibles. --}}
@extends('layouts.panel.principal')

@section('title', 'Métricas de marketing')
@section('body_class', 'modulo-metricas-marketing')

@section('content')
{{-- Vista de compatibilidad para conservar la plantilla heredada del módulo. --}}
<section class="admin-analysis-module admin-analysis-module--metricas-marketing" aria-labelledby="metricas-marketing-title">
    {{-- El reporte real se consulta desde la pantalla unificada de reportes. --}}
    <header class="admin-analysis-header">
        <div>
            <span class="admin-analysis-eyebrow"><i class="bi bi-megaphone" aria-hidden="true"></i> Análisis y reportes</span>
            <h1 id="metricas-marketing-title">Métricas de marketing</h1>
            <p>Consulta las campañas y sus indicadores desde el reporte global autorizado.</p>
        </div>
        <a class="admin-analysis-button admin-analysis-button--primary" href="{{ route('management.reports', ['vista' => 'campanas']) }}">Abrir reporte de campañas</a>
    </header>
</section>
@endsection
