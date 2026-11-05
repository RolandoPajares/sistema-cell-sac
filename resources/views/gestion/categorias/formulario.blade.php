@php($editing = isset($category) && $category !== null)

<label>
    Nombre
    <input
        name="nombre"
        @if($editing) value="{{ $category->nombre }}" @endif
        required
        maxlength="120"
    >
</label>

<label>
    Descripción
    <textarea name="descripcion" maxlength="500">{{ $editing ? $category->descripcion : '' }}</textarea>
</label>
