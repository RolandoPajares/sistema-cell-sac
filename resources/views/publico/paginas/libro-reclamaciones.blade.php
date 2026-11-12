@extends('layouts.tienda.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Libro de reclamaciones | MD Technology Cell')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-libro-reclamaciones')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    <section class="container content-page">
        <span class="eyebrow">ATENCIÓN AL CLIENTE</span>
        <h1>Libro de reclamaciones</h1>
        <p>Si deseas comunicar una consulta, incidencia o reclamo relacionado con tu atención, puedes utilizar nuestro canal de contacto.</p>
        <p>Describe el caso con información clara para que el equipo pueda revisarlo y responderte.</p>
        <a class="btn btn-primary" href="{{ route('pages.contact') }}#formulario">Ir al formulario de contacto</a>
    </section>
@endsection
