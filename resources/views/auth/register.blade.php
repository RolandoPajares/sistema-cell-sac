@extends('layouts.auth.principal')


{{-- Configuración general del layout de autenticación --}}
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('body_class', 'modulo-autenticacion')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Crear cuenta')

{{-- Recursos CSS exclusivos del formulario de registro --}}
{{-- bloque de estilos o scripts para el lugar reservado por la plantilla. --}}
@push('styles')
    @vite('resources/css/auth/register.css')
@endpush

{{-- Recursos JavaScript para los campos dinámicos del registro --}}
{{-- bloque de estilos o scripts para el lugar reservado por la plantilla. --}}
@push('scripts')
    @vite('resources/js/auth/register.js')
@endpush

{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
{{-- Contenedor principal de la página de registro --}}
<div class="auth-register-page">
    <div class="auth-wrap panel">
        
        <h1>Crear cuenta</h1>
        <p>El registro público crea una cuenta de cliente. El acceso administrativo se concede según el rol autorizado.</p>

        {{-- Alerta general de errores de validación enviados por el servidor --}}
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if ($errors->any())
            <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- Formulario público para la creación de cuenta con protección CSRF --}}
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form method="POST" action="{{ route('register.store') }}" novalidate>
{{-- proteccion CSRF que valida el origen del formulario. --}}
            @csrf

            {{-- Selector del tipo de cliente (determina si se solicita el RUC) --}}
            <div class="form-group">
                <label for="tipo_cliente">Tipo de cliente</label>
                <select class="input" id="tipo_cliente" name="tipo_cliente" data-customer-type required>
                    <option value="minorista" @selected(old('tipo_cliente', 'minorista') === 'minorista')>Cliente minorista</option>
                    <option value="mayorista" @selected(old('tipo_cliente') === 'mayorista')>Cliente mayorista</option>
                </select>
            </div>

            {{-- Campo para el nombre completo del usuario --}}
            <div class="form-group">
                <label for="nombrce">Nombre completo</label>
                <input id="nombre" class="input" type="text" name="nombre" value="{{ old('nombre') }}" maxlength="120" required autocomplete="name" placeholder="Tu nombre completo">
            </div>

            {{-- Campo para el correo electrónico --}}
            <div class="form-group">
                <label for="correo">Correo</label>
                <input id="correo" class="input" type="email" name="correo" value="{{ old('correo') }}" maxlength="160" required autocomplete="email" inputmode="email" placeholder="ejemplo@correo.com">
            </div>

            {{-- Campo condicional de RUC (se muestra únicamente para clientes mayoristas) --}}
            <div class="form-group" data-wholesale-field @if(old('tipo_cliente', 'minorista') !== 'mayorista') hidden @endif>
                <label for="ruc">RUC</label>
                <input id="ruc" class="input" type="text" name="ruc" value="{{ old('ruc') }}" maxlength="11" inputmode="numeric" autocomplete="organization" placeholder="Número de RUC" @required(old('tipo_cliente') === 'mayorista')>
            </div>

            {{-- Campo de contraseña de seguridad --}}
            <div class="form-group">
                <label for="password">Contraseña</label>
                <div class="admin-password-input auth-password-field">
                    <input id="password" class="input" type="password" name="password" minlength="10" maxlength="4096" required autocomplete="new-password" placeholder="••••••••••••">
                    <button type="button" data-password-toggle aria-label="Mostrar u ocultar contraseña">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            {{-- Campo de confirmación de contraseña --}}
            <div class="form-group">
                <label for="password_confirmation">Confirmar contraseña</label>
                <div class="admin-password-input auth-password-field">
                    <input id="password_confirmation" class="input" type="password" name="password_confirmation" maxlength="4096" required autocomplete="new-password" placeholder="••••••••••••">
                    <button type="button" data-password-toggle aria-label="Mostrar u ocultar contraseña">
                        <i class="bi bi-eye" aria-hidden="true"></i>
                    </button>
                </div>
            </div>

            {{-- Botón para enviar el formulario de registro --}}
            <button type="submit" class="btn btn-primary auth-submit">Registrarme</button>
        </form>

        {{-- Enlace de redirección si el usuario ya posee una cuenta --}}
        <p>¿Ya tienes cuenta? <a class="auth-register-link" href="{{ route('login') }}">Inicia sesión</a>.</p>
        
    </div>
</div>
@endsection
