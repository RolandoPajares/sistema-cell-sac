@extends(auth()->user()->isRole('cliente_mayorista') ? 'layouts.tienda.principal' : 'layouts.panel.principal')

@section('title', 'Cotizaciones mayoristas')

@section('content')
    <section class="container content-page">
        <h1>Cotizaciones mayoristas</h1>

        @include('gestion.comercial.cotizaciones.crear', ['customers' => $customers, 'products' => $products])
        <x-inteligencia.cotizador.resumen :quotations="$quotations" />
    </section>
@endsection
