
<aside class="admin-sidebar" id="adminSidebar" aria-label="Navegación del panel">
    <div class="admin-sidebar-brand">
        <a href="{{ route($panelBrandRoute) }}" title="MD Technology Digital Cell">
            <span class="admin-brand-mark"><i class="bi bi-phone" aria-hidden="true"></i></span>
            <span class="admin-brand-copy"><strong>TECHNOLOGY <b>CELL</b></strong><small>MD Technology Digital Cell S.A.C.</small></span>
        </a>
    </div>

    <nav class="admin-sidebar-nav">
        <div class="admin-menu-desplegable" data-admin-menu-desplegable><div class="admin-menu-desplegable__groups">
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
            @foreach ($links['grupos'] ?? [] as $group)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                @if ($group['direct'] ?? false)
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                    @foreach ($group['items'] as $item)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                        @if ($item['enabled'] && $item['route'])
                            <a class="admin-menu-desplegable__dashboard {{ $item['active'] ? 'is-active' : '' }}" href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif><i class="bi {{ $item['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $item['label'] }}</span></a>
                        @endif
                    @endforeach
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
                @elseif ($group['items'] !== [])
                    <section class="admin-menu-desplegable__group {{ ($group['hasActiveItem'] ?? false) ? 'is-active is-open' : '' }}" data-admin-menu-group>
                        <button class="admin-menu-desplegable__toggle" type="button" aria-expanded="{{ ($group['hasActiveItem'] ?? false) ? 'true' : 'false' }}" aria-controls="{{ $group['submenuId'] }}" data-admin-menu-toggle><span class="admin-menu-desplegable__toggle-main"><i class="bi {{ $group['icon'] }}" aria-hidden="true"></i><span class="admin-menu-desplegable__label">{{ $group['label'] }}</span></span><i class="bi bi-chevron-down admin-menu-desplegable__chevron" aria-hidden="true"></i></button>
                        <div class="admin-menu-desplegable__submenu" id="{{ $group['submenuId'] }}" data-admin-menu-submenu @if (! ($group['hasActiveItem'] ?? false)) hidden @endif>
{{-- ciclo que recorre los datos recibidos para generar elementos repetidos. --}}
                            @foreach ($group['items'] as $item)
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
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
        <a class="{{ request()->routeIs($panelAccountRoute) ? 'is-active' : '' }}" href="{{ route($panelAccountRoute) }}" title="Mi perfil"><i class="bi bi-gear" aria-hidden="true"></i><span>Mi perfil</span></a>
{{-- formulario que recopila datos y los envia a la ruta indicada. --}}
        <form method="post" action="{{ route('logout') }}">@csrf<button type="submit" title="Cerrar sesión"><i class="bi bi-box-arrow-right" aria-hidden="true"></i><span>Cerrar sesión</span></button></form>
    </div>
</aside>
