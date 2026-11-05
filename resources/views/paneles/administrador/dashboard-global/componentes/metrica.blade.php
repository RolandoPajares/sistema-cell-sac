<article class="tc-dg__metric tc-dg__metric--{{ $tono ?? 'azul' }}">
    <span class="tc-dg__metric-icon"><i class="bi {{ $icono }}" aria-hidden="true"></i></span>
    <div>
        <small>{{ $titulo }}</small>
        <strong>{{ $valor ?? '—' }}</strong>
        <span>{{ $detalle ?? 'Pendiente de datos integrados' }}</span>
    </div>
</article>
