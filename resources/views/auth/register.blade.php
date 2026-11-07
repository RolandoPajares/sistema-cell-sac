@extends('layouts.auth.principal')

@section('body_class', 'modulo-autenticacion')
@section('title', 'Crear cuenta')

@section('content')
<div class="auth-register-page">
<div class="auth-wrap panel">
    <h1>Crear cuenta</h1>
    <p>El registro público crea una cuenta de cliente. El acceso administrativo se concede según el rol autorizado.</p>
    @if($errors->any())<div class="alert alert-error">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('register.store') }}">
        @csrf
        <div class="form-group">
            <label for="tipo_cliente">Tipo de cliente</label>
            <select class="input" id="tipo_cliente" name="tipo_cliente" data-customer-type required>
                <option value="minorista" @selected(old('tipo_cliente', 'minorista') === 'minorista')>Cliente minorista</option>
                <option value="mayorista" @selected(old('tipo_cliente') === 'mayorista')>Cliente mayorista</option>
            </select>
        </div>

        <div class="form-group">
            <label for="nombre">Nombre completo</label>
            <input
                class="input"
                id="nombre"
                name="nombre"
                value="{{ old('nombre') }}"
                maxlength="120"
                required
                autocomplete="name"
            >
        </div>

        <div class="form-group">
            <label for="correo">Correo</label>
            <input
                class="input"
                id="correo"
                type="email"
                name="correo"
                value="{{ old('correo') }}"
                maxlength="160"
                required
                autocomplete="email"
            >
        </div>

        <div class="form-group" data-wholesale-field @if(old('tipo_cliente', 'minorista') !== 'mayorista') hidden @endif>
            <label for="ruc">RUC</label>
            <input
                class="input"
                id="ruc"
                name="ruc"
                value="{{ old('ruc') }}"
                maxlength="11"
                inputmode="numeric"
                autocomplete="organization"
                @required(old('tipo_cliente') === 'mayorista')
            >
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="admin-password-input auth-password-field">
                <input class="input" id="password" type="password" name="password" minlength="10" maxlength="4096" required autocomplete="new-password">
                <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Confirmar contraseña</label>
            <div class="admin-password-input auth-password-field">
                <input class="input" id="password_confirmation" type="password" name="password_confirmation" maxlength="4096" required autocomplete="new-password">
                <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye" aria-hidden="true"></i></button>
            </div>
        </div>
        <button class="btn btn-primary" style="width:100%">Registrarme</button>
    </form>
    <p>¿Ya tienes cuenta? <a style="color: var(--primary)" href="{{ route('login') }}">Inicia sesión</a>.</p>
</div>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const selector = document.querySelector('[data-customer-type]');
        const field = document.querySelector('[data-wholesale-field]');
        const ruc = document.querySelector('#ruc');
        if (!selector || !field || !ruc) return;
        const update = () => {
            const wholesale = selector.value === 'mayorista';
            field.hidden = !wholesale;
            ruc.required = wholesale;
            if (!wholesale) ruc.value = '';
        };
        selector.addEventListener('change', update);
        update();
    })();
</script>
@endpush
