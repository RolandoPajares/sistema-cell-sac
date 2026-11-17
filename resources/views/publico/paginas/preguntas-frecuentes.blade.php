@extends('layouts.tienda.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Preguntas frecuentes | MD Technology Cell')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-preguntas-frecuentes')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    <section class="container content-page">
        <span class="eyebrow">AYUDA</span>
        <h1>Preguntas frecuentes</h1>
        <h2>¿Cómo consulto un producto?</h2>
        <p>Ingresa al catálogo para revisar los productos, sus características, precios y disponibilidad.</p>
        <h2>¿Qué ocurre al confirmar un pedido?</h2>
        <p>El sistema vuelve a validar la disponibilidad y registra el pedido con la información de la cuenta.</p>
        <h2>¿Cómo consulto mis pedidos?</h2>
        <p>Después de iniciar sesión, puedes revisar el historial y el detalle desde la sección de pedidos.</p>
        <h2>¿Necesitas ayuda adicional?</h2>
        <p>Utiliza nuestro <a href="{{ route('pages.contact') }}">canal de contacto</a> para enviar una consulta.</p>
    </section>
@endsection
