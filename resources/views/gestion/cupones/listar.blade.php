@extends('layouts.administracion')

@section('title', 'Cupones')

@section('content')
    <section class="container content-page">
        <h1>Cupones</h1>
        <p class="notice">Los cupones se administran aquí, pero no descuentan el total del checkout hasta completar pruebas de compatibilidad y límites.</p>

        @include('gestion.cupones.crear')

        @foreach($coupons as $coupon)
            <article class="data-row">
                <b>{{ $coupon->codigo }}</b>
                <span>{{ $coupon->tipo_descuento }} {{ $coupon->valor }} · usos {{ $coupon->usos }}/{{ $coupon->limite_usos ?? '∞' }}</span>
            </article>
        @endforeach

        {{ $coupons->links() }}
    </section>
@endsection


