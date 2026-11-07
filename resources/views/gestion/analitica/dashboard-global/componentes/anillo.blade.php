<div class="tc-dg__donut-layout">
    <div class="tc-dg__donut" style="--tc-dg-donut: {{ $gradiente }}"><span><strong>{{ $centro }}</strong><small>{{ $etiqueta }}</small></span></div>
    <ul class="tc-dg__legend">
        @foreach ($leyenda as $elemento)
            <li><i style="--tc-dg-legend-color: {{ $elemento['color'] }}"></i><span>{{ $elemento['nombre'] }}</span><strong>{{ $elemento['valor'] }}</strong></li>
        @endforeach
    </ul>
</div>
