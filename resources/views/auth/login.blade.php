@extends('layouts.auth.principal')

{{-- Configuración de la clase CSS del cuerpo y el título de la página --}}
@section('body_class', 'modulo-autenticacion')
@section('title', 'Iniciar sesión')

{{-- Carga de los estilos CSS específicos del formulario de login --}}
@push('styles')
    @vite('resources/css/auth/login.css')
@endpush

{{-- Carga del script JS para alternar la visibilidad de la contraseña --}}
@push('scripts')
    @vite('resources/js/auth/login.js')
@endpush

@section('content')
{{-- Contenedor principal de la pantalla de autenticación --}}
<div class="auth-reference">
    <div class="auth-reference__inner container">
        
        {{-- Bloque de bienvenida de la marca (Lado izquierdo) --}}
        <div class="auth-intro">
            <span class="auth-reference__badge"><i class="bi bi-stars"></i> MD SmartCommerce</span>
            <h1>Bienvenido a<br>Technology <span>CELL</span></h1>
        </div>

        {{-- Panel contenedor del formulario (Lado derecho) --}}
        <div class="auth-wrap panel">
            
            {{-- Logo de la empresa --}}
            <div class="auth-brand">
                <img src="{{ asset('assets/cell-legacy/img/marca/logo.jpeg') }}" alt="Logo Technology Cell">
            </div>

            <h2>Iniciar sesión</h2>

            {{-- Alerta de errores de validación enviados desde el servidor --}}
            @if ($errors->any())
                <div class="alert alert-error" role="alert">{{ $errors->first() }}</div>
            @endif

            {{-- Formulario principal de inicio de sesión con token CSRF --}}
            <form method="POST" action="{{ route('login.store') }}" novalidate>
                @csrf

                {{-- Campo de entrada para el correo electrónico --}}
                <div class="form-group">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" class="input" type="email" name="correo" value="{{ old('correo') }}" maxlength="160" required autocomplete="email" placeholder="ejemplo@correo.com">
                </div>

                {{-- Campo de entrada para la contraseña con botón para toggle visibilidad --}}
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="admin-password-input auth-password-field">
                        <input id="password" class="input" type="password" name="password" maxlength="4096" required autocomplete="current-password" placeholder="••••••••">
                        <button type="button" data-password-toggle aria-label="Mostrar u ocultar contraseña">
                            <i class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                </div>

                {{-- Opciones auxiliares: Checkbox recordar sesión y enlace de recuperación --}}
                <div class="auth-reference__options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> 
                        <span>Recordarme</span>
                    </label>
                    <a href="{{ route('pages.contact') }}#contact-name">¿Olvidaste tu contraseña?</a>
                </div>

                {{-- Botón de envío del formulario --}}
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    <i class="bi bi-box-arrow-in-right"></i> Ingresar
                </button>
            </form>

            {{-- Enlace hacia la pantalla de registro de nuevo usuario --}}
            <a class="auth-reference__register" href="{{ route('register') }}">
                <i class="bi bi-person-plus"></i> Registrarse aquí
            </a>

            {{-- Sección informativa con insignias de seguridad y confianza --}}
            <div class="auth-reference__secure">
                <span><i class="bi bi-shield-check"></i> <b>Acceso seguro y confiable</b></span>
                <div>
                    <small><i class="bi bi-lock"></i> Tus datos<br>protegidos</small>
                    <small><i class="bi bi-patch-check"></i> Cuenta<br>verificada</small>
                    <small><i class="bi bi-fingerprint"></i> Experiencia<br>personalizada</small>
                </div>
            </div>

        </div>

    </div>
</div>
@endsection