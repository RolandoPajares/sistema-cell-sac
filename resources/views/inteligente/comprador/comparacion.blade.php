@extends('layouts.tienda.principal')

@section('body_class', 'modulo-comparador')
@section('title', 'Comparador inteligente')

@section('content')
    <section class="smart-page">
        <div class="comparator-reference-hero comparator-reference-hero--full">
            <div class="container comparator-reference-hero__inner">
                <span class="eyebrow">MD SmartCommerce</span>
                <h1><i class="bi bi-columns-gap" aria-hidden="true"></i> Comparador <span>inteligente</span></h1>
                <p>Selecciona hasta tres celulares y compara precio, disponibilidad y SmartScore por perfil.</p>
            </div>
        </div>
        <div class="container">
            <div class="comparator-steps" aria-label="Pasos del comparador"><span><b>1</b> Selecciona hasta 3 equipos</span><span><b>2</b> Compara características</span><span><b>3</b> Elige el ideal para ti</span></div>
            <form class="panel smart-form compare-picker" method="get" id="compareForm">
                @for ($slot = 1; $slot <= 3; $slot++)
                    <label>
                        Equipo {{ $slot }}
                        <select class="compare-select">
                            <option value="">-- Opcional --</option>
                            @foreach ($availableProducts as $option)
                                <option value="{{ $option->id }}" @selected((string) $selectedIds->get($slot - 1) === (string) $option->id)>
                                    {{ $option->marca }} {{ $option->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                @endfor
                <input type="hidden" name="seleccion" id="compareIds" value="{{ $selectedIds->implode(',') }}">
                <button class="btn btn-primary" type="submit">Comparar ahora</button>
            </form>

            @if ($products->isNotEmpty())
                <div class="comparison-grid">
                    @foreach ($products as $product)
<article class="panel comparison-card">
    <img class="comparison-product-photo" src="{{ $product->comparison_image }}" alt="{{ $product->marca }} {{ $product->nombre }}" loading="lazy">
    <span class="eyebrow">{{ $product->marca ?: 'CELL SAC' }}</span>
    <h2>{{ $product->nombre }}</h2>
    <div class="price">S/ {{ number_format((float) $product->effective_price, 2) }}</div>
    <p>{{ $product->category?->nombre ?: 'Equipo' }} · Stock {{ (int) $product->existencias }}</p>
    <div class="score-row">
        <span>SmartScore</span>
        <b>{{ $product->comparison_score }}/100</b>
        <div class="scorebar"><i style="width: {{ $product->comparison_score }}%"></i></div>
    </div>
    <a class="btn btn-ghost" href="{{ route('catalog.show', $product) }}">Ver detalles</a>
</article>

                    @endforeach
                </div>
            @else
                <div class="panel comparison-empty">
                    <i class="bi bi-columns-gap"></i>
                    <h2>Selecciona los equipos que quieres comparar</h2>
                    <p>Puedes elegir hasta tres productos del catálogo para revisar sus datos en paralelo.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
