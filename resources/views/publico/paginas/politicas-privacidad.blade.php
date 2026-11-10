@extends('layouts.tienda.principal')

@section('title', 'Políticas de privacidad | MD Technology Cell')
@section('body_class', 'modulo-politicas-privacidad')

@section('content')
    <section class="container content-page">
        <span class="eyebrow">INFORMACIÓN</span>
        <h1>Políticas de privacidad</h1>
        <p>La información proporcionada en esta plataforma se utiliza para atender solicitudes, gestionar la cuenta y operar las funciones autorizadas de la tienda.</p>
        <p>El acceso a la información se mantiene dentro de los permisos definidos por el sistema y se conserva para los fines operativos correspondientes.</p>
        <p>Para realizar una consulta sobre tus datos, utiliza nuestro <a href="{{ route('pages.contact') }}">canal de contacto</a>.</p>
    </section>
@endsection
