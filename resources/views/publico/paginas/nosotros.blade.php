@extends('layouts.tienda.principal')

@section('title', 'Nosotros | MD Technology Cell')
@section('body_class', 'modulo-about')

@section('content')
    <section class="about-reference-hero">
        <div class="container about-reference-hero__inner">
            <div class="about-reference-hero__copy">
                <span class="eyebrow">NOSOTROS</span>
                <h1>Conoce a<br>Technology <span>CELL</span></h1>
                <p>Tu tienda de confianza en celulares, audífonos y accesorios originales.</p>
                <div class="about-hero-points">
                    <span><i class="bi bi-shield-check" aria-hidden="true"></i><b>Productos</b><small>100% originales</small></span>
                    <span><i class="bi bi-truck" aria-hidden="true"></i><b>Envíos rápidos</b><small>y seguros</small></span>
                    <span><i class="bi bi-headset" aria-hidden="true"></i><b>Asesoría</b><small>personalizada</small></span>
                </div>
            </div>
        </div>
    </section>

    <section class="about-company section-space">
        <div class="container about-company__grid">
            <div class="about-company__content">
                <span class="eyebrow eyebrow--light">Empresa local</span>
                <h2>MD Technology Digital Cell S.A.C.</h2>
                <p>Negocio dedicado a la comercialización de celulares y audífonos originales de marcas reconocidas. Nuestra propuesta facilita la consulta de productos y centraliza la gestión de catálogo e inventario.</p>
                <div class="about-facts">
                    <article class="about-fact"><i class="bi bi-geo-alt" aria-hidden="true"></i><h3>Ubicación</h3><p>Jr. Amazonas 563, Bagua 01721, cerca de Plásticos Jireh.</p></article>
                    <article class="about-fact"><i class="bi bi-headset" aria-hidden="true"></i><h3>Atención</h3><p>Venta presencial con apoyo de una plataforma web responsiva.</p></article>
                    <article class="about-fact"><i class="bi bi-bullseye" aria-hidden="true"></i><h3>Misión</h3><p>Ofrecer celulares y audífonos originales, con asesoría personalizada y atención confiable.</p></article>
                    <article class="about-fact"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><h3>Visión</h3><p>Ser una empresa referente en Bagua por la calidad, confianza e innovación en la atención.</p></article>
                </div>
            </div>
            <div class="about-company__photo"><img src="{{ asset('assets/cell-legacy/img/publico/nosotros/local.jpg') }}" alt="Fachada del local de MD Technology Digital Cell" loading="lazy"></div>
        </div>
    </section>

    <section class="about-values section-space">
        <div class="container">
            <div class="about-values__heading">
                <span class="about-values__kicker"><i></i> CONÓCENOS <i></i></span>
                <h2>Más que una tienda, <span>tu aliado en tecnología</span></h2>
                <p>Conoce más sobre nuestra trayectoria, lo que ofrecemos y el compromiso que nos define.</p>
            </div>
            <div class="about-values__grid">
                <article><i class="bi bi-calendar3" aria-hidden="true"></i><h3>Nuestra historia</h3><p>Nacemos en Bagua con el objetivo de acercar lo mejor en telefonía y audio, con un servicio cercano y de confianza.</p></article>
                <article><i class="bi bi-boxes" aria-hidden="true"></i><h3>Qué ofrecemos</h3><p>Celulares y audífonos originales de marcas reconocidas, con variedad, calidad y garantía.</p></article>
                <article><i class="bi bi-person-hearts" aria-hidden="true"></i><h3>Atención confiable</h3><p>Asesoría personalizada y acompañamiento en tu compra, tanto en tienda física como a través de nuestra plataforma web.</p></article>
                <article><i class="bi bi-map" aria-hidden="true"></i><h3>Cobertura local</h3><p>Atención presencial en Bagua, Amazonas, con un servicio ágil, seguro y pensado en nuestra comunidad.</p></article>
            </div>
        </div>
    </section>
@endsection
