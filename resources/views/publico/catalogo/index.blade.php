@extends('layouts.tienda')

@section('body_class', 'modulo-catalog')
@section('title', 'Catálogo | MD Technology Cell')

@section('content')
@php
    $baseFilters = request()->except('page');
    $selectedCategory = request('categoria');
    $selectedBrand = request('marca');
@endphp
<section class="catalog-page">
    <div class="container">
        <nav class="catalog-breadcrumbs" aria-label="Ruta de navegación">
            <a href="{{ route('home') }}">Inicio</a><span aria-hidden="true">›</span><span aria-current="page">Catálogo</span>
        </nav>

        <div class="catalog-layout">
            <aside class="catalog-sidebar" aria-label="Filtros del catálogo">
                <section class="catalog-filter-section">
                    <h2>Categorías</h2>
                    <nav class="catalog-filter-list" aria-label="Categorías">
                        <a class="{{ $selectedCategory ? '' : 'is-active' }}" href="{{ route('catalog.index', collect($baseFilters)->except('categoria')->all()) }}">Todas las categorías</a>
                        @foreach($categories as $category)
                            <a class="{{ (string) $selectedCategory === (string) $category->id ? 'is-active' : '' }}" href="{{ route('catalog.index', array_merge($baseFilters, ['categoria' => $category->id])) }}">{{ $category->nombre }}</a>
                        @endforeach
                    </nav>
                </section>

                <section class="catalog-filter-section">
                    <h2>Marca</h2>
                    <nav class="catalog-filter-list" aria-label="Marcas">
                        <a class="{{ $selectedBrand ? '' : 'is-active' }}" href="{{ route('catalog.index', collect($baseFilters)->except('marca')->all()) }}">Todas las marcas</a>
                        @foreach($brands as $brand)
                            <a class="{{ $selectedBrand === $brand ? 'is-active' : '' }}" href="{{ route('catalog.index', array_merge($baseFilters, ['marca' => $brand])) }}">{{ $brand }}</a>
                        @endforeach
                    </nav>
                </section>

                <form class="catalog-price-filter" method="get" action="{{ route('catalog.index') }}">
                    <h2>Rango de precio</h2>
                    <input type="hidden" name="q" value="{{ $query }}">
                    <input
                        type="hidden"
                        name="marca"
                        value="{{ $selectedBrand }}"
                    >
                    <input
                        type="hidden"
                        name="categoria"
                        value="{{ $selectedCategory }}"
                    >
                    <input
                        type="hidden"
                        name="orden"
                        value="{{ request('orden') }}"
                    >
                    <label for="catalog-min-price">Mínimo</label>
                    <input
                        class="input"
                        id="catalog-min-price"
                        name="min"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ request('min') }}"
                    >
                    <label for="catalog-max-price">Máximo</label>
                    <input
                        class="input"
                        id="catalog-max-price"
                        name="max"
                        type="number"
                        min="0"
                        step="0.01"
                        value="{{ request('max') }}"
                    >
                    <button class="btn btn-ghost" type="submit">Aplicar</button>
                </form>
            </aside>

            <div class="catalog-content">
                <div class="catalog-toolbar">
                    <div class="catalog-heading"><h1>{{ $selectedCategory ? $categories->firstWhere('id', (int) $selectedCategory)?->nombre : 'Catálogo' }}</h1><p>Encuentra el celular ideal para ti.</p></div>
                    <form id="catalog-search-form" class="catalog-search" method="get" action="{{ route('catalog.index') }}" role="search">
                        <input type="hidden" name="marca" value="{{ $selectedBrand }}">
                        <input type="hidden" name="categoria" value="{{ $selectedCategory }}">
                        <input type="hidden" name="min" value="{{ request('min') }}">
                        <input type="hidden" name="max" value="{{ request('max') }}">
                        <label class="visually-hidden" for="catalog-search-input">Buscar productos</label>
                        <input
                            class="input"
                            id="catalog-search-input"
                            name="q"
                            value="{{ $query }}"
                            placeholder="Buscar celulares, marcas o modelos..."
                        >
                        <button class="btn btn-primary" type="submit" aria-label="Buscar">
                            <i class="bi bi-search" aria-hidden="true"></i>
                        </button>
                        <label class="visually-hidden" for="catalog-sort">Ordenar por</label>
                        <select id="catalog-sort" name="orden" data-catalog-sort>
                            <option value="nuevo" @selected(request('orden') === 'nuevo')>Más recientes</option>
                            <option value="precio_asc" @selected(request('orden') === 'precio_asc')>Menor precio</option>
                            <option value="precio_desc" @selected(request('orden') === 'precio_desc')>Mayor precio</option>
                            <option value="nombre" @selected(request('orden') === 'nombre')>Nombre</option>
                        </select>
                    </form>
                </div>

                <div class="product-grid catalog-product-grid">
                    @forelse($products as $product)
                        <x-publico.catalogo.tarjeta-producto :product="$product" />
                    @empty
                        <div class="empty panel catalog-empty">No encontramos productos con esos filtros.</div>
                    @endforelse
                </div>
                <nav class="pagination" aria-label="Paginación">{{ $products->links() }}</nav>
            </div>
        </div>
    </div>
</section>
@endsection
