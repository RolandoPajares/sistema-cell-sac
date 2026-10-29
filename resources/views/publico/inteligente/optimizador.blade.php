@extends('layouts.tienda')

@section('title', 'Optimizador de compra')
@section('body_class', 'modulo-optimizador')

@section('content')
    <section class="smart-page">
        <div class="container">
            <div class="smart-hero">
                <span class="eyebrow">Modo emprendedor</span>
                <h1><i class="bi bi-cash-coin" aria-hidden="true"></i> Optimizador por presupuesto</h1>
                <p>El sistema arma automáticamente una propuesta de compra respetando tu presupuesto y objetivo comercial.</p>
            </div>

            <form class="smart-form panel" method="get">
                <label>
                    Presupuesto
                    <input
                        class="input"
                        type="number"
                        name="presupuesto"
                        min="500"
                        step="100"
                        value="{{ $budget > 0 ? $budget : '' }}"
                        placeholder="Ej. 10000"
                    >
                </label>

                <label>
                    Objetivo
                    <select name="objetivo">
                        <option value="variety" @selected($goal === 'variety')>Más variedad</option>
                        <option value="units" @selected($goal === 'units')>Más unidades</option>
                        <option value="margin" @selected($goal === 'margin')>Mayor margen estimado</option>
                        <option value="premium" @selected($goal === 'premium')>Equipos premium</option>
                    </select>
                </label>
                <button class="btn btn-primary" type="submit">Generar propuesta</button>
            </form>

            @if ($proposal)
                <div class="panel proposal">
                    <div class="proposal-kpis">
                        <div><span>Inversión</span><strong>S/ {{ number_format($proposal['investment'], 2) }}</strong></div>
                        <div><span>Saldo disponible</span><strong>S/ {{ number_format($proposal['balance'], 2) }}</strong></div>
                        <div><span>Margen referencial</span><strong>S/ {{ number_format($proposal['estimated_margin'], 2) }}</strong></div>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Producto</th><th>Cant.</th><th>Precio</th><th>Subtotal</th></tr></thead>
                            <tbody>
                                @forelse ($proposal['items'] as $item)
                                    <tr>
                                        <td>{{ $item['product']->marca }} {{ $item['product']->nombre }}</td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>S/ {{ number_format((float) $item['product']->effective_price, 2) }}</td>
                                        <td>S/ {{ number_format($item['subtotal'], 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4">No hay productos disponibles para ese presupuesto.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <p class="product-note">Margen referencial calculado para simulación; no constituye una garantía de rentabilidad.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
