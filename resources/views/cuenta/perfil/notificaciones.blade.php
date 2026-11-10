@extends('layouts.panel.principal')

@section('title', 'Mis notificaciones')
@section('content')
    {{-- Encabezado y navegación de notificaciones. --}}
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Mis notificaciones</h1><p>Avisos asociados a tu cuenta.</p></div></header>
        @include('cuenta.perfil.navegacion')
        @unless ($available)
            {{-- Aviso mostrado si la tabla de notificaciones aún no existe. --}}
            <div class="admin-alert admin-alert--error" role="status">La tabla de notificaciones no está disponible en la base de datos.</div>
        @else
            {{-- Listado de notificaciones y acciones de lectura. --}}
            <div class="form-card">
                @forelse ($notificaciones as $notificacion)
                    <article class="notification-row">
                        <div>
                            <strong>{{ $notificacion->nombreTipo() }}</strong>
                            <p>{{ $notificacion->datosLegibles() }}</p>
                            <small>{{ $notificacion->creado_en?->diffForHumans() }}</small>
                        </div>
                        @if (! $notificacion->leido_en)
                            <form method="post" action="{{ route('management.account.notifications.read', $notificacion->id) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-outline-primary btn-sm" type="submit">Marcar como leída</button>
                            </form>
                        @else
                            <span class="badge text-bg-light">Leída</span>
                        @endif
                    </article>
                @empty
                    <p>No tienes notificaciones registradas.</p>
                @endforelse
                {{ $notificaciones->links() }}
            </div>
        @endunless
    </section>
@endsection
