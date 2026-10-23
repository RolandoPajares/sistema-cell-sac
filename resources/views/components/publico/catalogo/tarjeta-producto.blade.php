@props(['product'])

@php
    $imagePath = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
    $imagePath = $imagePath ? ltrim(preg_replace('~^assets/~', '', $imagePath), '/') : null;
    $salePrice = (float) ($product->precio_oferta ?? $product->precio);
    $originalPrice = (float) ($product->precio_original ?? $product->precio);
    $discount = $originalPrice > $salePrice && $originalPrice > 0 ? round((1 - ($salePrice / $originalPrice)) * 100) : 0;
@endphp

<article class="product-card catalog-product-card">
    <div class="product-art catalog-product-art">
        @if($product->etiqueta || $discount > 0)<span class="badge">{{ $product->etiqueta ?: 'Oferta' }}</span>@endif
        @if($imagePath)<img class="catalog-product-photo" src="{{ asset('assets/cell-legacy/'.$imagePath) }}" alt="{{ $product->marca }} {{ $product->nombre }}" loading="lazy">@else<div class="catalog-accessory-shape" role="img" aria-label="Imagen no disponible"><i class="bi bi-box-seam"></i></div>@endif
    </div>
    <div class="product-body catalog-product-body">
        <div class="catalog-product-meta"><span>{{ $product->marca }}</span><span>{{ $product->category?->nombre ?: $product->categoria }}</span></div>
        <h2>{{ $product->nombre }}</h2>
        <div class="catalog-product-specs">{{ trim(($product->almacenamiento ? $product->almacenamiento.' · ' : '').\Illuminate\Support\Str::limit(strip_tags((string) $product->descripcion), 38)) }}</div>
        <div class="catalog-product-pricing">@if($discount > 0)<div><del>S/ {{ number_format($originalPrice, 2) }}</del><span>-{{ $discount }}%</span></div>@endif</div>
        <p class="price catalog-product-sale-price">S/ {{ number_format($salePrice, 2) }}</p>
        <div class="stock catalog-product-stock">{{ $product->existencias > 0 ? $product->existencias.' unidades disponibles' : 'Agotado' }}</div>
        <div class="catalog-product-actions">
            @auth
                <form action="{{ route('cart.add', $product) }}" method="post">@csrf<input type="hidden" name="cantidad" value="1"><button class="btn btn-primary catalog-buy-button" type="submit" @disabled($product->existencias <= 0)><i class="bi bi-cart3" aria-hidden="true"></i> {{ $product->existencias > 0 ? 'Comprar' : 'Agotado' }}</button></form>
            @else
                <a class="btn btn-primary catalog-buy-button" href="{{ route('login') }}"><i class="bi bi-cart3" aria-hidden="true"></i> Comprar</a>
            @endauth
            <a class="btn btn-ghost catalog-details-button" href="{{ route('catalog.show', $product) }}">Ver detalles</a>
        </div>
    </div>
</article>
