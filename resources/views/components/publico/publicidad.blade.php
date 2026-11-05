@php
    $campaigns = collect($publicCampaigns ?? []);
    $principalCampaign = $campaigns->firstWhere('ubicacion', 'emergente') ?? $campaigns->first();
    $contextualCampaign = $campaigns->first(fn ($campaign): bool => $principalCampaign && $campaign->id !== $principalCampaign->id);
    $advertisingContext = request()->routeIs('home') ? 'inicio' : 'general';
@endphp

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
                    @if($principalCampaign->precio_oferta)
                        <div class="publicidad-precio"><del>S/ {{ number_format((float) $principalCampaign->precio_anterior, 2) }}</del><strong>S/ {{ number_format((float) $principalCampaign->precio_oferta, 2) }}</strong></div>
                    @endif
                    <div class="publicidad-beneficios"><span><i class="bi bi-check-circle-fill"></i> Fácil de usar</span><span><i class="bi bi-check-circle-fill"></i> Sin compromiso</span></div>
                    <a class="btn btn-primary js-publicidad-accion" href="{{ route('catalog.index') }}">{{ $principalCampaign->texto_boton }} <i class="bi bi-arrow-right"></i></a>
                    <button class="publicidad-despues js-publicidad-cerrar" type="button">Seguir navegando</button>
                </div>
            </section>
        </div>

        @if($contextualCampaign)
            <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden>
                <button class="publicidad-cerrar js-contextual-cerrar" type="button" aria-label="Cerrar promoción"><i class="bi bi-x-lg"></i></button>
                <span class="publicidad-contextual-icono"><i class="bi bi-gift-fill"></i></span>
                <div><small>{{ $contextualCampaign->nombre }}</small><b>{{ $contextualCampaign->titulo }}</b><p>{{ $contextualCampaign->descripcion }}</p></div>
                <a class="btn btn-primary" href="{{ route('catalog.index') }}">{{ $contextualCampaign->texto_boton }}</a>
            </aside>
        @else
            <aside class="publicidad-contextual js-publicidad-contextual" aria-live="polite" hidden></aside>
        @endif

        <button class="publicidad-lanzador js-publicidad-lanzador" type="button" aria-label="Ver oferta disponible" title="Ver oferta"><i class="bi bi-gift-fill"></i><span>Oferta</span></button>
    </div>
@endif
