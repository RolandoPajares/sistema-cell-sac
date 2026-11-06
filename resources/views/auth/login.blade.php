@extends('layouts.tienda')

@section('body_class', 'modulo-autenticacion')
@section('title', 'Iniciar sesión')

@section('content')
<div class="auth-reference">
 <div class="auth-reference__inner container">
  <div class="auth-intro">
    <span class="auth-reference__badge"><i class="bi bi-stars"></i> MD SmartCommerce</span>
    <h1>Bienvenido a<br>Technology <span>CELL</span></h1>
    <p>Ingresa a tu cuenta y disfruta de una experiencia de compra inteligente, segura y personalizada.</p>
    <ul>
        <li><i class="bi bi-bag"></i><span><b>Catálogo actualizado</b><small>Las mejores marcas y modelos.</small></span></li>
        <li><i class="bi bi-lightning-charge-fill"></i><span><b>SmartMatch</b><small>Te ayuda a encontrar tu celular ideal.</small></span></li>
        <li><i class="bi bi-bar-chart"></i><span><b>Comparador inteligente</b><small>Analiza y elige con confianza.</small></span></li>
        <li><i class="bi bi-shield-check"></i><span><b>Compras seguras</b><small>Tu información siempre protegida.</small></span></li>
    </ul>
    <img class="auth-reference__devices" src="{{ asset('assets/cell-legacy/img/publico/inicio/secciones/hero-devices.png') }}" alt="Celulares, audífonos y reloj inteligente">
 </div>
 <div class="auth-wrap panel">
    <div class="auth-brand"><img src="{{ asset('assets/cell-legacy/img/marca/logo.jpeg') }}" alt="Technology Cell"></div>
    <h2>Iniciar sesión</h2>
    <p>Accede a tu cuenta y gestiona tus compras, comparaciones y recomendaciones.</p>
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <div class="form-group">
            <label>Correo</label>
            <input
                class="input"
                type="email"
                name="correo"
                value="{{ old('correo') }}"
                maxlength="160"
                required
                autocomplete="email"
            >
        </div>
        <div class="form-group">
            <label>Contraseña</label>
            <input
                class="input"
                type="password"
                name="password"
                maxlength="4096"
                required
                autocomplete="current-password"
            >
        </div>
        <div class="auth-reference__options">
            <label><input type="checkbox" name="remember" value="1"> <span>Recordarme</span></label>
            <a href="{{ route('pages.contact') }}#contact-name">¿Olvidaste tu contraseña?</a>
        </div>
        <button class="btn btn-primary" style="width:100%"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
    </form>
    <a class="auth-reference__register" href="{{ route('register') }}"><i class="bi bi-person-plus"></i> Registrarse aquí</a>
    <div class="auth-reference__secure"><span><i class="bi bi-shield-check"></i><b>Acceso seguro y confiable</b></span><div><small><i class="bi bi-lock"></i> Tus datos<br>protegidos</small><small><i class="bi bi-patch-check"></i> Cuenta<br>verificada</small><small><i class="bi bi-fingerprint"></i> Experiencia<br>personalizada</small></div></div>
 </div>
 </div>
</div>
@endsection
