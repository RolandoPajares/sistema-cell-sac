@extends('layouts.store')

@section('body_class', 'modulo-producto')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/publico/producto.css') }}">@endpush
@section('title', $product->nombre.' | MD Technology Cell')

@section('content')
@php
    $categoryName = $product->category?->nombre ?: $product->categoria;
    $imageItems = $product->images->map(function ($image): array {
        $path = ltrim((string) preg_replace('~^assets/~', '', $image->ruta_imagen), '/');
        return ['url' => asset('assets/cell-legacy/'.$path), 'path' => $path];
    });
    $primaryImage = $imageItems->first()['url'] ?? null;
    $salePrice = (float) ($product->precio_oferta ?? $product->precio);
    $originalPrice = (float) ($product->precio_original ?? $product->precio);
    $discount = $originalPrice > $salePrice && $originalPrice > 0 ? round((1 - ($salePrice / $originalPrice)) * 100) : 0;
    $visualType = str_contains(mb_strtolower((string) $categoryName), 'audio') ? 'audio' : (str_contains(mb_strtolower((string) $categoryName), 'celular') ? 'celular' : 'accesorio');
@endphp
<section class="product-page-light" data-product-detail>
    <div class="container product-detail-container">
        <nav class="product-breadcrumbs" aria-label="Ruta de navegación"><a href="{{ route('home') }}">Inicio</a><span aria-hidden="true">›</span><a href="{{ route('catalog.index') }}">Catálogo</a><span aria-hidden="true">›</span><span aria-current="page">{{ $product->nombre }}</span></nav>
        <div class="product-detail-modern">
            <div class="detail-gallery" aria-label="Imágenes del producto">
                <div class="gallery-thumbs" data-gallery-thumbs @if(!$primaryImage) hidden @endif>
                    @foreach($imageItems as $index => $image)<button class="gallery-thumb {{ $index === 0 ? 'active' : '' }}" type="button" data-src="{{ $image['url'] }}" aria-label="Ver imagen {{ $index + 1 }}" @if($index === 0) aria-current="true" @endif><img src="{{ $image['url'] }}" alt=""></button>@endforeach
                </div>
                <div class="detail-main-image">
                    @if($primaryImage)<img data-main-product-image src="{{ $primaryImage }}" alt="{{ $product->marca }} {{ $product->nombre }}">@endif
                    <div class="detail-product-placeholder detail-product-placeholder--{{ $visualType }}" data-image-placeholder @if($primaryImage) hidden @endif role="img" aria-label="Imagen no disponible"><span class="detail-product-icon"><strong>{{ $product->marca }}</strong><i class="bi {{ $visualType === 'audio' ? 'bi-headphones' : ($visualType === 'celular' ? 'bi-phone' : 'bi-box-seam') }}" aria-hidden="true"></i></span><span>{{ $product->marca }}</span></div>
                    <button class="gallery-arrow prev" type="button" data-gallery-prev aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button>
                    <button class="gallery-arrow next" type="button" data-gallery-next aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                </div>
            </div>
            <div class="detail-info">
                <div class="product-detail-heading"><div class="product-detail-meta"><span>{{ $product->marca }}</span><span>{{ $categoryName }}</span>@if($product->etiqueta)<span class="product-detail-badge">{{ $product->etiqueta }}</span>@endif</div><h1>{{ $product->nombre }}</h1></div>
                <div class="product-detail-price"><strong>S/ {{ number_format($salePrice, 2) }}</strong>@if($discount > 0)<div class="product-detail-old-price"><del>S/ {{ number_format($originalPrice, 2) }}</del><span class="product-detail-discount">-{{ $discount }}%</span></div>@endif</div>
                <p class="product-detail-stock {{ $product->existencias > 0 ? 'is-available' : 'is-unavailable' }}"><i class="bi {{ $product->existencias > 0 ? 'bi-check-circle' : 'bi-x-circle' }}" aria-hidden="true"></i>{{ $product->existencias > 0 ? $product->existencias.' unidades disponibles' : 'Sin stock' }}</p>
                @auth
                    <form class="product-detail-cart" action="{{ route('cart.add', $product) }}" method="post">@csrf<input type="hidden" name="cantidad" value="1"><button class="product-detail-buy" type="submit" @disabled($product->existencias <= 0)><i class="bi bi-cart3" aria-hidden="true"></i>{{ $product->existencias > 0 ? 'Agregar al carrito' : 'Sin stock' }}</button></form>
                @else
                    <a class="product-detail-buy" href="{{ route('login') }}"><i class="bi bi-cart3" aria-hidden="true"></i>Inicia sesión para comprar</a>
                @endauth
                @if($product->descripcion)<section class="product-detail-description"><h2>Descripción</h2><p>{!! nl2br(e($product->descripcion)) !!}</p></section>@endif
                @if($product->features->isNotEmpty())<section class="product-detail-specs"><h2>Información del producto</h2><ul>@foreach($product->features as $feature)<li><span>{{ $feature->nombre }}</span><strong>{{ $feature->valor }}</strong></li>@endforeach</ul></section>@endif
            </div>
        </div>
    </div>
</section>
@endsection
