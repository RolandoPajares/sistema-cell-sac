@extends('layouts.tienda.principal')

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Página no encontrada')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
<section class="container form-page">
    <div class="form-card" style="text-align:center">
        <span class="product-category">Error 404</span>
        <h1>No encontramos esta página</h1>
        <p>El enlace puede haber cambiado o el producto ya no está disponible.</p>
        <a class="btn btn-primary" href="{{ route('home') }}">Volver al inicio</a>
    </div>
</section>
@endsection

