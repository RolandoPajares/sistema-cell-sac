@extends('layouts.administracion')

@section('title', 'Editar perfil — cliente minorista')
@section('body_class', 'modulo-cuenta-admin rol-cliente-minorista')

@section('content')
    {{-- Formularios para editar el perfil del cliente minorista --}}
    @php($usuario = auth()->user())

    <section class="perfil-cliente">
        <header class="perfil-cliente-portada">
            <span class="perfil-cliente-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($usuario->nombre, 0, 1)) }}</span>
            <div>
                <h1>Editar perfil</h1>
                <p><i class="bi bi-person"></i> Actualiza solo los datos de tu propia cuenta.</p>
            </div>
            <a class="perfil-cliente-boton" href="{{ route('dashboard') }}"><i class="bi bi-arrow-left"></i> Volver</a>
        </header>

        <div class="perfil-cliente-formularios">
            <form method="post" action="{{ route('account.profile') }}" class="perfil-cliente-caja">
                @csrf
                @method('PUT')
                <h2><i class="bi bi-person-vcard"></i> Datos personales</h2>
                <label>
                    Nombre
                    <input name="nombre" value="{{ old('nombre', $usuario->nombre) }}" required maxlength="120">
                </label>
                <label>
                    Correo
                    <input type="email" name="correo" value="{{ old('correo', $usuario->correo) }}" required maxlength="160">
                </label>
                <button class="perfil-cliente-boton" type="submit"><i class="bi bi-save"></i> Guardar perfil</button>
            </form>

            <form method="post" action="{{ route('account.password') }}" class="perfil-cliente-caja">
                @csrf
                @method('PUT')
                <h2><i class="bi bi-shield-lock"></i> Cambiar contraseña</h2>
                <label>
                    Contraseña actual
                    <span class="admin-password-input">
                        <input type="password" name="password_actual" required>
                        <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                    </span>
                </label>
                <label>
                    Nueva contraseña
                    <span class="admin-password-input">
                        <input type="password" name="password" required minlength="10">
                        <button type="button" data-password-toggle aria-label="Mostrar contraseña"><i class="bi bi-eye"></i></button>
                    </span>
                </label>
                <label>
                    Confirmar contraseña
                    <input type="password" name="password_confirmation" required>
                </label>
                <button class="perfil-cliente-boton perfil-cliente-boton--negro" type="submit"><i class="bi bi-key"></i> Cambiar contraseña</button>
            </form>
        </div>
    </section>
@endsection
