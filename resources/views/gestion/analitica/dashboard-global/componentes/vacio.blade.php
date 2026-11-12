
{{-- Componente de analitica: dashboard-global\componentes\vacio.blade.php. Renderiza un bloque visual reutilizable. --}}
{{-- Bloque visual: muestra un estado vacío cuando no existen datos para presentar. --}}
<div class="tc-dg__empty">
    <i class="bi {{ $icono ?? 'bi-graph-up' }}" aria-hidden="true"></i>
    <strong>{{ $titulo ?? 'Sin datos disponibles' }}</strong>
    <span>{{ $descripcion ?? 'La información aparecerá cuando este módulo esté conectado a los registros reales.' }}</span>
</div>
