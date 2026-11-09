{{-- Analitica: reportes-financieros\listar.blade.php. Presenta datos preparados por el controlador. --}}
{{-- Bloque principal: ofrece un acceso visual al reporte financiero preparado desde la capa de aplicación. --}}
@extends('layouts.panel.principal')

@section('title', 'Reportes financieros')
@section('body_class', 'modulo-reportes-financieros')

@section('content')
{{-- Vista de compatibilidad para conservar la plantilla heredada del módulo. --}}
<section class="admin-analysis-module admin-analysis-module--reportes-financieros" aria-labelledby="reportes-financieros-title">
    {{-- El reporte real se consulta desde la pantalla unificada de reportes. --}}
    <header class="admin-analysis-header">
        <div>
            <span class="admin-analysis-eyebrow"><i class="bi bi-cash-stack" aria-hidden="true"></i> Análisis y reportes</span>
            <h1 id="reportes-financieros-title">Reportes financieros</h1>
            <p>Consulta las ventas y los indicadores financieros desde el reporte global autorizado.</p>
        </div>
        <a class="admin-analysis-button admin-analysis-button--primary" href="{{ route('management.reports', ['vista' => 'resumen']) }}">Abrir reporte financiero</a>
    </header>
</section>
@endsection
