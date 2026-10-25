@extends('layouts.administracion')

@section('title', 'Usuarios y roles')

@section('content')
<section class="container content-page">
    <h1>Usuarios y los siete roles</h1>

    {{-- Formulario de creación de usuarios --}}
    @include('gestion.usuarios.crear')

    {{-- Listado de usuarios registrados --}}
    <div class="users-list">
        @foreach($users as $user)
            <article class="data-row">
                <div>
                    <b>{{ $user->nombre }}</b>
                    <small>{{ $user->correo }}</small>
                </div>
                <span>{{ str_replace('_', ' ', $user->rol) }}</span>
            </article>
        @endforeach
    </div>

    {{-- Paginación --}}
    <div class="pagination-container">
        {{ $users->links() }}
    </div>
</section>
@endsection
