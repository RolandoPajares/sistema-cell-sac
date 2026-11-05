@extends('layouts.tienda')

@section('body_class', 'modulo-autenticacion')
@section('title', 'Crear cuenta')

@section('content')
<div class="auth-wrap panel">
    <h1>Crear cuenta</h1>
    <p>El registro público crea una cuenta de cliente. El acceso administrativo se concede según el rol autorizado.</p>
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('register.store') }}">
        @csrf
        <div class="form-group">
            <label>Nombre completo</label>
            <input
                class="input"
                name="nombre"
                value="{{ old('nombre') }}"
                maxlength="120"
                required
                autocomplete="name"
            >
        </div>

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
                minlength="10"
                maxlength="4096"
                required
                autocomplete="new-password"
            >
        </div>

        <div class="form-group">
            <label>Confirmar contraseña</label>
            <input
                class="input"
                type="password"
                name="password_confirmation"
                maxlength="4096"
                required
                autocomplete="new-password"
            >
        </div>
        <button class="btn btn-primary" style="width:100%">Registrarme</button>
    </form>
    <p>¿Ya tienes cuenta? <a style="color: var(--primary)" href="{{ route('login') }}">Inicia sesión</a>.</p>
</div>
@endsection
