{{-- Menú lateral desplegable del cliente minorista (mismo estilo que el del administrador) --}}
{{-- Responsable: Integrante 4 - Sebastián --}}
@php
    // arma cada opción del menú, solo si la ruta existe
    $opcion = static fn (string $ruta, string $texto, string $icono, string $ancla = ''): array => [
        'ruta' => $ruta,
        'texto' => $texto,
        'icono' => $icono,
        'ancla' => $ancla,
        'existe' => \Illuminate\Support\Facades\Route::has($ruta),
    ];

    $grupos = [
        [
            'clave' => 'compras',
            'titulo' => 'Mis compras',
            'icono' => 'bi-bag-check',
            'opciones' => [
                $opcion('orders.index', 'Mis pedidos', 'bi-box-seam'),
                $opcion('cart.index', 'Mi carrito', 'bi-cart3'),
            ],
        ],
        [
            'clave' => 'tienda',
            'titulo' => 'Tienda',
            'icono' => 'bi-shop',
            'opciones' => [
                $opcion('catalog.index', 'Catálogo', 'bi-phone'),
                $opcion('home', 'Página de inicio', 'bi-house'),
            ],
        ],
        [
            'clave' => 'elegir',
            'titulo' => 'Te ayudamos a elegir',
            'icono' => 'bi-stars',
            'opciones' => [
                $opcion('recommendations', 'Recomendador', 'bi-stars'),
                $opcion('comparison', 'Comparador', 'bi-columns-gap'),
                $opcion('smart.optimizer', 'Optimizar compra', 'bi-calculator'),
                $opcion('assistant', 'Asistente virtual', 'bi-robot'),
            ],
        ],
        [
            'clave' => 'cuenta',
            'titulo' => 'Mi cuenta',
            'icono' => 'bi-person-circle',
            'opciones' => [
                $opcion('dashboard', 'Mis datos', 'bi-person-vcard', '#mis-datos'),
                $opcion('dashboard', 'Seguridad', 'bi-shield-lock', '#seguridad'),
                $opcion('pages.contact', 'Ayuda', 'bi-headset'),
            ],
        ],
    ];

    // una opción está activa si estamos en esa página (las anclas no cuentan)
    $estaActiva = static fn (array $item): bool => $item['ancla'] === ''
        && (request()->routeIs($item['ruta']) || request()->routeIs($item['ruta'].'.*'));
@endphp

<div class="cliente-menu" data-cliente-menu>
    <a class="cliente-menu__inicio {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}" title="Mi panel">
        <i class="bi bi-speedometer2" aria-hidden="true"></i>
        <span class="cliente-menu__texto">Mi panel</span>
    </a>

    <div class="cliente-menu__grupos">
        @foreach ($grupos as $grupo)
            @php
                $grupoActivo = collect($grupo['opciones'])->contains(fn ($item) => $item['existe'] && $estaActiva($item));
            @endphp
            <section class="cliente-menu__grupo {{ $grupoActivo ? 'is-active is-open' : '' }}" data-cliente-menu-grupo>
                <button class="cliente-menu__boton" type="button" aria-expanded="{{ $grupoActivo ? 'true' : 'false' }}" aria-controls="cliente-submenu-{{ $grupo['clave'] }}" data-cliente-menu-boton>
                    <span class="cliente-menu__boton-izq">
                        <i class="bi {{ $grupo['icono'] }}" aria-hidden="true"></i>
                        <span class="cliente-menu__texto">{{ $grupo['titulo'] }}</span>
                    </span>
                    <i class="bi bi-chevron-down cliente-menu__flecha" aria-hidden="true"></i>
                </button>

                <div class="cliente-menu__submenu" id="cliente-submenu-{{ $grupo['clave'] }}" data-cliente-menu-submenu @if (! $grupoActivo) hidden @endif>
                    @foreach ($grupo['opciones'] as $item)
                        @if ($item['existe'])
                            <a class="cliente-menu__opcion {{ $estaActiva($item) ? 'is-active' : '' }}" href="{{ route($item['ruta']).$item['ancla'] }}" title="{{ $item['texto'] }}">
                                <span class="cliente-menu__texto">{{ $item['texto'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</div>
