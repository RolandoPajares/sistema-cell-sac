@extends(in_array(auth()->user()->rol, ['cliente_mayorista'], true) ? 'layouts.tienda' : 'layouts.administracion')

@section('title', 'Cotizaciones mayoristas')

@section('content')
    <section class="container content-page">
        <h1>Cotizaciones mayoristas</h1>

        @include('gestion.cotizaciones.crear', ['customers' => $customers, 'products' => $products])
        <x-inteligencia.cotizador.resumen :quotations="$quotations" />
    </section>
@endsection
