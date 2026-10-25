@extends('layouts.administracion')

@section('title', 'Campañas')

@section('content')
<section class="admin-page" data-admin-table-container>
    <div class="admin-page-head">
        <div>
            <span class="product-category">Marketing Center</span>
            <h1>Campañas</h1>
            <p>Promociones y banners con registro de vistas y clics.</p>
        </div>
    </div>

    <div class="two-column">
        @include('gestion.campanias.crear', ['segments' => $segments])

        <div class="form-card">
            <h2>Campañas registradas</h2>
            @forelse($campaigns as $campaign)
                <article class="data-row" data-data-row>
                    <div>
                        <b>{{ $campaign->titulo }}</b>
                        <small>{{ $campaign->nombre }} · {{ $campaign->ubicacion }} · {{ $campaign->inicia_en?->format('d/m/Y H:i') }} — {{ $campaign->finaliza_en?->format('d/m/Y H:i') }} · {{ $campaign->activo ? 'Activa' : 'Inactiva' }}</small>
                        <small>{{ $campaign->vistas }} vistas / {{ $campaign->clics }} clics · Segmentos: {{ $campaign->segments->pluck('nombre')->join(', ') ?: 'Público' }}</small>
                    </div>
                    <div class="inline-actions">
                        <details>
                            <summary class="btn btn-secondary">Editar</summary>
                            @include('gestion.campanias.editar', ['campaign' => $campaign, 'segments' => $segments])
                        </details>
                        @if($campaign->activo)
                            <form method="post" action="{{ route('management.campaigns.deactivate', $campaign) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="btn btn-secondary"
                                    data-confirm="¿Desactivar esta campaña?"
                                >
                                    Desactivar
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p>No hay campañas registradas.</p>
            @endforelse
            {{ $campaigns->links() }}
        </div>
    </div>
</section>
@endsection

