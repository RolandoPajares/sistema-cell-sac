@php($editing = isset($product) && $product !== null)

<label class="admin-form-group">
    <span>{{ $editing ? 'Marca' : 'Marca *' }}</span>
    <input
        name="marca"
        @if($editing) value="{{ $product->marca }}" @endif
        required
    >
</label>

<label class="admin-form-group">
    <span>{{ $editing ? 'Nombre' : 'Nombre *' }}</span>
    <input
        name="nombre"
        @if($editing) value="{{ $product->nombre }}" @endif
        required
    >
</label>

<label class="admin-form-group is-full">
    <span>{{ $editing ? 'Categoría' : 'Categoría *' }}</span>
    <select name="categoria_id" required>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" @selected($editing && $category->id === $product->categoria_id)>{{ $category->nombre }}</option>
        @endforeach
    </select>
</label>

<label class="admin-form-group">
    <span>{{ $editing ? 'Precio' : 'Precio *' }}</span>
    <input
        name="precio"
        type="number"
        min="0"
        step="0.01"
        @if($editing) value="{{ $product->precio }}" @endif
        required
    >
</label>

<label class="admin-form-group">
    <span>Precio oferta</span>
    <input
        name="precio_oferta"
        type="number"
        min="0"
        step="0.01"
        @if($editing) value="{{ $product->precio_oferta }}" @endif
    >
</label>

<label class="admin-form-group is-full">
    <span>{{ $editing ? 'Imagen' : 'Ruta de imagen' }}</span>
    <input
        name="url_imagen"
        @if(!$editing) maxlength="255" @endif
        @if($editing) value="{{ $product->url_imagen }}" @endif
    >
</label>

<label class="admin-form-group is-full">
    <span>Descripción</span>
    <textarea
        name="descripcion"
        @if(!$editing) rows="4" @endif
    >{{ $editing ? $product->descripcion : '' }}</textarea>
</label>
