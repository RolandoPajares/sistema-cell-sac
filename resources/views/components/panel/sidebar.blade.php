@php
    $isAdministrator = auth()->user()?->rol === 'administrador';
    $brandRoute = $isAdministrator ? 'management.dashboard' : 'dashboard';
    $accountRoute = $isAdministrator ? 'management.account' : 'dashboard';
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <div class="admin-sidebar-brand">
        <a href="{{ route($brandRoute) }}" title="MD Technology Digital Cell">
            <span class="admin-brand-mark"><i class="bi bi-phone" aria-hidden="true"></i></span>
            <span class="admin-brand-copy">
                <strong>TECHNOLOGY <b>CELL</b></strong>
                <small>MD Technology Digital Cell S.A.C.</small>
            </span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        @foreach ($links as $link)
            @if (\Illuminate\Support\Facades\Route::has($link['route']))
                <a class="{{ request()->routeIs($link['route']) ? 'is-active' : '' }}" href="{{ route($link['route']) }}" title="{{ $link['label'] }}">
                    <i class="bi {{ $link['icon'] }}" aria-hidden="true"></i>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="admin-sidebar-footer">
        <a class="{{ request()->routeIs($accountRoute) ? 'is-active' : '' }}" href="{{ route($accountRoute) }}" title="{{ $isAdministrator ? 'Configuración' : 'Mi cuenta' }}">
            <i class="bi bi-gear" aria-hidden="true"></i>
            <span>{{ $isAdministrator ? 'Configuración' : 'Mi cuenta' }}</span>
        </a>
        <form method="post" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Cerrar sesión">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
