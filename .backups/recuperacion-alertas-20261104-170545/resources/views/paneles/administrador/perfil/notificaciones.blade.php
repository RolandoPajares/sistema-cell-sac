@extends('layouts.administracion')

@section('title', 'Mis notificaciones')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Mis notificaciones</h1><p>Avisos asociados a tu cuenta.</p></div></header>
        @include('paneles.administrador.perfil.navegacion')
        @unless ($available)
            <div class="admin-alert admin-alert--error" role="status">Las notificaciones persistentes requieren la migración de seguridad de cuenta.</div>
        @else
            <div class="form-card">
                @forelse ($notifications as $notification)
                    <article class="notification-row"><div><strong>{{ data_get($notification->data, 'title', class_basename($notification->type)) }}</strong><p>{{ data_get($notification->data, 'message', 'Aviso del sistema') }}</p><small>{{ $notification->created_at?->diffForHumans() }}</small></div>@if (! $notification->read_at)<form method="post" action="{{ route('management.account.notifications.read', $notification->id) }}">@csrf @method('PATCH')<button class="btn btn-outline-primary btn-sm" type="submit">Marcar como leída</button></form>@else<span class="badge text-bg-light">Leída</span>@endif</article>
                @empty
                    <p>No tienes notificaciones.</p>
                @endforelse
                {{ $notifications->links() }}
            </div>
        @endunless
    </section>
@endsection
