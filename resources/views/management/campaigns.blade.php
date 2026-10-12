@extends('layouts.admin')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/marketing/campanias.css') }}">
@endpush

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
        <form method="post" class="form-card" action="{{ route('management.campaigns.store') }}">
            @csrf
            <h2>Nueva campaña</h2>
            <label>Nombre<input name="nombre" required maxlength="120" value="{{ old('nombre') }}"></label>
            <label>Ubicación<select name="ubicacion">
                <option value="emergente">Emergente</option>
                <option value="lateral">Lateral</option>
                <option value="barra_superior">Barra superior</option>
            </select></label>
            <label>Título<input name="titulo" required maxlength="160" value="{{ old('titulo') }}"></label>
            <label>Descripción<textarea name="descripcion" maxlength="500">{{ old('descripcion') }}</textarea></label>
            <label>Texto del botón<input name="texto_boton" value="{{ old('texto_boton', 'Ver oferta') }}" required></label>
            <label>Destino<input name="url_boton" value="{{ old('url_boton', 'catalog') }}" required></label>
            <label>Imagen<input name="url_imagen" maxlength="255" value="{{ old('url_imagen') }}"></label>
            <label>Precio anterior<input type="number" name="precio_anterior" min="0" step="0.01" value="{{ old('precio_anterior') }}"></label>
            <label>Precio oferta<input type="number" name="precio_oferta" min="0" step="0.01" value="{{ old('precio_oferta') }}"></label>
            <label>Inicia<input type="datetime-local" name="inicia_en" required value="{{ old('inicia_en') }}"></label>
            <label>Finaliza<input type="datetime-local" name="finaliza_en" required value="{{ old('finaliza_en') }}"></label>
            <label>Segmentos<select name="segmentos[]" multiple>
                @foreach($segments as $segment)
                    <option value="{{ $segment->id }}">{{ $segment->nombre }}</option>
                @endforeach
            </select></label>
            <button class="btn btn-primary">Guardar campaña</button>
        </form>

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
                            <form method="post" class="form-card compact-form" action="{{ route('management.campaigns.update', $campaign) }}">
                                @csrf
                                @method('PUT')
                                <label>Nombre<input name="nombre" required maxlength="120" value="{{ $campaign->nombre }}"></label>
                                <label>Ubicación<select name="ubicacion">
                                    @foreach(['emergente' => 'Emergente', 'lateral' => 'Lateral', 'barra_superior' => 'Barra superior'] as $value => $label)
                                        <option value="{{ $value }}" @selected($campaign->ubicacion === $value)>{{ $label }}</option>
                                    @endforeach
                                </select></label>
                                <label>Título<input name="titulo" required maxlength="160" value="{{ $campaign->titulo }}"></label>
                                <label>Descripción<textarea name="descripcion" maxlength="500">{{ $campaign->descripcion }}</textarea></label>
                                <label>Texto del botón<input name="texto_boton" required value="{{ $campaign->texto_boton }}"></label>
                                <label>Destino<input name="url_boton" required value="{{ $campaign->url_boton }}"></label>
                                <label>Imagen<input name="url_imagen" maxlength="255" value="{{ $campaign->url_imagen }}"></label>
                                <label>Precio anterior<input type="number" name="precio_anterior" min="0" step="0.01" value="{{ $campaign->precio_anterior }}"></label>
                                <label>Precio oferta<input type="number" name="precio_oferta" min="0" step="0.01" value="{{ $campaign->precio_oferta }}"></label>
                                <label>Inicia<input type="datetime-local" name="inicia_en" required value="{{ $campaign->inicia_en?->format('Y-m-d\\TH:i') }}"></label>
                                <label>Finaliza<input type="datetime-local" name="finaliza_en" required value="{{ $campaign->finaliza_en?->format('Y-m-d\\TH:i') }}"></label>
                                <label>Segmentos<select name="segmentos[]" multiple>
                                    @foreach($segments as $segment)
                                        <option value="{{ $segment->id }}" @selected($campaign->segments->contains('id', $segment->id))>{{ $segment->nombre }}</option>
                                    @endforeach
                                </select></label>
                                <button class="btn btn-primary">Actualizar campaña</button>
                            </form>
                        </details>
                        @if($campaign->activo)
                            <form method="post" action="{{ route('management.campaigns.deactivate', $campaign) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-secondary" data-confirm="¿Desactivar esta campaña?">Desactivar</button>
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
