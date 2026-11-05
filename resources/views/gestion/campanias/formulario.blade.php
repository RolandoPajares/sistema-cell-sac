@php
    $editing = isset($campaign) && $campaign !== null;
    $selectedSegments = $editing ? $campaign->segments->modelKeys() : old('segmentos', []);
    $currentImage = $editing ? $campaign->url_imagen : old('url_imagen');
@endphp

<div class="campanias-form-grid" data-campaign-form>
    <label>
        <span>Nombre de la campaña <b>*</b></span>
        <input name="nombre" required maxlength="120" value="{{ $editing ? $campaign->nombre : old('nombre') }}" placeholder="Ej. SMART WEEK | Ofertas especiales">
    </label>

    <label>
        <span>Estado</span>
        <select name="activo">
            <option value="1" @selected((string) old('activo', $editing ? (int) $campaign->activo : 1) === '1')>Activa</option>
            <option value="0" @selected((string) old('activo', $editing ? (int) $campaign->activo : 1) === '0')>Pausada</option>
        </select>
    </label>

    <label>
        <span>Ubicación <b>*</b></span>
        <select name="ubicacion" required>
            @foreach (['emergente' => 'Emergente', 'lateral' => 'Lateral', 'barra_superior' => 'Barra superior'] as $value => $label)
                <option value="{{ $value }}" @selected(($editing ? $campaign->ubicacion : old('ubicacion', 'emergente')) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label>
        <span>Destino <b>*</b></span>
        <select name="destino" required data-campaign-destination>
            @foreach (['catalog' => 'Catálogo', 'catalog.index' => 'Catálogo público', 'products' => 'Productos', 'offers' => 'Ofertas'] as $value => $label)
                <option value="{{ $value }}" @selected(($editing ? $campaign->url_boton : old('url_boton', 'catalog')) === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </label>

    <label class="campanias-form-span-2">
        <span>Título <b>*</b></span>
        <input name="titulo" required maxlength="160" value="{{ $editing ? $campaign->titulo : old('titulo') }}" placeholder="Ej. ¡Ofertas que vuelan!">
    </label>

    <label class="campanias-form-span-2">
        <span>Descripción</span>
        <textarea name="descripcion" maxlength="500" rows="3" placeholder="Describe brevemente la campaña...">{{ $editing ? $campaign->descripcion : old('descripcion') }}</textarea>
        <small class="campanias-character-count">Máximo 500 caracteres</small>
    </label>

    <label>
        <span>Texto del botón <b>*</b></span>
        <input name="texto_boton" required maxlength="80" value="{{ $editing ? $campaign->texto_boton : old('texto_boton', 'Ver oferta') }}" placeholder="Ver oferta">
    </label>

    <label>
        <span>URL o enlace <b>*</b></span>
        <input name="url_boton" required maxlength="255" value="{{ $editing ? $campaign->url_boton : old('url_boton', 'catalog') }}" placeholder="catalog">
    </label>

    <label>
        <span>Precio anterior <small>(opcional)</small></span>
        <input type="number" name="precio_anterior" min="0" step="0.01" value="{{ $editing ? $campaign->precio_anterior : old('precio_anterior') }}" placeholder="S/ 0.00">
    </label>

    <label>
        <span>Precio de oferta <small>(opcional)</small></span>
        <input type="number" name="precio_oferta" min="0" step="0.01" value="{{ $editing ? $campaign->precio_oferta : old('precio_oferta') }}" placeholder="S/ 0.00">
    </label>

    <label>
        <span>Fecha de inicio <b>*</b></span>
        <input type="datetime-local" name="inicia_en" required value="{{ $editing ? $campaign->inicia_en?->format('Y-m-d\TH:i') : old('inicia_en') }}">
    </label>

    <label>
        <span>Fecha de finalización <b>*</b></span>
        <input type="datetime-local" name="finaliza_en" required value="{{ $editing ? $campaign->finaliza_en?->format('Y-m-d\TH:i') : old('finaliza_en') }}">
    </label>

    <div class="campanias-upload-card">
        <div class="campanias-upload-copy"><span>Imagen de la campaña <b>*</b></span><small>JPG, PNG o WEBP · Máximo 5 MB</small></div>
        <label class="campanias-upload-zone">
            <i class="bi bi-cloud-arrow-up" aria-hidden="true"></i>
            <strong>Arrastra una imagen o haz clic</strong>
            <small>Selecciona el banner de la promoción</small>
            <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp" data-campaign-image-input>
        </label>
        <label class="campanias-image-url"><span>O usa una URL existente</span><input name="url_imagen" maxlength="255" value="{{ $currentImage }}" placeholder="https://..." data-campaign-image-url></label>
        <div class="campanias-preview" data-campaign-preview @if (!$currentImage) hidden @endif>
            @if ($currentImage)
                <img src="{{ str_starts_with($currentImage, 'http') ? $currentImage : asset(ltrim($currentImage, '/')) }}" alt="Vista previa de la campaña" data-campaign-preview-image>
            @else
                <img alt="Vista previa de la campaña" data-campaign-preview-image>
            @endif
            <button type="button" data-clear-campaign-image aria-label="Quitar imagen"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
        </div>
    </div>

    <fieldset class="campanias-segments campanias-form-span-2">
        <legend>Segmentos de público</legend>
        <div>
            <label><input type="checkbox" data-campaign-all @checked(empty($selectedSegments))><span>Todos</span></label>
            @foreach ($segments as $segment)
                <label><input type="checkbox" name="segmentos[]" value="{{ $segment->id }}" @checked(in_array($segment->id, $selectedSegments, true))><span>{{ $segment->nombre }}</span></label>
            @endforeach
        </div>
    </fieldset>
</div>
