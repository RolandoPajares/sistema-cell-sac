{{-- Analitica: clientes\listar.blade.php. Presenta datos preparados por el controlador. --}}
{{-- Bloque principal: ofrece el acceso al reporte analítico de clientes. --}}
@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Analítica de clientes')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-analitica-clientes')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- Vista de compatibilidad para conservar la plantilla heredada del módulo. --}}
<section class="admin-analysis-module admin-analysis-module--analitica-clientes" aria-labelledby="analitica-clientes-title">
    {{-- El reporte real se consulta desde la pantalla unificada de reportes. --}}
    <header class="admin-analysis-header">
        <div>
            <span class="admin-analysis-eyebrow"><i class="bi bi-people" aria-hidden="true"></i> Análisis y reportes</span>
            <h1 id="analitica-clientes-title">Analítica de clientes</h1>
            <p>Consulta la información real de clientes desde el reporte global autorizado.</p>
        </div>
        <a class="admin-analysis-button admin-analysis-button--primary" href="{{ route('management.reports', ['vista' => 'clientes']) }}">Abrir reporte de clientes</a>
    </header>
</section>
@endsection
