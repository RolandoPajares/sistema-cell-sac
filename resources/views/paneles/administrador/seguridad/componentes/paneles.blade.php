<div class="tc-sec__panels">
    @foreach ($paneles as $panel)
        <article class="tc-sec__card"><div class="tc-sec__card-heading"><h2><i class="bi {{ $panel['icono'] ?? 'bi-bar-chart' }}" aria-hidden="true"></i>{{ $panel['titulo'] }}</h2></div>
            @if (($panel['tipo'] ?? 'barras') === 'anillo')
                <div class="tc-sec__donut-layout"><div class="tc-sec__donut" style="--tc-sec-donut: {{ $panel['gradiente'] ?? 'conic-gradient(#1285f9 0 35%, #61d981 35% 58%, #a37afa 58% 80%, #ff789a 80% 100%)' }}"><span><strong>{{ $panel['valor'] }}</strong><small>{{ $panel['unidad'] ?? 'Total' }}</small></span></div><ul class="tc-sec__legend">@foreach ($panel['filas'] as $item)<li><i></i><span>{{ $item[0] }}</span><strong>{{ $item[1] }}</strong></li>@endforeach</ul></div>
            @elseif (($panel['tipo'] ?? 'barras') === 'lista')
                <ul class="tc-sec__activity">@foreach ($panel['filas'] as $item)<li><span class="tc-sec__avatar">{{ $item[0] }}</span><strong>{{ $item[1] }}</strong><small>{{ $item[2] ?? '' }}</small></li>@endforeach</ul>
            @else
                <div class="tc-sec__bars">@foreach ($panel['filas'] as $item)<div><span>{{ $item[0] }}</span><i><b style="width: {{ $item[2] ?? 60 }}%"></b></i><strong>{{ $item[1] }}</strong></div>@endforeach</div>
            @endif
        </article>
    @endforeach
</div>
