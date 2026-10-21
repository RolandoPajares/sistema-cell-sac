
@extends('layouts.store')

@section('body_class', 'modulo-catalog')
@section('title', 'Catálogo | MD Technology Cell')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/publico/catalogo.css') }}">
@endpush

@section('content')
@php
    $baseFilters = request()->except('page');
    $selectedCategory = request('categoria');
    $selectedBrand = request('marca');
@endphp

<section class="catalog-page">
    <div class="container">

        <nav class="catalog-breadcrumbs" aria-label="Ruta de navegación">
            <a href="{{ route('home') }}">Inicio</a>
            <span aria-hidden="true">›</span>
            <span aria-current="page">Catálogo</span>
        </nav>

        <div class="catalog-layout">

            {{-- FILTROS DE ESCRITORIO Y MÓVIL --}}
            <details class="catalog-filters-container" open>
                <summary class="catalog-mobile-filter-toggle">
                    <span class="catalog-mobile-filter-label">
                        <i class="bi bi-sliders" aria-hidden="true"></i>
                        Filtros y categorías
                    </span>
                    <i class="bi bi-chevron-down catalog-filter-chevron" aria-hidden="true"></i>
                </summary>

                <aside class="catalog-sidebar" aria-label="Filtros del catálogo">

                    <section class="catalog-filter-section">
                        <h2>Categorías</h2>

                        <nav class="catalog-filter-list" aria-label="Categorías">
                            <a class="{{ $selectedCategory ? '' : 'is-active' }}"
                               href="{{ route('catalog.index', collect($baseFilters)->except('categoria')->all()) }}">
                                Todas las categorías
                            </a>

                            @foreach($categories as $category)
                                <a class="{{ (string) $selectedCategory === (string) $category->id ? 'is-active' : '' }}"
                                   href="{{ route('catalog.index', array_merge($baseFilters, ['categoria' => $category->id])) }}">
                                    {{ $category->nombre }}
                                </a>
                            @endforeach
                        </nav>
                    </section>

                    <section class="catalog-filter-section">
                        <h2>Marca</h2>

                        <nav class="catalog-filter-list" aria-label="Marcas">
                            <a class="{{ $selectedBrand ? '' : 'is-active' }}"
                               href="{{ route('catalog.index', collect($baseFilters)->except('marca')->all()) }}">
                                Todas las marcas
                            </a>

                            @foreach($brands as $brand)
                                <a class="{{ $selectedBrand === $brand ? 'is-active' : '' }}"
                                   href="{{ route('catalog.index', array_merge($baseFilters, ['marca' => $brand])) }}">
                                    {{ $brand }}
                                </a>
                            @endforeach
                        </nav>
                    </section>

                    <form class="catalog-price-filter"
                          method="get"
                          action="{{ route('catalog.index') }}">

                        <h2>Rango de precio</h2>

                        <input type="hidden" name="q" value="{{ $query }}">
                        <input type="hidden" name="marca" value="{{ $selectedBrand }}">
                        <input type="hidden" name="categoria" value="{{ $selectedCategory }}">
                        <input type="hidden" name="orden" value="{{ request('orden') }}">

                        <label for="catalog-min-price">Mínimo</label>
                        <input class="input"
                               id="catalog-min-price"
                               name="min"
                               type="number"
                               min="0"
                               step="0.01"
                               value="{{ request('min') }}">

                        <label for="catalog-max-price">Máximo</label>
                        <input class="input"
                               id="catalog-max-price"
                               name="max"
                               type="number"
                               min="0"
                               step="0.01"
                               value="{{ request('max') }}">

                        <button class="btn btn-ghost" type="submit">
                            Aplicar
                        </button>
                    </form>

                </aside>
            </details>

            {{-- CONTENIDO DEL CATÁLOGO --}}
            <div class="catalog-content">

                <div class="catalog-toolbar">

                    <div class="catalog-heading">
                        <h1>
                            {{ $selectedCategory ? $categories->firstWhere('id', (int) $selectedCategory)?->nombre : 'Catálogo' }}
                        </h1>
                        <p>Encuentra el celular ideal para ti.</p>
                    </div>

                    <form id="catalog-search-form"
                          class="catalog-search"
                          method="get"
                          action="{{ route('catalog.index') }}"
                          role="search">

                        <input type="hidden" name="marca" value="{{ $selectedBrand }}">
                        <input type="hidden" name="categoria" value="{{ $selectedCategory }}">
                        <input type="hidden" name="min" value="{{ request('min') }}">
                        <input type="hidden" name="max" value="{{ request('max') }}">

                        <label class="visually-hidden" for="catalog-search-input">
                            Buscar productos
                        </label>

                        <input class="input"
                               id="catalog-search-input"
                               name="q"
                               value="{{ $query }}"
                               placeholder="Buscar celulares, marcas o modelos...">

                        <button class="btn btn-primary"
                                type="submit"
                                aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>

                        <label class="visually-hidden" for="catalog-sort">
                            Ordenar por
                        </label>

                        <select id="catalog-sort" name="orden" data-catalog-sort>
                            <option value="nuevo" @selected(request('orden') === 'nuevo')>
                                Más recientes
                            </option>
                            <option value="precio_asc" @selected(request('orden') === 'precio_asc')>
                                Menor precio
                            </option>
                            <option value="precio_desc" @selected(request('orden') === 'precio_desc')>
                                Mayor precio
                            </option>
                            <option value="nombre" @selected(request('orden') === 'nombre')>
                                Nombre
                            </option>
                        </select>
                    </form>
                </div>

                {{-- TARJETAS DE PRODUCTOS --}}
                <div class="product-grid catalog-product-grid">

                    @forelse($products as $product)
                        @php
                            $imagePath = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
                            $imagePath = $imagePath ? ltrim(preg_replace('~^assets/~', '', $imagePath), '/') : null;

                            $salePrice = (float) ($product->precio_oferta ?? $product->precio);
                            $originalPrice = (float) ($product->precio_original ?? $product->precio);

                            $discount = $originalPrice > $salePrice && $originalPrice > 0
                                ? round((1 - ($salePrice / $originalPrice)) * 100)
                                : 0;
                        @endphp

                        <article class="product-card catalog-product-card">

                            <div class="product-art catalog-product-art">

                                @if($product->etiqueta || $discount > 0)
                                    <span class="badge">
                                        {{ $product->etiqueta ?: 'Oferta' }}
                                    </span>
                                @endif

                                @if($imagePath)
                                    <img class="catalog-product-photo"
                                         src="{{ asset('assets/cell-legacy/'.$imagePath) }}"
                                         alt="{{ $product->marca }} {{ $product->nombre }}"
                                         loading="lazy">
                                @else
                                    <div class="catalog-accessory-shape"
                                         role="img"
                                         aria-label="Imagen no disponible">
                                        <i class="bi bi-box-seam"></i>
                                    </div>
                                @endif

                            </div>

                            <div class="product-body catalog-product-body">

                                <div class="catalog-product-meta">
                                    <span>{{ $product->marca }}</span>
                                    <span>{{ $product->category?->nombre ?: $product->categoria }}</span>
                                </div>

                                <h2>{{ $product->nombre }}</h2>

                                <div class="catalog-product-specs">
                                    {{ trim(($product->almacenamiento ? $product->almacenamiento.' · ' : '').\Illuminate\Support\Str::limit(strip_tags((string) $product->descripcion), 38)) }}
                                </div>

                                <div class="catalog-product-pricing">
                                    @if($discount > 0)
                                        <div>
                                            <del>S/ {{ number_format($originalPrice, 2) }}</del>
                                            <span>-{{ $discount }}%</span>
                                        </div>
                                    @endif
                                </div>

                                <p class="price catalog-product-sale-price">
                                    S/ {{ number_format($salePrice, 2) }}
                                </p>

                                <div class="stock catalog-product-stock">
                                    {{ $product->existencias > 0 ? $product->existencias.' unidades disponibles' : 'Agotado' }}
                                </div>

                                <div class="catalog-product-actions">

                                    @auth
                                        <form action="{{ route('cart.add', $product) }}" method="post">
                                            @csrf

                                            <input type="hidden" name="cantidad" value="1">

                                            <button class="btn btn-primary catalog-buy-button"
                                                    type="submit"
                                                    @disabled($product->existencias <= 0)>
                                                <i class="bi bi-cart3" aria-hidden="true"></i>
                                                {{ $product->existencias > 0 ? 'Comprar' : 'Agotado' }}
                                            </button>
                                        </form>
                                    @else
                                        <a class="btn btn-primary catalog-buy-button"
                                           href="{{ route('login') }}">
                                            <i class="bi bi-cart3" aria-hidden="true"></i>
                                            Comprar
                                        </a>
                                    @endauth

                                    <a class="btn btn-ghost catalog-details-button"
                                       href="{{ route('catalog.show', $product) }}">
                                        Ver detalles
                                    </a>

                                </div>
                            </div>
                        </article>

                    @empty
                        <div class="empty panel catalog-empty">
                            No encontramos productos con esos filtros.
                        </div>
                    @endforelse

                </div>

                {{-- PAGINACIÓN PERSONALIZADA --}}
                @if($products->hasPages())
                    <nav class="catalog-pagination" aria-label="Paginación del catálogo">

                        @if($products->onFirstPage())
                            <span class="catalog-page-arrow is-disabled" aria-hidden="true">
                                <i class="bi bi-chevron-left"></i>
                            </span>
                        @else
                            <a class="catalog-page-arrow"
                               href="{{ $products->previousPageUrl() }}"
                               aria-label="Página anterior">
                                <i class="bi bi-chevron-left"></i>
                            </a>
                        @endif

                        <div class="catalog-page-numbers">

                            @for($page = 1; $page <= $products->lastPage(); $page++)

                                @if($page == $products->currentPage())
                                    <span class="catalog-page-dot is-current"
                                          aria-label="Página {{ $page }}, actual"
                                          aria-current="page">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a class="catalog-page-dot"
                                       href="{{ $products->url($page) }}"
                                       aria-label="Ir a la página {{ $page }}">
                                        <span class="catalog-dot-square" aria-hidden="true"></span>
                                    </a>
                                @endif

                            @endfor

                        </div>

                        @if($products->hasMorePages())
                            <a class="catalog-page-arrow"
                               href="{{ $products->nextPageUrl() }}"
                               aria-label="Página siguiente">
                                <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="catalog-page-arrow is-disabled" aria-hidden="true">
                                <i class="bi bi-chevron-right"></i>
                            </span>
                        @endif

                    
</nav>

<p class="catalog-pagination-info"
   style="text-align:center;
          color:#63718a;
          font-size:14px;
          margin-top:4px;">
    Página {{ $products->currentPage() }}
    de {{ $products->lastPage() }}
</p>

@endif


            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const filtros = document.querySelector(
        '.catalog-filters-container'
    );

    if (!filtros) return;

    const pantalla = window.matchMedia('(min-width: 761px)');

    function ajustarFiltros() {

        if (pantalla.matches) {
            // TABLET Y LAPTOP:
            // Los filtros siempre abiertos
            filtros.open = true;

        } else {
            // MOVIL:
            // Los filtros empiezan cerrados
            filtros.open = false;
        }
    }

    // Ejecutar al cargar la pagina
    ajustarFiltros();

    // Ejecutar al cambiar entre movil y escritorio
    pantalla.addEventListener('change', ajustarFiltros);

});
</script>

@endsection
