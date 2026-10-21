
@extends('layouts.admin')

@section('title', 'Campañas')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/cell-legacy/css/modulos/marketing/campanias.css') }}">
@endpush

@section('content')
<section class="admin-page david-campaigns">
    <header class="admin-page-head">
        <div>
            <span class="product-category">Marketing Center</span>
            <h1>Campañas</h1>
            <p>Promociones y banners con registro de vistas y clics.</p>
        </div>
    </header>

    <section class="dc-panel">
        <header class="dc-header">
            <div>
                <h2>Campañas registradas</h2>
                <p>Administra las promociones y banners de la empresa.</p>
            </div>
            <button type="button" class="dc-btn dc-primary"
                    onclick="document.getElementById('dc-create').showModal()">
                <i class="bi bi-plus-lg"></i> Nueva campaña
            </button>
        </header>

        <div class="dc-grid">
            @forelse($campaigns as $campaign)
                <article class="dc-card">
                    <div class="dc-card-top">
                        <span class="dc-type">
                            {{ str_replace('_', ' ', $campaign->ubicacion) }}
                        </span>
                        <span class="dc-status {{ $campaign->activo ? 'dc-active' : 'dc-inactive' }}">
                            {{ $campaign->activo ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>

                    <h3>{{ $campaign->titulo }}</h3>
                    <p class="dc-muted">{{ $campaign->nombre }}</p>

                    <p class="dc-muted">
                        <i class="bi bi-calendar3"></i>
                        {{ $campaign->inicia_en?->format('d/m/Y H:i') }}
                        —
                        {{ $campaign->finaliza_en?->format('d/m/Y H:i') }}
                    </p>

                    <p class="dc-muted">
                        <i class="bi bi-people"></i>
                        {{ $campaign->segments->pluck('nombre')->join(', ') ?: 'Público' }}
                    </p>

                    <div class="dc-stats">
                        <div>
                            <small>Vistas</small>
                            <strong>{{ $campaign->vistas }}</strong>
                        </div>
                        <div>
                            <small>Clics</small>
                            <strong>{{ $campaign->clics }}</strong>
                        </div>
                    </div>

                    <div class="dc-actions">
                        <button type="button" class="dc-btn dc-outline"
                                onclick="document.getElementById('dc-edit-{{ $campaign->id }}').showModal()">
                            <i class="bi bi-pencil-square"></i> Editar
                        </button>

                        @if($campaign->activo)
                            <form method="post"
                                  action="{{ route('management.campaigns.deactivate', $campaign) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="dc-btn dc-outline"
                                        data-confirm="¿Desactivar esta campaña?">
                                    Desactivar
                                </button>
                            </form>
                        @else
                            <form method="post"
                                  action="{{ route('management.campaigns.activate', $campaign) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="dc-btn dc-primary"
                                        data-confirm="¿Activar esta campaña?">
                                    Activar
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <p class="dc-empty">No hay campañas registradas.</p>
            @endforelse
        </div>

        <div class="dc-pagination">
            {{ $campaigns->links() }}
        </div>
    </section>
</section>

{{-- MODAL CREAR --}}
<dialog id="dc-create" class="dc-dialog" aria-labelledby="dc-create-title">
    <div class="dc-dialog-header">
        <h2 id="dc-create-title">Nueva campaña</h2>
        <button type="button" class="dc-close"
                onclick="this.closest('dialog').close()"
                aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form method="post" action="{{ route('management.campaigns.store') }}">
        @csrf
        <div class="dc-fields">
            <label>Nombre
                <input name="nombre" required maxlength="120"
                       value="{{ old('nombre') }}">
            </label>

            <label>Ubicación
                <select name="ubicacion">
                    @foreach(['emergente' => 'Emergente', 'lateral' => 'Lateral', 'barra_superior' => 'Barra superior'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('ubicacion') === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="dc-full">Título
                <input name="titulo" required maxlength="160"
                       value="{{ old('titulo') }}">
            </label>

            <label class="dc-full">Descripción
                <textarea name="descripcion" maxlength="500">{{ old('descripcion') }}</textarea>
            </label>

            <label>Texto del botón
                <input name="texto_boton" required
                       value="{{ old('texto_boton', 'Ver oferta') }}">
            </label>

            <label>Destino
                <input name="url_boton" required
                       value="{{ old('url_boton', 'catalog') }}">
            </label>

            <label class="dc-full">Imagen
                <input name="url_imagen" maxlength="255"
                       value="{{ old('url_imagen') }}">
            </label>

            <label>Precio anterior
                <input type="number" name="precio_anterior" min="0"
                       step="0.01" value="{{ old('precio_anterior') }}">
            </label>

            <label>Precio oferta
                <input type="number" name="precio_oferta" min="0"
                       step="0.01" value="{{ old('precio_oferta') }}">
            </label>

            <label>Inicia
                <input type="datetime-local" name="inicia_en" required
                       value="{{ old('inicia_en') }}">
            </label>

            <label>Finaliza
                <input type="datetime-local" name="finaliza_en" required
                       value="{{ old('finaliza_en') }}">
            </label>

            <label class="dc-full">Segmentos
                <select name="segmentos[]" multiple size="4">
                    @foreach($segments as $segment)
                        <option value="{{ $segment->id }}"
                            @selected(in_array($segment->id, old('segmentos', [])))>
                            {{ $segment->nombre }}
                        </option>
                    @endforeach
                </select>
                <small>Ctrl + clic para seleccionar varios segmentos.</small>
            </label>
        </div>

        @if($errors->any())
            <div class="dc-errors" role="alert">
                <strong>Revisa los datos ingresados:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="dc-footer">
            <button type="button" class="dc-btn dc-outline"
                    onclick="this.closest('dialog').close()">
                Cancelar
            </button>
            <button type="submit" class="dc-btn dc-primary">
                Guardar campaña
            </button>
        </div>
    </form>
</dialog>

{{-- MODALES EDITAR: UNO POR CAMPAÑA --}}
@foreach($campaigns as $campaign)
<dialog id="dc-edit-{{ $campaign->id }}" class="dc-dialog"
        aria-labelledby="dc-edit-title-{{ $campaign->id }}">

    <div class="dc-dialog-header">
        <div>
            <h2 id="dc-edit-title-{{ $campaign->id }}">Editar campaña</h2>
            <p>{{ $campaign->nombre }}</p>
        </div>
        <button type="button" class="dc-close"
                onclick="this.closest('dialog').close()"
                aria-label="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <form method="post"
          action="{{ route('management.campaigns.update', $campaign) }}">
        @csrf
        @method('PUT')

        <div class="dc-fields">
            <label>Nombre
                <input name="nombre" required maxlength="120"
                       value="{{ $campaign->nombre }}">
            </label>

            <label>Ubicación
                <select name="ubicacion">
                    @foreach(['emergente' => 'Emergente', 'lateral' => 'Lateral', 'barra_superior' => 'Barra superior'] as $value => $label)
                        <option value="{{ $value }}" @selected($campaign->ubicacion === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="dc-full">Título
                <input name="titulo" required maxlength="160"
                       value="{{ $campaign->titulo }}">
            </label>

            <label class="dc-full">Descripción
                <textarea name="descripcion" maxlength="500">{{ $campaign->descripcion }}</textarea>
            </label>

            <label>Texto del botón
                <input name="texto_boton" required
                       value="{{ $campaign->texto_boton }}">
            </label>

            <label>Destino
                <input name="url_boton" required
                       value="{{ $campaign->url_boton }}">
            </label>

            <label class="dc-full">Imagen
                <input name="url_imagen" maxlength="255"
                       value="{{ $campaign->url_imagen }}">
            </label>

            <label>Precio anterior
                <input type="number" name="precio_anterior" min="0"
                       step="0.01" value="{{ $campaign->precio_anterior }}">
            </label>

            <label>Precio oferta
                <input type="number" name="precio_oferta" min="0"
                       step="0.01" value="{{ $campaign->precio_oferta }}">
            </label>

            <label>Inicia
                <input type="datetime-local" name="inicia_en" required
                       value="{{ $campaign->inicia_en?->format('Y-m-d\TH:i') }}">
            </label>

            <label>Finaliza
                <input type="datetime-local" name="finaliza_en" required
                       value="{{ $campaign->finaliza_en?->format('Y-m-d\TH:i') }}">
            </label>

            <label class="dc-full">Segmentos
                <select name="segmentos[]" multiple size="4">
                    @foreach($segments as $segment)
                        <option value="{{ $segment->id }}"
                            @selected($campaign->segments->contains('id', $segment->id))>
                            {{ $segment->nombre }}
                        </option>
                    @endforeach
                </select>
                <small>Ctrl + clic para seleccionar varios segmentos.</small>
            </label>
        </div>

        <div class="dc-footer">
            <button type="button" class="dc-btn dc-outline"
                    onclick="this.closest('dialog').close()">
                Cancelar
            </button>
            <button type="submit" class="dc-btn dc-primary">
                Actualizar campaña
            </button>
        </div>
    </form>
</dialog>
@endforeach

@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('dc-create')?.showModal();
});
</script>
@endif

@endsection
