@extends('layouts.panel.principal')


{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('title', 'Mis notificaciones')
{{-- seccion Blade que la plantilla principal mostrara en esta vista. --}}
@section('content')
    {{-- Encabezado y navegación de notificaciones. --}}
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Mis notificaciones</h1><p>Avisos asociados a tu cuenta.</p></div></header>
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('cuenta.perfil.navegacion')
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @unless ($available)
            {{-- Aviso mostrado si la tabla de notificaciones aún no existe. --}}
            <div class="admin-alert admin-alert--error" role="status">La tabla de notificaciones no está disponible en la base de datos.</div>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            {{-- Listado de notificaciones y acciones de lectura. --}}
            <div class="form-card">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                @forelse ($notificaciones as $notificacion)
                    <article class="notification-row">
                        <div>
                            <strong>{{ $notificacion->nombreTipo() }}</strong>
                            <p>{{ $notificacion->datosLegibles() }}</p>
                            <small>{{ $notificacion->creado_en?->diffForHumans() }}</small>
                        </div>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                        @if (! $notificacion->leido_en)
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
                            <form method="post" action="{{ route('management.account.notifications.read', $notificacion->id) }}">
{{-- proteccion CSRF que valida el origen del formulario. --}}
                                @csrf
{{-- metodo HTTP que permite simular PUT, PATCH o DELETE desde el formulario. --}}
                                @method('PATCH')
                                <button class="btn btn-outline-primary btn-sm" type="submit">Marcar como leída</button>
                            </form>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
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
