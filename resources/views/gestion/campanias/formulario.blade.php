@php($editing = isset($campaign) && $campaign !== null)

<label>
    Nombre
    <input
        name="nombre"
        required
        maxlength="120"
        value="{{ $editing ? $campaign->nombre : old('nombre') }}"
    >
</label>

<label>
    Ubicación
    <select name="ubicacion">
        @foreach(['emergente' => 'Emergente', 'lateral' => 'Lateral', 'barra_superior' => 'Barra superior'] as $value => $label)
            <option value="{{ $value }}" @selected($editing && $campaign->ubicacion === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>

<label>
    Título
    <input
        name="titulo"
        required
        maxlength="160"
        value="{{ $editing ? $campaign->titulo : old('titulo') }}"
    >
</label>

<label>
    Descripción
    <textarea name="descripcion" maxlength="500">{{ $editing ? $campaign->descripcion : old('descripcion') }}</textarea>
</label>

<label>
    Texto del botón
    <input
        name="texto_boton"
        value="{{ $editing ? $campaign->texto_boton : old('texto_boton', 'Ver oferta') }}"
        required
    >
</label>

<label>
    Destino
    <input
        name="url_boton"
        value="{{ $editing ? $campaign->url_boton : old('url_boton', 'catalog') }}"
        required
    >
</label>

<label>
    Imagen
    <input
        name="url_imagen"
        maxlength="255"
        value="{{ $editing ? $campaign->url_imagen : old('url_imagen') }}"
    >
</label>

<label>
    Precio anterior
    <input
        type="number"
        name="precio_anterior"
        min="0"
        step="0.01"
        value="{{ $editing ? $campaign->precio_anterior : old('precio_anterior') }}"
    >
</label>

<label>
    Precio oferta
    <input
        type="number"
        name="precio_oferta"
        min="0"
        step="0.01"
        value="{{ $editing ? $campaign->precio_oferta : old('precio_oferta') }}"
    >
</label>

<label>
    Inicia
    <input
        type="datetime-local"
        name="inicia_en"
        required
        value="{{ $editing ? $campaign->inicia_en?->format('Y-m-d\\TH:i') : old('inicia_en') }}"
    >
</label>

<label>
    Finaliza
    <input
        type="datetime-local"
        name="finaliza_en"
        required
        value="{{ $editing ? $campaign->finaliza_en?->format('Y-m-d\\TH:i') : old('finaliza_en') }}"
    >
</label>

<label>
    Segmentos
    <select name="segmentos[]" multiple>
        @foreach($segments as $segment)
            <option value="{{ $segment->id }}" @selected($editing && $campaign->segments->contains('id', $segment->id))>{{ $segment->nombre }}</option>
        @endforeach
    </select>
</label>
