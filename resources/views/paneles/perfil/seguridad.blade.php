@extends('layouts.panel.principal')

@section('title', 'Seguridad de la cuenta')
@section('content')
    <section class="admin-page">
        <header class="admin-page-head"><div><span class="eyebrow">Cuenta personal</span><h1>Seguridad de la cuenta</h1><p>Revisa el estado de tu cuenta y controla las sesiones abiertas.</p></div></header>
        @include('paneles.perfil.navegacion')
        <div class="two-column">
            <article class="form-card"><h2>Contraseña</h2><p>Las contraseñas se almacenan con hash y el cambio exige validar tu contraseña actual.</p><a class="btn btn-primary" href="{{ route('management.account') }}#cambiar-contrasena">Cambiar contraseña</a></article>
            <article class="form-card"><h2>Sesiones y dispositivos</h2><p>{{ $sessionsAvailable ? $sessions->count().' sesiones registradas recientemente.' : 'El registro de sesiones estará disponible después de aplicar la migración de seguridad.' }}</p><a class="btn btn-primary" href="{{ route('management.account.sessions') }}">Administrar sesiones</a></article>
        </div>
        <article class="form-card"><h2>Estado de la cuenta</h2><p>{{ $sessionsAvailable ? 'La cuenta está autenticada y las nuevas sesiones se registran en el panel de seguridad.' : 'El control persistente de sesiones requiere una migración aditiva todavía no ejecutada.' }}</p></article>
    </section>
@endsection
