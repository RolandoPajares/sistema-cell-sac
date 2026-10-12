@extends('layouts.store')

@section('title', 'Nosotros | MD Technology Cell')
@section('body_class', 'modulo-about')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/yaxon/nosotros.css') }}">
@endpush

@section('content')
    <section>
        <div class="container detail-grid">
            <div>
                <span class="eyebrow">Empresa local</span>
                <h1>MD Technology Digital Cell S.A.C.</h1>
                <p>Negocio dedicado a la comercialización de celulares y audífonos originales de marcas reconocidas. Nuestra propuesta facilita la consulta de productos y centraliza la gestión del catálogo e inventario.</p>
                <div class="feature-grid" style="grid-template-columns:1fr 1fr">
                    <div class="feature"><h3>Ubicación</h3><p>Jr. Amazonas 563, Bagua 01721, cerca de Plásticos Jireh.</p></div>
                    <div class="feature"><h3>Atención</h3><p>Venta presencial con apoyo de una plataforma web responsiva.</p></div>
                    <div class="feature"><h3>Misión</h3><p>Ofrecer celulares y audífonos originales, con asesoría personalizada y atención confiable.</p></div>
                    <div class="feature"><h3>Visión</h3><p>Ser una empresa referente en Bagua por la calidad, confianza e innovación en la atención.</p></div>
                </div>
            </div>
            <div class="hero-card"><img src="{{ asset('assets/cell-legacy/img/publico/nosotros/local.jpg') }}" alt="Local MD Technology Digital Cell"></div>
        </div>
    </section>
@endsection
