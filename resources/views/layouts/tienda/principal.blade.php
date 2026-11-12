
<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="description" content="Celulares y accesorios originales de MD Technology Digital Cell.">
        <title>@yield('title', 'MD Technology Cell')</title>

        @vite(['resources/css/layouts/tienda/principal.css', 'resources/js/layouts/tienda/principal.js'])
        <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.min.css') }}">
        @stack('styles')
    </head>
    <body class="@yield('body_class')">
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('layouts.tienda.encabezado')

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @unless(request()->routeIs('home'))
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
@if($principalCampaign)
    <div class="publicidad-dinamica publicidad-{{ $advertisingContext }}" id="publicidadDinamica" data-contexto="{{ $advertisingContext }}" data-perfil="visitante" data-limite="4" data-espera="12000">
        <div class="publicidad-modal-fondo js-publicidad-modal" hidden>
            <section class="publicidad-modal" role="dialog" aria-modal="true" aria-labelledby="publicidadTitulo" data-track-url="{{ route('campaigns.track', $principalCampaign) }}">
                <button class="publicidad-cerrar js-publicidad-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
                <div class="publicidad-modal-arte">
                    <span><i class="bi bi-lightning-charge-fill"></i></span>
                    <b>MD</b><strong>SMART DEALS</strong>
                    <small>Una mejor compra empieza con una buena recomendación.</small>
                </div>
                <div class="publicidad-modal-contenido">
                    <span class="publicidad-etiqueta"><i class="bi bi-lightning-charge-fill"></i> {{ $principalCampaign->nombre }}</span>
                    <h2 id="publicidadTitulo">{{ $principalCampaign->titulo }}</h2>
                    <p>{{ $principalCampaign->descripcion }}</p>
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
                    @if($principalCampaign->precio_oferta)
                        <div class="publicidad-precio"><del>S/ {{ number_format((float) $principalCampaign->precio_anterior, 2) }}</del><strong>S/ {{ number_format((float) $principalCampaign->precio_oferta, 2) }}</strong></div>
                    @endif
                    <div class="publicidad-beneficios"><span><i class="bi bi-check-circle-fill"></i> Fácil de usar</span><span><i class="bi bi-check-circle-fill"></i> Sin compromiso</span></div>
                    <a class="btn btn-primary js-publicidad-accion" href="{{ route('catalog.index') }}">{{ $principalCampaign->texto_boton }} <i class="bi bi-arrow-right"></i></a>
                    <button class="publicidad-despues js-publicidad-cerrar" type="button">Seguir navegando</button>
                </div>
            </section>
        </div>

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if($contextualCampaign)
            <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden>
                <button class="publicidad-cerrar js-contextual-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
                <span class="publicidad-contextual-icono"><i class="bi bi-gift-fill"></i></span>
                <div><small>{{ $contextualCampaign->nombre }}</small><b>{{ $contextualCampaign->titulo }}</b><p>{{ $contextualCampaign->descripcion }}</p></div>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}">{{ $contextualCampaign->texto_boton }}</a>
            </aside>
{{-- alternativa del condicional cuando la condicion anterior no se cumple. --}}
        @else
            <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden></aside>
        @endif

        <button class="publicidad-lanzador js-publicidad-lanzador" type="button" aria-label="Ver oferta disponible" title="Ver oferta"><i class="bi bi-gift-fill"></i><span>Oferta</span></button>
    </div>
@endif

        @endunless

{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if (session('status'))<div class="container flash-success" role="status">{{ session('status') }}</div>@endif
{{-- condicional que muestra este bloque solo cuando se cumple la condicion. --}}
        @if ($errors->any())<div class="container flash-error" role="alert">{{ $errors->first() }}</div>@endif

        <main>@yield('content')</main>

{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('layouts.tienda.pie-pagina')
        <button id="toTop" class="to-top" aria-label="Subir"><i class="bi bi-arrow-up" aria-hidden="true"></i></button>
{{-- vista parcial reutilizada con los datos necesarios para este bloque. --}}
        @include('inteligente.asistente.flotante')

        @stack('scripts')
    </body>
</html>
