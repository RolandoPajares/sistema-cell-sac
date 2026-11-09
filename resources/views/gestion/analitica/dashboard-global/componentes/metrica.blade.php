{{-- Componente de analitica: dashboard-global\componentes\metrica.blade.php. Renderiza un bloque visual reutilizable. --}}
{{-- Bloque visual: presenta una métrica, su icono, valor y descripción. --}}
<article class="tc-dg__metric tc-dg__metric--{{ $tono ?? 'azul' }}">
    <span class="tc-dg__metric-icon"><i class="bi {{ $icono }}" aria-hidden="true"></i></span>
    <div>
        <small>{{ $titulo }}</small>
        <strong>{{ $valor ?? '—' }}</strong>
        <span>{{ $detalle ?? 'Pendiente de datos integrados' }}</span>
    </div>
</article>
