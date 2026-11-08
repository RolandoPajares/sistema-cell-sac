@php($profileHomeRoute = auth()->user()?->can('gestion.seguridad.consultar') ? 'management.account' : 'account.profile.show')
<nav class="profile-account-links" aria-label="Secciones de mi cuenta">
    <a href="{{ route($profileHomeRoute) }}">Mi perfil</a>
    <a href="{{ route('management.account.security') }}">Seguridad</a>
    <a href="{{ route('management.account.sessions') }}">Sesiones activas</a>
    <a href="{{ route('management.account.preferences') }}">Preferencias</a>
    <a href="{{ route('management.account.notifications') }}">Notificaciones</a>
    <a href="{{ route('management.account.activity') }}">Actividad reciente</a>
</nav>
