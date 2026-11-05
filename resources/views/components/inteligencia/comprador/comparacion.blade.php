@props(['product'])

@php
    $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
    $path = ltrim((string) preg_replace('~^assets/~', '', $path ?: 'img/marca/logo.jpeg'), '/');
    $score = $product->intelligenceProfile?->puntuacion_valor ?? 70;
@endphp

<article class="panel comparison-card">
    <img class="comparison-product-photo" src="{{ asset('assets/cell-legacy/'.$path) }}" alt="{{ $product->marca }} {{ $product->nombre }}" loading="lazy">
    <span class="eyebrow">{{ $product->marca ?: 'CELL SAC' }}</span>
    <h2>{{ $product->nombre }}</h2>
    <div class="price">S/ {{ number_format((float) $product->effective_price, 2) }}</div>
    <p>{{ $product->category?->nombre ?: 'Equipo' }} · Stock {{ (int) $product->existencias }}</p>
    <div class="score-row">
        <span>SmartScore</span>
        <b>{{ (int) $score }}/100</b>
        <div class="scorebar"><i style="width: {{ (int) $score }}%"></i></div>
    </div>
    <a class="btn btn-ghost" href="{{ route('catalog.show', $product) }}">Ver detalles</a>
</article>
