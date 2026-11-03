{{-- Menú lateral desplegable del cliente minorista (diseño pedido por Rolando) --}}
{{-- Responsable: Integrante 4 - Sebastián --}}
{{-- Tipos de opción: ruta = página que ya existe, vista = sección de mi panel (#), pronto = todavía no tiene backend --}}
@php
    $ruta = static fn (string $texto, string $nombreRuta, string $extra = ''): array => [
        'texto' => $texto,
        'tipo' => \Illuminate\Support\Facades\Route::has($nombreRuta) ? 'ruta' : 'pronto',
        'ruta' => $nombreRuta,
        'extra' => $extra,
    ];
    $vista = static fn (string $texto, string $ancla): array => [
        'texto' => $texto,
        'tipo' => 'vista',
        'ruta' => 'dashboard',
        'extra' => '#'.$ancla,
    ];
    $pronto = static fn (string $texto): array => [
        'texto' => $texto,
        'tipo' => 'pronto',
        'ruta' => null,
        'extra' => '',
    ];

    $grupos = [
        [
            'clave' => 'explorar',
            'titulo' => 'Explorar productos',
            'icono' => 'bi-box-seam',
            'opciones' => [
                $ruta('Catálogo de productos', 'catalog.index'),
                $ruta('Categorías', 'catalog.index', '#filtros'),
                $ruta('Buscar productos', 'catalog.index', '?q='),
                $ruta('Comparar productos', 'comparison'),
                $ruta('Recomendaciones inteligentes', 'recommendations'),
                $pronto('Lista de favoritos'),
            ],
        ],
        [
            'clave' => 'compras',
            'titulo' => 'Mis compras',
            'icono' => 'bi-cart3',
            'opciones' => [
                $ruta('Mi carrito', 'cart.index'),
                $ruta('Finalizar compra', 'checkout.form'),
                $ruta('Mis pedidos', 'orders.index'),
                $vista('Seguimiento de pedidos', 'resumen/seguimiento'),
                $vista('Historial de compras', 'reportes'),
                $pronto('Boletas y comprobantes'),
            ],
        ],
        [
            'clave' => 'ofertas',
            'titulo' => 'Ofertas y beneficios',
            'icono' => 'bi-tag',
            'opciones' => [
                $vista('Ofertas especiales', 'promociones/ofertas'),
                $vista('Promociones vigentes', 'promociones/campanias'),
                $vista('Mis cupones', 'promociones/cupones'),
                $vista('Descuentos disponibles', 'promociones'),
            ],
        ],
        [
            'clave' => 'postventa',
            'titulo' => 'Devoluciones y garantías',
            'icono' => 'bi-shield-check',
            'opciones' => [
                $vista('Solicitar devolución', 'garantias/devolucion'),
                $pronto('Seguimiento de solicitudes'),
                $vista('Mis garantías', 'garantias'),
            ],
        ],
        [
            'clave' => 'cuenta',
            'titulo' => 'Mi cuenta',
            'icono' => 'bi-person',
            'opciones' => [
                $vista('Mi perfil', 'perfil'),
                $vista('Editar información personal', 'perfil/datos'),
                $pronto('Mis direcciones'),
                $vista('Preferencias', 'configuracion'),
                $vista('Seguridad de mi cuenta', 'perfil/seguridad'),
            ],
        ],
        [
            'clave' => 'ayuda',
            'titulo' => 'Ayuda y soporte',
            'icono' => 'bi-headset',
            'opciones' => [
                $ruta('Asistente IA', 'assistant'),
                $ruta('Centro de ayuda', 'pages.contact'),
                $ruta('Contactar con soporte', 'pages.contact', '#formulario'),
                $pronto('Mis reclamos'),
            ],
        ],
    ];

    // una opción con ruta está activa si estamos en esa página
    $estaActiva = static fn (array $item): bool => $item['tipo'] === 'ruta'
        && $item['extra'] === ''
        && (request()->routeIs($item['ruta']) || request()->routeIs($item['ruta'].'.*'));
@endphp

<div class="cliente-menu" data-cliente-menu>
    <p class="cliente-menu__rol">CLIENTE MINORISTA</p>

    <a class="cliente-menu__inicio {{ request()->routeIs('dashboard') ? 'is-active' : '' }}" href="{{ route('dashboard') }}#resumen" title="Dashboard" data-cliente-menu-vista="resumen">
        <i class="bi bi-grid-1x2" aria-hidden="true"></i>
        <span class="cliente-menu__texto">Dashboard</span>
    </a>

    <div class="cliente-menu__grupos">
        @foreach ($grupos as $numero => $grupo)
            @php
                $grupoActivo = collect($grupo['opciones'])->contains(fn ($item) => $estaActiva($item));
                // si ninguna página del menú está activa, el primer grupo empieza abierto (como en el diseño)
                $grupoAbierto = $grupoActivo || ($numero === 0 && ! request()->routeIs(collect($grupos)->flatMap(fn ($g) => collect($g['opciones'])->where('tipo', 'ruta')->pluck('ruta'))->all()));
            @endphp
            <section class="cliente-menu__grupo {{ $grupoActivo ? 'is-active' : '' }} {{ $grupoAbierto ? 'is-open' : '' }}" data-cliente-menu-grupo>
                <button class="cliente-menu__boton" type="button" aria-expanded="{{ $grupoAbierto ? 'true' : 'false' }}" aria-controls="cliente-submenu-{{ $grupo['clave'] }}" data-cliente-menu-boton>
                    <span class="cliente-menu__boton-izq">
                        <i class="bi {{ $grupo['icono'] }}" aria-hidden="true"></i>
                        <span class="cliente-menu__texto">{{ $grupo['titulo'] }}</span>
                    </span>
                    <i class="bi bi-chevron-down cliente-menu__flecha" aria-hidden="true"></i>
                </button>

                <div class="cliente-menu__submenu" id="cliente-submenu-{{ $grupo['clave'] }}" data-cliente-menu-submenu @if (! $grupoAbierto) hidden @endif>
                    @foreach ($grupo['opciones'] as $item)
                        @if ($item['tipo'] === 'pronto')
                            <span class="cliente-menu__opcion cliente-menu__opcion--pronto" aria-disabled="true" title="Próximamente">
                                <span class="cliente-menu__texto">{{ $item['texto'] }}</span>
                                <small>Pronto</small>
                            </span>
                        @else
                            <a class="cliente-menu__opcion {{ $estaActiva($item) ? 'is-active' : '' }}"
                               href="{{ route($item['ruta']).$item['extra'] }}"
                               title="{{ $item['texto'] }}"
                               @if ($item['tipo'] === 'vista') data-cliente-menu-vista="{{ ltrim($item['extra'], '#') }}" @endif>
                                <span class="cliente-menu__texto">{{ $item['texto'] }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>

    {{-- cerrar sesión al final, como en el diseño --}}
    <form class="cliente-menu__salir" method="post" action="{{ route('logout') }}">
        @csrf
        <button type="submit" title="Cerrar sesión">
            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
            <span class="cliente-menu__texto">Cerrar sesión</span>
        </button>
    </form>
</div>
