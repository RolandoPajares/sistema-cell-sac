@extends('layouts.tienda')

@section('body_class', 'modulo-comparador')
@section('title', 'Comparador inteligente')

@php
    $selectedIds = collect(explode(',', (string) request('seleccion')))->filter()->values();
    $imageUrl = static function ($product): string {
        $path = $product->images->first()?->ruta_imagen ?: $product->url_imagen;
        $path = ltrim((string) preg_replace('~^assets/~', '', $path ?: 'img/marca/logo.jpeg'), '/');

        return asset('assets/cell-legacy/'.$path);
    };
@endphp

@section('content')
    <section class="smart-page">
        <div class="container">
            <div class="smart-hero">
                <span class="eyebrow">MD SmartCommerce</span>
                <h1><i class="bi bi-columns-gap" aria-hidden="true"></i> Comparador inteligente</h1>
                <p>Selecciona hasta tres celulares y compara precio, disponibilidad y SmartScore por perfil.</p>
            </div>

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
                            <img class="comparison-product-photo" src="{{ $imageUrl($product) }}" alt="{{ $product->marca }} {{ $product->nombre }}" loading="lazy">
                            <span class="eyebrow">{{ $product->marca ?: 'CELL SAC' }}</span>
                            <h2>{{ $product->nombre }}</h2>
                            <div class="price">S/ {{ number_format((float) $product->effective_price, 2) }}</div>
                            <p>{{ $product->category?->nombre ?: 'Equipo' }} · Stock {{ (int) $product->existencias }}</p>
                            @php($score = $product->intelligenceProfile?->puntuacion_valor ?? 70)
                            <div class="score-row">
                                <span>SmartScore</span>
                                <b>{{ (int) $score }}/100</b>
                                <div class="scorebar"><i style="width: {{ (int) $score }}%"></i></div>
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

@push('scripts')
    <script>
        document.getElementById('compareForm')?.addEventListener('submit', () => {
            const values = [...document.querySelectorAll('.compare-select')].map((select) => select.value).filter(Boolean);
            document.getElementById('compareIds').value = [...new Set(values)].slice(0, 3).join(',');
        });
    </script>
@endpush
