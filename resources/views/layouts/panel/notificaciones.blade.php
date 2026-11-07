@if ($isAdministrator)
    <a class="admin-topbar-icon" href="{{ route('management.account.notifications') }}" aria-label="Mis notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></a>
@else
    <span class="admin-topbar-icon" aria-label="Notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></span>
@endif
