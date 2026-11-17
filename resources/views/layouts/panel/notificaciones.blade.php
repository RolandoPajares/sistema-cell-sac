
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if ($panelIsAdministrator)
    <a class="admin-topbar-icon" href="{{ route('management.account.notifications') }}" aria-label="Mis notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></a>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
@else
    <span class="admin-topbar-icon" aria-label="Notificaciones"><i class="bi bi-bell" aria-hidden="true"></i></span>
@endif
