@extends('layouts.store')

@section('body_class', 'modulo-recomendador')
@section('title', 'SmartMatch')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/comercio-inteligente/recomendador.css') }}">
@endpush

@php
    $imageUrl = static function ($product): string {
        $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
        $path = ltrim((string) preg_replace('~^assets/~', '', $path ?: 'img/marca/logo.jpeg'), '/');

        return asset('assets/cell-legacy/'.$path);
    };
@endphp

@section('content')
    <section class="smartmatch-hero">
        <div class="container smartmatch-hero-grid">
            <div>
                <span class="smartmatch-etiqueta"><i class="bi bi-stars"></i> MD SmartCommerce</span>
                <h1>Encuentra tu <span>celular ideal</span> con SmartMatch</h1>
                <p>Indica tu presupuesto, para qué usarás el equipo y qué es lo más importante para ti. SmartMatch revisa el catálogo y te muestra los celulares que mejor encajan contigo.</p>
                <div class="smartmatch-pasos-mini">
                    <span><b>1</b> Presupuesto</span>
                    <span><b>2</b> Uso</span>
                    <span><b>3</b> Prioridad</span>
                </div>
            </div>
            <div class="smartmatch-hero-imagen">
                <img src="{{ asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png') }}" alt="Celulares y accesorios">
            </div>
        </div>
    </section>

    <section class="container smartmatch-contenido">
        <form class="smartmatch-form" method="get" id="buscar">
            <div class="smartmatch-form-titulo">
                <i class="bi bi-sliders"></i>
                <div>
                    <h2>Cuéntanos qué buscas</h2>
                    <p>Completa los tres datos y presiona el botón.</p>
                </div>
            </div>
            <div class="smartmatch-campos">
                <label>
                    <span><i class="bi bi-cash-coin"></i> Presupuesto máximo (S/)</span>
                    <input class="input" type="number" name="presupuesto" min="300" step="50" value="{{ request('presupuesto') }}" placeholder="Ej. 1500">
                </label>
                <label>
                    <span><i class="bi bi-phone"></i> Uso principal</span>
                    <select name="uso">
                        <option value="">General</option>
                        <option value="study" @selected(request('uso') === 'study')>Estudio</option>
                        <option value="gaming" @selected(request('uso') === 'gaming')>Gaming</option>
                        <option value="camera" @selected(request('uso') === 'camera')>Fotografía</option>
                        <option value="work" @selected(request('uso') === 'work')>Trabajo</option>
                        <option value="social" @selected(request('uso') === 'social')>Redes sociales</option>
                    </select>
                </label>
                <label>
                    <span><i class="bi bi-star"></i> Prioridad</span>
                    <select name="prioridad">
                        <option value="">Equilibrada</option>
                        <option value="valor" @selected(request('prioridad') === 'valor')>Calidad/precio</option>
                        <option value="rendimiento" @selected(request('prioridad') === 'rendimiento')>Rendimiento</option>
                        <option value="bateria" @selected(request('prioridad') === 'bateria')>Batería</option>
                        <option value="camara" @selected(request('prioridad') === 'camara')>Cámara</option>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Encontrar mi celular</button>
            </div>
        </form>

        <div class="smartmatch-como">
            <article><i class="bi bi-wallet2"></i><h3>1. Tu presupuesto</h3><p>Solo se muestran equipos que no pasan el monto que indicas.</p></article>
            <article><i class="bi bi-controller"></i><h3>2. Tu forma de usarlo</h3><p>Estudio, juegos, fotos, trabajo o redes: cada uso tiene sus puntos fuertes.</p></article>
            <article><i class="bi bi-graph-up-arrow"></i><h3>3. Tu coincidencia</h3><p>Cada celular recibe un porcentaje para ayudarte a decidir.</p></article>
        </div>

        <div class="smartmatch-titulo-resultados">
            <h2>Te recomendamos estos equipos</h2>
            <p>Ordenados de mayor a menor coincidencia.</p>
        </div>

        @if ($products->isNotEmpty())
            <div class="smartmatch-resultados">
                @foreach ($products as $product)
                    <article class="smartmatch-tarjeta">
                        <span class="smartmatch-puesto">#{{ $loop->iteration }}</span>
                        <img class="smartmatch-product-photo" src="{{ $imageUrl($product) }}" alt="{{ $product->marca }} {{ $product->nombre }}" loading="lazy">
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
                @endforeach
            </div>
        @else
            <div class="smartmatch-vacio">
                <i class="bi bi-emoji-neutral"></i>
                <h2>No encontramos equipos para esa búsqueda</h2>
                <p>Prueba con un presupuesto un poco más alto o cambia la prioridad. También puedes revisar todo el catálogo.</p>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}">Ver catálogo</a>
            </div>
        @endif
    </section>
@endsection
