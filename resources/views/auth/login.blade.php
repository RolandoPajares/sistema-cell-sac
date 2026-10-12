@extends('layouts.store')

@section('body_class', 'modulo-autenticacion')
@push('styles')<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/publico/autenticacion.css') }}">@endpush
@section('title', 'Iniciar sesión')

@section('content')
<div class="auth-wrap panel">
    <h1>Iniciar sesión</h1>
    <p>Los administradores son enviados automáticamente al panel de gestión.</p>
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('login.store') }}">
        @csrf
        <div class="form-group"><label>Correo</label><input class="input" type="email" name="correo" value="{{ old('correo') }}" maxlength="160" required autocomplete="email"></div>
        <div class="form-group"><label>Contraseña</label><input class="input" type="password" name="password" maxlength="4096" required autocomplete="current-password"></div>
        <button class="btn btn-primary" style="width:100%">Ingresar</button>
    </form>
    <p>¿No tienes cuenta? <a style="color: var(--primary)" href="{{ route('register') }}">Regístrate aquí</a>.</p>
</div>
@endsection
