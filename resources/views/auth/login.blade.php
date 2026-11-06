@extends('layouts.tienda')

@section('body_class', 'modulo-autenticacion')
@section('title', 'Iniciar sesión')

@section('content')
<div class="auth-shell container">
 <div class="auth-intro">
    <span class="eyebrow">MD SMARTCOMMERCE</span>
    <h1>Bienvenido a <span>Technology CELL</span></h1>
    <p>Ingresa a tu cuenta y disfruta de una experiencia de compra inteligente, segura y personalizada.</p>
    <ul><li><i class="bi bi-bag-check"></i> Catálogo actualizado</li><li><i class="bi bi-stars"></i> SmartMatch y comparador</li><li><i class="bi bi-shield-check"></i> Compras seguras</li></ul>
 </div>
 <div class="auth-wrap panel">
    <div class="auth-brand"><img src="{{ asset('assets/cell-legacy/img/marca/logo.jpeg') }}" alt="Technology Cell"></div>
    <h2>Iniciar sesión</h2>
    <p>Accede a tu cuenta y gestiona tus compras.</p>
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
        <button class="btn btn-primary" style="width:100%"><i class="bi bi-box-arrow-in-right"></i> Ingresar</button>
    </form>
    <p>¿No tienes cuenta? <a style="color: var(--primary)" href="{{ route('register') }}">Regístrate aquí</a>.</p>
 </div>
</div>
@endsection
