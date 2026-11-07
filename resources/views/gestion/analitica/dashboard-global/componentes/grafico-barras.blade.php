<div class="tc-dg__bars" role="img" aria-label="{{ $descripcion }}">
    @foreach ($barras as $barra)
        <div class="tc-dg__bar-column">
            <strong>{{ $barra['valor'] }}</strong>
            <span class="tc-dg__bar tc-dg__bar--{{ $barra['tono'] ?? 'azul' }}" style="height: {{ $barra['altura'] }}%"></span>
            <small>{{ $barra['etiqueta'] }}</small>
        </div>
    @endforeach
</div>
