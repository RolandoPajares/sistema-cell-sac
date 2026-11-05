@props(['product', 'position'])

@php
    $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
    $path = ltrim((string) preg_replace('~^assets/~', '', $path ?: 'img/marca/logo.jpeg'), '/');
@endphp

<article class="smartmatch-tarjeta">
    <span class="smartmatch-puesto">#{{ $position }}</span>
    <img class="smartmatch-product-photo" src="{{ asset('assets/cell-legacy/'.$path) }}" alt="{{ $product->marca }} {{ $product->nombre }}" loading="lazy">
    <div class="smartmatch-tarjeta-arriba">
        <div>
            <small>{{ $product->marca ?: 'CELL SAC' }}</small>
            <h3>{{ $product->nombre }}</h3>
            <p class="smartmatch-precio">S/ {{ number_format((float) $product->effective_price, 2) }}</p>
        </div>
        <div class="smartmatch-porcentaje"><b>{{ (int) $product->recommendation_score }}%</b><small>coincide</small></div>
    </div>
    <ul>
        <li><i class="bi bi-check-circle-fill"></i> Precio dentro del catálogo vigente</li>
        <li><i class="bi bi-check-circle-fill"></i> Stock disponible: {{ $product->existencias }}</li>
        <li><i class="bi bi-check-circle-fill"></i> Puntaje calculado según tu selección</li>
    </ul>
    <div class="smartmatch-botones">
        <a class="btn btn-primary" href="{{ route('catalog.show', $product) }}">Ver equipo</a>
        <a class="btn btn-ghost" href="{{ route('comparison', ['seleccion' => $product->id]) }}">Comparar</a>
    </div>
</article>
