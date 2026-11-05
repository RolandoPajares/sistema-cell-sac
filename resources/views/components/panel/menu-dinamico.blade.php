@props(['menu' => []])

<div class="admin-menu-desplegable" data-admin-menu-desplegable>
    <div class="admin-menu-desplegable__groups">
        @foreach ($menu['grupos'] ?? [] as $group)
            @php
                $hasActiveItem = collect($group['items'])->contains(fn (array $item): bool => $item['active'] ?? false);
                $submenuId = 'admin-menu-submenu-'.\Illuminate\Support\Str::slug($group['id']);
            @endphp

            @if ($group['direct'] ?? false)
                @foreach ($group['items'] as $item)
                    @if ($item['enabled'] && $item['route'])
                        <a class="admin-menu-desplegable__dashboard {{ $item['active'] ? 'is-active' : '' }}" href="{{ route($item['route']) }}" @if ($item['active']) aria-current="page" @endif>
                            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                            <span class="admin-menu-desplegable__label">{{ $item['label'] }}</span>
                        </a>
                    @else
                        <span class="admin-menu-desplegable__dashboard admin-menu-desplegable__item--disabled" aria-disabled="true" title="Módulo pendiente de implementación">
                            <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                            <span class="admin-menu-desplegable__label">{{ $item['label'] }} · Próximamente</span>
                        </span>
                    @endif
                @endforeach
            @else
                @php($isOpen = $hasActiveItem)
                <section class="admin-menu-desplegable__group {{ $hasActiveItem ? 'is-active' : '' }} {{ $isOpen ? 'is-open' : '' }}" data-admin-menu-group>
                    <button class="admin-menu-desplegable__toggle" type="button" aria-expanded="{{ $isOpen ? 'true' : 'false' }}" aria-controls="{{ $submenuId }}" data-admin-menu-toggle>
                        <span class="admin-menu-desplegable__toggle-main">
                            <i class="bi {{ $group['icon'] }}" aria-hidden="true"></i>
                            <span class="admin-menu-desplegable__label">{{ $group['label'] }}</span>
                        </span>
                        <i class="bi bi-chevron-down admin-menu-desplegable__chevron" aria-hidden="true"></i>
                    </button>
                    <div class="admin-menu-desplegable__submenu" id="{{ $submenuId }}" data-admin-menu-submenu @if (! $isOpen) hidden @endif>
                        @foreach ($group['items'] as $item)
                            @if ($item['enabled'] && $item['route'])
                                <a class="admin-menu-desplegable__item {{ $item['active'] ? 'is-active' : '' }}" href="{{ route($item['route']) }}" title="{{ $item['label'] }}" @if ($item['active']) aria-current="page" @endif>
                                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                    <span class="admin-menu-desplegable__label">{{ $item['label'] }}</span>
                                </a>
                            @else
                                <span class="admin-menu-desplegable__item admin-menu-desplegable__item--disabled" aria-disabled="true" title="Módulo pendiente de implementación">
                                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                                    <span class="admin-menu-desplegable__label">{{ $item['label'] }} · Próximamente</span>
                                </span>
                            @endif
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</div>
