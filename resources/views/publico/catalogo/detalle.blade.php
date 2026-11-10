@extends('layouts.tienda.principal')

@section('body_class', 'modulo-producto')
@section('title', $product->nombre.' | MD Technology Cell')

@section('content')
<section class="product-page-light" data-product-detail data-product-id="{{ $product->id }}" data-variant-data='@json($variantData)' data-authenticated="{{ $isAuthenticated ? '1' : '0' }}" data-login-url="{{ route('login') }}" data-favorite-url="{{ route('catalog.favorite', $product) }}" data-review-url="{{ route('catalog.review.store', $product) }}" data-favorite-initial="{{ $isFavorite ? '1' : '0' }}" data-rating-initial="{{ $userRating }}">
    <div class="container product-detail-container">
        <nav class="product-breadcrumbs" aria-label="Ruta de navegación">
            <a href="{{ route('home') }}"><i class="bi bi-house" aria-hidden="true"></i> Inicio</a><span aria-hidden="true">/</span>
            <a href="{{ route('catalog.index') }}">Catálogo</a><span aria-hidden="true">/</span>
            <a href="{{ route('catalog.index', ['categoria' => $product->categoria_id]) }}">{{ $categoryName }}</a><span aria-hidden="true">/</span>
            <span aria-current="page">{{ $product->nombre }}</span>
        </nav>

        <div class="product-detail-modern">
            <div class="detail-gallery" aria-label="Imágenes del producto">
                <div class="gallery-thumbs" data-gallery-thumbs @if(!$primaryImage) hidden @endif>
                    @foreach($imageItems as $index => $image)
                        <button class="gallery-thumb {{ $index === 0 ? 'active' : '' }}" type="button" data-src="{{ $image['url'] }}" aria-label="Ver imagen {{ $index + 1 }}" @if($index === 0) aria-current="true" @endif><img src="{{ $image['url'] }}" alt="" loading="lazy"></button>
                    @endforeach
                </div>
                <div class="detail-main-image" data-zoom-source>
                    @if($primaryImage)<img data-main-product-image src="{{ $primaryImage }}" alt="{{ $product->marca }} {{ $product->nombre }}">@endif
                    <div class="detail-product-placeholder detail-product-placeholder--{{ $visualType }}" data-image-placeholder @if($primaryImage) hidden @endif role="img" aria-label="Imagen no disponible"><span class="detail-product-icon"><strong>{{ $product->marca }}</strong><i class="bi {{ $visualType === 'audio' ? 'bi-headphones' : ($visualType === 'celular' ? 'bi-phone' : 'bi-box-seam') }}" aria-hidden="true"></i></span><span>Imagen no disponible</span></div>
                    <div class="detail-gallery-actions"><button type="button" class="detail-icon-button" data-favorite-toggle aria-pressed="false" title="Añadir a favoritos"><i class="bi bi-heart" aria-hidden="true"></i><span class="visually-hidden" data-favorite-label>Añadir a favoritos</span></button><button type="button" class="detail-icon-button" data-zoom-open title="Ampliar imagen"><i class="bi bi-zoom-in" aria-hidden="true"></i><span class="visually-hidden">Ampliar imagen</span></button></div>
                    <button class="gallery-arrow prev" type="button" data-gallery-prev aria-label="Imagen anterior" hidden><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button class="gallery-arrow next" type="button" data-gallery-next aria-label="Imagen siguiente" hidden><i class="bi bi-chevron-right" aria-hidden="true"></i></button>
                    <span class="gallery-counter" data-gallery-counter>{{ $imageItems->count() }} imágenes</span>
                </div>
            </div>

            <div class="detail-info">
                <div class="product-detail-meta"><strong>{{ $product->marca }}</strong><span>{{ $categoryName }}</span>@if($product->etiqueta)<span class="product-detail-badge">{{ $product->etiqueta }}</span>@endif</div>
                <h1>{{ $product->nombre }}</h1>
                <div class="product-detail-rating"><div class="rating-stars" data-rating-widget role="radiogroup" aria-label="Valorar producto">@foreach($ratingOptions as $rating)<button type="button" data-rating-value="{{ $rating['value'] }}" role="radio" aria-checked="{{ $rating['selected'] ? 'true' : 'false' }}" aria-label="{{ $rating['value'] }} de 5 estrellas"><i class="bi {{ $rating['value'] <= (int) $averageRating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i></button>@endforeach</div><span class="rating-muted" data-rating-summary>{{ $reviewCount > 0 ? 'Promedio: '.number_format($averageRating, 1).'/5 ('.$reviewCount.' reseñas)' : 'Sin valoración registrada' }}</span><span class="product-detail-sku">SKU: <strong data-current-sku>{{ $primarySku }}</strong></span></div>
                <div class="product-detail-price"><strong data-current-price>S/ {{ number_format($salePrice, 2) }}</strong>@if($discount > 0)<div class="product-detail-old-price"><del>S/ {{ number_format($originalPrice, 2) }}</del><span class="product-detail-discount">-{{ $discount }}%</span></div>@endif</div>
                <p class="product-detail-stock {{ $stockClass }}" data-current-stock data-stock="{{ $product->existencias_disponibles }}"><i class="bi {{ $stockIcon }}" aria-hidden="true"></i>{{ $stockLabel }}</p>

                @if($product->variants->isNotEmpty())
                    <fieldset class="product-variant-selector"><legend>Variante</legend><div class="variant-options" role="radiogroup" aria-label="Variantes disponibles">
                        @foreach($variantOptions as $variant)
                            <label class="variant-option {{ $variant['selected'] ? 'is-selected' : '' }} {{ $variant['class'] }}"><input type="radio" name="variante_visual" value="{{ $variant['id'] }}" data-variant-option @checked($variant['selected']) @disabled($variant['disabled'])><span>{{ $variant['name'] }}</span></label>
                        @endforeach
                    </div></fieldset>
                @endif

                <form class="product-detail-cart" action="{{ route('cart.add', $product) }}" method="post" data-product-form>
                    @csrf
                    <input type="hidden" name="variante_id" value="{{ $firstVariant?->id }}" data-selected-variant>
                    <div class="quantity-control"><label for="cantidad-producto">Cantidad</label><div><button type="button" data-quantity-minus aria-label="Disminuir cantidad">−</button><input id="cantidad-producto" type="number" name="cantidad" value="1" min="1" max="{{ max(1, $product->existencias_disponibles) }}" inputmode="numeric" data-quantity-input><button type="button" data-quantity-plus aria-label="Aumentar cantidad">+</button></div></div>
                    @auth
                        <div class="product-detail-actions"><button class="product-detail-buy product-detail-buy--primary" type="submit" @disabled($product->existencias_disponibles <= 0) data-add-to-cart><i class="bi bi-cart3" aria-hidden="true"></i> Agregar al carrito</button><button class="product-detail-buy product-detail-buy--success" type="submit" name="comprar_ahora" value="1" @disabled($product->existencias_disponibles <= 0)><i class="bi bi-lightning-charge" aria-hidden="true"></i> Comprar ahora</button></div>
                    @else
                        <a class="product-detail-buy product-detail-buy--success" href="{{ route('login') }}"><i class="bi bi-cart3" aria-hidden="true"></i> Comprar</a>
                    @endauth
                    <p class="product-detail-form-message" data-form-message role="status" hidden></p>
                </form>
                <button type="button" class="product-favorite-disabled" data-favorite-toggle aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"><i class="bi {{ $favoriteIcon }}" aria-hidden="true"></i> <span data-favorite-label>{{ $favoriteLabel }}</span><small data-favorite-status>{{ $favoriteStatus }}</small></button>
                <div class="product-trust-grid"><div><i class="bi bi-shield-check" aria-hidden="true"></i><span><strong>Producto original</strong><small>Información del catálogo</small></span></div><div><i class="bi bi-lock" aria-hidden="true"></i><span><strong>Pago seguro</strong><small>Flujo protegido por sesión</small></span></div><div><i class="bi bi-headset" aria-hidden="true"></i><span><strong>Soporte técnico</strong><small>Atención Technology Cell</small></span></div></div>
            </div>
        </div>

        <div class="product-delivery-panel"><i class="bi bi-truck" aria-hidden="true"></i><div><strong>Entrega y disponibilidad</strong><span>Consulta la sede y el costo de envío al confirmar el pedido.</span></div><b>Configuración de envío no registrada</b></div>

        <section class="product-tabs" data-product-tabs><div class="product-tab-list" role="tablist" aria-label="Información del producto"><button type="button" role="tab" aria-selected="true" data-tab-target="description">Descripción</button><button type="button" role="tab" aria-selected="false" data-tab-target="specifications">Especificaciones</button><button type="button" role="tab" aria-selected="false" data-tab-target="reviews">Reseñas ({{ $reviewCount }})</button><button type="button" role="tab" aria-selected="false" data-tab-target="shipping">Envíos y garantía</button></div>
            <div class="product-tab-panel is-active" data-tab-panel="description" role="tabpanel"><h2>Descripción</h2>@if($product->descripcion)<p>{!! nl2br(e($product->descripcion)) !!}</p>@else<p class="empty-state">Este producto no tiene una descripción registrada.</p>@endif</div>
            <div class="product-tab-panel" data-tab-panel="specifications" role="tabpanel" hidden><h2>Especificaciones principales</h2><dl class="specification-table"><div><dt>Marca</dt><dd>{{ $product->marca }}</dd></div><div><dt>Categoría</dt><dd>{{ $categoryName }}</dd></div><div><dt>SKU</dt><dd>{{ $primarySku }}</dd></div><div><dt>Color</dt><dd>{{ $productColor }}</dd></div><div><dt>Almacenamiento / tipo</dt><dd>{{ $product->almacenamiento ?: 'No registrado' }}</dd></div>@foreach($product->features as $feature)<div><dt>{{ $feature->nombre }}</dt><dd>{{ $feature->valor }}</dd></div>@endforeach</dl></div>
            <div class="product-tab-panel" data-tab-panel="reviews" role="tabpanel" hidden><h2>Reseñas</h2><div class="review-widget"><strong>Valora este producto</strong><div class="review-widget-stars" data-review-widget role="radiogroup" aria-label="Selecciona tu valoración">@foreach($ratingOptions as $rating)<button type="button" data-rating-value="{{ $rating['value'] }}" role="radio" aria-checked="{{ $rating['selected'] ? 'true' : 'false' }}" aria-label="{{ $rating['value'] }} de 5 estrellas"><i class="bi {{ $rating['value'] <= (int) $userRating ? 'bi-star-fill' : 'bi-star' }}" aria-hidden="true"></i></button>@endforeach</div><p class="empty-state" data-review-message>{{ $reviewMessage }}</p></div></div>
            <div class="product-tab-panel" data-tab-panel="shipping" role="tabpanel" hidden><h2>Envíos y garantía</h2><p class="empty-state">No existe una configuración persistida de envío o garantía para este producto. No se muestran condiciones simuladas.</p></div>
        </section>

        <section class="related-products" aria-labelledby="related-products-title"><div class="section-head"><div><span class="eyebrow">También puedes revisar</span><h2 id="related-products-title">Productos relacionados</h2></div><a href="{{ route('catalog.index', ['categoria' => $product->categoria_id]) }}">Ver más <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>@if($relatedProducts->isNotEmpty())<div class="related-grid">@foreach($relatedProducts as $related)<article class="related-card"><a href="{{ route('catalog.show', $related) }}" class="related-card-image">@if($related->url_imagen_principal)<img src="{{ $related->url_imagen_principal }}" alt="{{ $related->marca }} {{ $related->nombre }}" loading="lazy">@else<i class="bi bi-box-seam" aria-hidden="true"></i>@endif</a><div><small>{{ $related->marca }} · {{ $related->category?->nombre ?: $related->categoria }}</small><h3><a href="{{ route('catalog.show', $related) }}">{{ $related->nombre }}</a></h3><strong>S/ {{ number_format((float) ($related->precio_oferta ?? $related->precio), 2) }}</strong><span class="related-stock {{ $related->existencias_disponibles > 0 ? 'is-available' : 'is-unavailable' }}">{{ $related->existencias_disponibles > 0 ? $related->existencias_disponibles.' disponibles' : 'Sin stock' }}</span></div></article>@endforeach</div>@else<p class="empty-state related-empty">No hay productos relacionados disponibles.</p>@endif</section>
    </div>
</section>

<dialog class="product-zoom-dialog" data-zoom-dialog aria-labelledby="product-zoom-title"><div class="product-zoom-header"><strong id="product-zoom-title">Vista ampliada</strong><button type="button" data-zoom-close aria-label="Cerrar ampliación"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div><img data-zoom-image src="{{ $primaryImage ?? '' }}" alt="{{ $product->nombre }}"></dialog>
@endsection
