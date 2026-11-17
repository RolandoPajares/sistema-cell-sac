@extends('layouts.tienda.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Términos y condiciones | MD Technology Cell')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-terminos-condiciones')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    <section class="container content-page">
        <span class="eyebrow">INFORMACIÓN COMERCIAL</span>
        <h1>Términos y condiciones</h1>
        <p>La consulta del catálogo, el carrito y el checkout se realizan conforme a la disponibilidad, precios y condiciones vigentes mostradas por la plataforma.</p>
        <p>La disponibilidad se valida nuevamente durante la confirmación del pedido. El registro del pedido no constituye por sí mismo la confirmación de un pago.</p>
        <p>Para cualquier consulta sobre una operación, puedes comunicarte mediante el <a href="{{ route('pages.contact') }}">formulario de contacto</a>.</p>
    </section>
@endsection
