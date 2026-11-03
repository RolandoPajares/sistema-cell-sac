@extends('layouts.administracion')

@section('title', 'Perfil — cliente minorista')
@section('body_class', 'modulo-cuenta-admin rol-cliente-minorista')

@section('content')
    {{-- Perfil propio del cliente minorista (solo muestra los datos) --}}
    @php($usuario = auth()->user())

    <section class="perfil-cliente">
        <header class="perfil-cliente-portada">
            <span class="perfil-cliente-avatar">{{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($usuario->nombre, 0, 1)) }}</span>
            <div>
                <h1>{{ $usuario->nombre }}</h1>
                <p><i class="bi bi-person"></i> Cuenta personal · Cliente minorista</p>
            </div>
            <a class="perfil-cliente-boton" href="{{ route('dashboard') }}"><i class="bi bi-pencil"></i> Editar perfil</a>
        </header>

        <div class="perfil-cliente-datos">
            <article>
                <small>Nombre</small>
                <strong>{{ $usuario->nombre }}</strong>
            </article>
            <article>
                <small>Correo</small>
                <strong>{{ $usuario->correo }}</strong>
            </article>
            <article>
                <small>Tipo de cliente</small>
                <strong>Cliente minorista</strong>
            </article>
            <article>
                <small>Estado</small>
                <strong class="perfil-cliente-activo"><i class="bi bi-check-circle-fill"></i> Activa</strong>
            </article>
        </div>
    </section>
@endsection
