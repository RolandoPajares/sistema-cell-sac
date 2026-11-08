@php
    $currentUser = auth()->user();
    $hasManagementAccess = $currentUser?->can('gestion.seguridad.consultar') ?? false;
    $brandRoute = $hasManagementAccess ? 'management.dashboard' : 'dashboard';
    $accountRoute = $hasManagementAccess ? 'management.account' : 'account.profile.show';
@endphp

<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <div class="admin-sidebar-brand">
        <a href="{{ route($brandRoute) }}" title="MD Technology Digital Cell">
            <span class="admin-brand-mark"><i class="bi bi-phone" aria-hidden="true"></i></span>
            <span class="admin-brand-copy"><strong>TECHNOLOGY <b>CELL</b></strong><small>MD Technology Digital Cell S.A.C.</small></span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        @vite(['resources/css/paneles/dashboard.css'])
        <div class="admin-menu-desplegable" data-admin-menu-desplegable><div class="admin-menu-desplegable__groups">
            @foreach ($links['grupos'] ?? [] as $group)
                @php($hasActiveItem = collect($group['items'])->contains(fn (array $item): bool => $item['active'] ?? false))
                @php($submenuId = 'admin-menu-submenu-'.\Illuminate\Support\Str::slug($group['id']))
                @if ($group['direct'] ?? false)
                    @foreach ($group['items'] as $item)
                        @if ($item['enabled'] && $item['route'])
                            <a class="admin-menu-desplegable__dashboard {{ $item['active'] ? 'is-active' : '' }}" href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $item['label'] }}</span></a>
                        @endif
                    @endforeach
                @elseif ($group['items'] !== [])
                    <section class="admin-menu-desplegable__group {{ $hasActiveItem ? 'is-active is-open' : '' }}" data-admin-menu-group>
                        <button class="admin-menu-desplegable__toggle" type="button" aria-expanded="{{ $hasActiveItem ? 'true' : 'false' }}" aria-controls="{{ $submenuId }}" data-admin-menu-toggle><span class="admin-menu-desplegable__toggle-main"><i class="bi {{ $group['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $group['label'] }}</span></span><i class="bi bi-chevron-down admin-menu-desplegable__chevron" aria-hidden="true"></i></button>
                        <div class="admin-menu-desplegable__submenu" id="{{ $submenuId }}" data-admin-menu-submenu @if (! $hasActiveItem) hidden @endif>
                            @foreach ($group['items'] as $item)
                                @if ($item['enabled'] && $item['route'])
                                    <a class="admin-menu-desplegable__item {{ $item['active'] ? 'is-active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @if ($item['active']) aria-current="page" @endif><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $item['label'] }}</span></a>
                                @endif
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div></div>
    </nav>

    <div class="admin-sidebar-footer">
        <a class="{{ request()->routeIs($accountRoute) ? 'is-active' : '' }}" href="{{ route($accountRoute) }}" title="Mi perfil"><i class="bi bi-gear" aria-hidden="true"></i><span>Mi perfil</span></a>
        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" title="Cerrar sesión"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Cerrar sesión</span></button></form>
    </div>
</aside>
