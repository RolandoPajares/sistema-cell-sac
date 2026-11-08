@extends(auth()->user()->can('gestion.cotizaciones.consultar') ? 'layouts.tienda.principal' : 'layouts.panel.principal')

@section('title', 'Cotizaciones mayoristas')

@section('content')
    <section class="container content-page">
        <h1>Cotizaciones mayoristas</h1>

        @include('gestion.comercial.cotizaciones.crear', ['customers' => $customers, 'products' => $products])
@foreach($quotations as $quotation)
    <article class="data-row">
        <div><b>Cotización #{{ $quotation->id }} · {{ $quotation->customer->nombre_contacto }}</b><small>{{ $quotation->estado }} · {{ $quotation->creado_en }}</small></div>
        <strong>S/ {{ number_format((float) $quotation->total, 2) }}</strong>
    </article>
@endforeach

{{ $quotations->links() }}

    </section>
@endsection
