<form method="post" class="form-card compact-form" action="{{ route('management.purchases.store') }}">
    @csrf
    <input type="hidden" name="compra_token" value="{{ session('compra_token') }}">
    <h2>Registrar compra pendiente</h2>

    <label>
        Proveedor
        <select name="proveedor_id" required>
            @foreach($suppliers as $supplier)
                <option value="{{ $supplier->id }}">{{ $supplier->nombre }}</option>
            @endforeach
        </select>
    </label>

    <label>
        Fecha
        <input
            type="date"
            name="fecha_compra"
            required
            value="{{ now()->toDateString() }}"
        >
    </label>

    <label>
        Producto
        <select name="items[0][producto_id]" required>
            @foreach($products as $product)
                <option value="{{ $product->id }}">{{ $product->nombre }}</option>
            @endforeach
        </select>
    </label>

    <label>
        Variante (si corresponde)
        <select name="items[0][variante_id]">
            <option value="">Producto sin variantes</option>
            @foreach ($products as $product)
                @foreach ($product->variants->where('estado', 'activo') as $variant)
                    <option value="{{ $variant->id }}">{{ $product->nombre }} · {{ $variant->nombre_color }}@if ($variant->codigo_sku) · SKU {{ $variant->codigo_sku }}@endif</option>
                @endforeach
            @endforeach
        </select>
        <small>Si el producto tiene variantes, selecciona una que pertenezca al producto indicado.</small>
    </label>

    <label>
        Cantidad
        <input type="number" name="items[0][cantidad]" min="1" required>
    </label>

    <label>
        Costo unitario
        <input
            type="number"
            name="items[0][costo_unitario]"
            step="0.01"
            min="0"
            required
        >
    </label>

    <button class="btn btn-primary">Registrar compra</button>
</form>
